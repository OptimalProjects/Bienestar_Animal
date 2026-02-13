<?php

namespace App\Services;

use App\Models\Adopcion\Adopcion;
use App\Models\Adopcion\VisitaDomiciliaria;
use App\Mail\ContratoFirmadoMail;
use App\Mail\VisitasProgramadasMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Services\FileService;

class ContratoAdopcionService
{
    /**
     * Generar contrato de adopción en PDF.
     */
    public function generarContrato(Adopcion $adopcion): array
    {
        $adopcion->load(['animal', 'adoptante', 'evaluador']);

        // Generar datos para el contrato
        $data = $this->prepararDatosContrato($adopcion);

        // Generar PDF
        $pdf = Pdf::loadView('pdf.contrato-adopcion', $data);
        $pdf->setPaper('letter', 'portrait');

        // Nombre del archivo
        $fileName = 'contrato_' . $adopcion->id . '_' . now()->format('Ymd_His') . '.pdf';
        $path = 'documentos/contratos/' . $fileName;

        // Guardar en storage S3
        Storage::disk('s3')->put($path, $pdf->output());

        // Actualizar adopción con la URL del contrato
        $adopcion->update([
            'contrato_url' => $path,
        ]);

        return [
            'path' => $path,
            'url' => FileService::privateUrl($path),
            'filename' => $fileName,
        ];
    }

    /**
     * Obtener PDF del contrato sin guardar (para preview).
     */
    public function obtenerContratoPdf(Adopcion $adopcion)
    {
        $adopcion->load(['animal', 'adoptante', 'evaluador']);
        $data = $this->prepararDatosContrato($adopcion);

        // Si el contrato está firmado, incluir la firma
        if ($adopcion->contrato_firmado) {
            // Buscar archivo de firma existente
            $firmaPattern = 'firmas/firma_' . $adopcion->id . '_*.png';
            $firmaFiles = Storage::disk('s3')->files('documentos/firmas');
            $firmaPath = null;

            foreach ($firmaFiles as $file) {
                if (str_contains($file, 'firma_' . $adopcion->id . '_')) {
                    $firmaPath = $file;
                    break;
                }
            }

            if ($firmaPath && Storage::disk('s3')->exists($firmaPath)) {
                $firmaContent = Storage::disk('s3')->get($firmaPath);
                $firmaBase64 = base64_encode($firmaContent);
                $data['firma_base64'] = 'data:image/png;base64,' . $firmaBase64;
            }

            $data['contrato_firmado'] = true;
            $data['fecha_firma'] = $adopcion->fecha_entrega?->format('d/m/Y H:i');
        }

        $pdf = Pdf::loadView('pdf.contrato-adopcion', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    /**
     * Registrar firma del contrato.
     */
    public function registrarFirma(Adopcion $adopcion, string $firmaBase64, ?string $ipAddress = null): Adopcion
    {
        // Decodificar y guardar la imagen de la firma
        $firmaData = $this->procesarFirmaBase64($firmaBase64);

        if ($firmaData) {
            $firmaFileName = 'firma_' . $adopcion->id . '_' . now()->format('Ymd_His') . '.png';
            $firmaPath = 'documentos/firmas/' . $firmaFileName;
            Storage::disk('s3')->put($firmaPath, $firmaData);
        }

        // Actualizar adopción
        $adopcion->update([
            'contrato_firmado' => true,
            'fecha_entrega' => now(),
            'estado' => 'completada',
        ]);

        // Cambiar estado del animal a adoptado
        // Usar el modelo Animal directamente para evitar problemas de caché
        $animal = \App\Models\Animal\Animal::find($adopcion->animal_id);

        if ($animal) {
            $estadoAnterior = $animal->estado;

            $animal->estado = 'adoptado';
            $animal->save();

            Log::info('Estado del animal actualizado a adoptado', [
                'animal_id' => $animal->id,
                'animal_codigo' => $animal->codigo_unico,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'adoptado',
            ]);

            // Refrescar la relación en la adopción
            $adopcion->setRelation('animal', $animal);
        } else {
            Log::error('No se pudo actualizar estado del animal: animal no encontrado', [
                'adopcion_id' => $adopcion->id,
                'animal_id' => $adopcion->animal_id,
            ]);
        }

        // Regenerar contrato con la firma incluida
        $this->regenerarContratoConFirma($adopcion, $firmaPath ?? null);

        // Programar seguimientos post-adopción automáticos
        $this->programarSeguimientosPostAdopcion($adopcion);

        // Recargar la adopción con el contrato actualizado
        $adopcion = $adopcion->fresh(['animal', 'adoptante', 'evaluador', 'visitasDomiciliarias']);

        // Enviar correo con el contrato firmado al adoptante
        $this->enviarCorreoContratoFirmado($adopcion);

        Log::info('Contrato de adopción firmado', [
            'adopcion_id' => $adopcion->id,
            'animal_id' => $adopcion->animal_id,
            'adoptante_id' => $adopcion->adoptante_id,
            'ip_address' => $ipAddress,
        ]);

        return $adopcion;
    }

    /**
     * Aprobar adopción y generar contrato.
     */
    public function aprobarAdopcion(Adopcion $adopcion, string $evaluadorId): Adopcion
    {
        // Actualizar estado de la adopción
        $adopcion->update([
            'estado' => 'aprobada',
            'fecha_aprobacion' => now(),
            'evaluador_id' => $evaluadorId,
        ]);

        // Generar contrato
        $contratoInfo = $this->generarContrato($adopcion);

        Log::info('Adopción aprobada y contrato generado', [
            'adopcion_id' => $adopcion->id,
            'contrato_url' => $contratoInfo['url'],
        ]);

        return $adopcion->fresh(['animal', 'adoptante', 'evaluador']);
    }

    /**
     * Programar seguimientos post-adopción automáticos.
     */
    public function programarSeguimientosPostAdopcion(Adopcion $adopcion): array
    {
        $seguimientos = [];
        $fechaEntrega = $adopcion->fecha_entrega ?? now();

        // Seguimiento 1 mes
        $seguimientos[] = VisitaDomiciliaria::create([
            'adopcion_id' => $adopcion->id,
            'fecha_programada' => $fechaEntrega->copy()->addMonth(),
            'tipo_visita' => 'seguimiento_1mes',
            'visitador_id' => $adopcion->evaluador_id,
            'observaciones' => 'Seguimiento automático programado al completar adopción',
        ]);

        // Seguimiento 3 meses
        $seguimientos[] = VisitaDomiciliaria::create([
            'adopcion_id' => $adopcion->id,
            'fecha_programada' => $fechaEntrega->copy()->addMonths(3),
            'tipo_visita' => 'seguimiento_3meses',
            'visitador_id' => $adopcion->evaluador_id,
            'observaciones' => 'Seguimiento automático programado al completar adopción',
        ]);

        // Seguimiento 6 meses
        $seguimientos[] = VisitaDomiciliaria::create([
            'adopcion_id' => $adopcion->id,
            'fecha_programada' => $fechaEntrega->copy()->addMonths(6),
            'tipo_visita' => 'seguimiento_6meses',
            'visitador_id' => $adopcion->evaluador_id,
            'observaciones' => 'Seguimiento automático programado al completar adopción',
        ]);

        Log::info('Seguimientos post-adopción programados', [
            'adopcion_id' => $adopcion->id,
            'cantidad' => count($seguimientos),
        ]);

        // Notificar al adoptante las fechas de las visitas programadas
        $this->notificarVisitasProgramadas($adopcion, collect($seguimientos));

        return $seguimientos;
    }

    /**
     * Notificar al adoptante las visitas de seguimiento programadas.
     */
    protected function notificarVisitasProgramadas(Adopcion $adopcion, $visitas): void
    {
        try {
            $adopcion->load(['animal', 'adoptante']);
            $emailAdoptante = $adopcion->adoptante->email ?? null;

            if (!$emailAdoptante || !filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
                Log::warning('No se pudo enviar notificación de visitas programadas: email inválido', [
                    'adopcion_id' => $adopcion->id,
                    'email' => $emailAdoptante,
                ]);
                return;
            }

            Mail::to($emailAdoptante)->send(new VisitasProgramadasMail($adopcion, $visitas));

            Log::info('Notificación de visitas programadas enviada al adoptante', [
                'adopcion_id' => $adopcion->id,
                'animal' => $adopcion->animal->nombre ?? $adopcion->animal->codigo_unico,
                'destinatario' => $emailAdoptante,
                'cantidad_visitas' => $visitas->count(),
            ]);
        } catch (\Exception $e) {
            // No interrumpir el flujo si falla el envío
            Log::error('Error enviando notificación de visitas programadas', [
                'adopcion_id' => $adopcion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Preparar datos para la plantilla del contrato.
     */
    protected function prepararDatosContrato(Adopcion $adopcion): array
    {
        $adoptante = $adopcion->adoptante;
        $animal = $adopcion->animal;
        $evaluador = $adopcion->evaluador;

        return [
            'numero_contrato' => $this->generarNumeroContrato($adopcion),
            'fecha_contrato' => now()->format('d/m/Y'),
            'fecha_contrato_texto' => $this->fechaEnTexto(now()),

            // Datos del adoptante
            'adoptante' => [
                'nombre_completo' => $adoptante->nombre_completo,
                'tipo_documento' => $this->getTipoDocumentoTexto($adoptante->tipo_documento),
                'numero_documento' => $adoptante->numero_documento,
                'direccion' => $adoptante->direccion,
                'telefono' => $adoptante->telefono,
                'email' => $adoptante->email,
                'tipo_vivienda' => $this->getTipoViviendaTexto($adoptante->tipo_vivienda),
            ],

            // Datos del animal
            'animal' => [
                'codigo' => $animal->codigo_unico,
                'nombre' => $animal->nombre ?: 'Sin nombre asignado',
                'especie' => ucfirst($animal->especie),
                'raza' => $animal->raza ?: 'Mestizo',
                'sexo' => $animal->sexo === 'macho' ? 'Macho' : 'Hembra',
                'color' => $animal->color ?: 'No especificado',
                'edad' => $animal->edad_formateada ?? 'Desconocida',
                'tamanio' => $this->getTamanioTexto($animal->tamanio),
                'esterilizado' => $animal->esterilizacion ? 'Sí' : 'No',
                'microchip' => $animal->microchip ?: 'No aplica',
                'estado_salud' => $this->getEstadoSaludTexto($animal->estado_salud),
            ],

            // Datos institucionales
            'institucion' => [
                'nombre' => 'Sistema de Bienestar Animal',
                'municipio' => 'Municipio de Colombia',
                'direccion' => 'Dirección de la entidad',
                'telefono' => '(000) 000-0000',
            ],

            // Evaluador
            'evaluador' => $evaluador ? [
                'nombre' => $evaluador->nombre_completo ?? $evaluador->nombres . ' ' . $evaluador->apellidos,
                'cargo' => 'Coordinador de Adopciones',
            ] : null,

            // Compromisos del adoptante
            'compromisos' => $this->getCompromisosAdoptante(),

            // Datos adicionales
            'adopcion_id' => $adopcion->id,
            'contrato_firmado' => $adopcion->contrato_firmado,
            'fecha_firma' => $adopcion->fecha_entrega?->format('d/m/Y'),
        ];
    }

    /**
     * Generar número de contrato único.
     */
    protected function generarNumeroContrato(Adopcion $adopcion): string
    {
        $year = now()->format('Y');
        $sequence = Adopcion::whereYear('created_at', $year)->count();
        return sprintf('CONT-ADOP-%s-%04d', $year, $sequence);
    }

    /**
     * Convertir fecha a texto.
     */
    protected function fechaEnTexto(\DateTimeInterface $fecha): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        $dia = $fecha->format('d');
        $mes = $meses[(int)$fecha->format('m')];
        $year = $fecha->format('Y');

        return "{$dia} de {$mes} de {$year}";
    }

    /**
     * Obtener compromisos del adoptante.
     */
    protected function getCompromisosAdoptante(): array
    {
        return [
            'Proporcionar alimentación adecuada y agua fresca permanentemente.',
            'Brindar atención veterinaria cuando sea necesario, incluyendo vacunación anual y desparasitación periódica.',
            'Mantener al animal en condiciones higiénicas y de bienestar apropiadas.',
            'No abandonar, maltratar ni permitir que terceros maltraten al animal.',
            'No utilizar al animal para peleas, experimentos o cualquier actividad que atente contra su bienestar.',
            'No comercializar ni ceder al animal sin autorización previa de la entidad.',
            'Permitir las visitas de seguimiento programadas por la entidad.',
            'Notificar a la entidad en caso de cambio de domicilio o cualquier situación que afecte al animal.',
            'En caso de no poder continuar con la tenencia del animal, devolverlo a la entidad.',
            'Cumplir con la normatividad vigente sobre tenencia responsable de animales.',
        ];
    }

    /**
     * Procesar firma en Base64.
     */
    protected function procesarFirmaBase64(string $firmaBase64): ?string
    {
        // Remover el prefijo data:image/png;base64, si existe
        if (Str::contains($firmaBase64, ',')) {
            $firmaBase64 = explode(',', $firmaBase64)[1];
        }

        $decoded = base64_decode($firmaBase64);

        if ($decoded === false) {
            return null;
        }

        return $decoded;
    }

    /**
     * Regenerar contrato incluyendo la firma.
     */
    protected function regenerarContratoConFirma(Adopcion $adopcion, ?string $firmaPath): void
    {
        $adopcion->load(['animal', 'adoptante', 'evaluador']);
        $data = $this->prepararDatosContrato($adopcion);

        if ($firmaPath && Storage::disk('s3')->exists($firmaPath)) {
            // Embeber firma como base64 para DomPDF
            $firmaContent = Storage::disk('s3')->get($firmaPath);
            $firmaBase64 = base64_encode($firmaContent);
            $data['firma_base64'] = 'data:image/png;base64,' . $firmaBase64;
            $data['firma_url'] = FileService::privateUrl($firmaPath);
        }

        $data['contrato_firmado'] = true;
        $data['fecha_firma'] = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('pdf.contrato-adopcion', $data);
        $pdf->setPaper('letter', 'portrait');

        // Nombre del archivo firmado
        $fileName = 'contrato_firmado_' . $adopcion->id . '_' . now()->format('Ymd_His') . '.pdf';
        $path = 'documentos/contratos/' . $fileName;

        Storage::disk('s3')->put($path, $pdf->output());

        $adopcion->update([
            'contrato_url' => $path,
        ]);
    }

    // Helpers para textos
    protected function getTipoDocumentoTexto(?string $tipo): string
    {
        return match($tipo) {
            'CC' => 'Cédula de Ciudadanía',
            'CE' => 'Cédula de Extranjería',
            'PA' => 'Pasaporte',
            'TI' => 'Tarjeta de Identidad',
            default => $tipo ?? 'No especificado',
        };
    }

    protected function getTipoViviendaTexto(?string $tipo): string
    {
        return match($tipo) {
            'casa' => 'Casa',
            'apartamento' => 'Apartamento',
            'finca' => 'Finca',
            'otro' => 'Otro',
            default => $tipo ?? 'No especificado',
        };
    }

    protected function getTamanioTexto(?string $tamanio): string
    {
        return match($tamanio) {
            'pequeno' => 'Pequeño',
            'mediano' => 'Mediano',
            'grande' => 'Grande',
            default => $tamanio ?? 'No especificado',
        };
    }

    protected function getEstadoSaludTexto(?string $estado): string
    {
        return match($estado) {
            'excelente' => 'Excelente',
            'bueno' => 'Bueno',
            'estable' => 'Estable',
            'en_tratamiento' => 'En tratamiento',
            'critico' => 'Crítico',
            default => $estado ?? 'No especificado',
        };
    }

    /**
     * Enviar correo con el contrato firmado al adoptante.
     */
    protected function enviarCorreoContratoFirmado(Adopcion $adopcion): void
    {
        try {
            $emailAdoptante = $adopcion->adoptante->email ?? null;

            if ($emailAdoptante && filter_var($emailAdoptante, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emailAdoptante)->send(new ContratoFirmadoMail($adopcion));

                Log::info('Correo con contrato firmado enviado', [
                    'adopcion_id' => $adopcion->id,
                    'animal' => $adopcion->animal->nombre ?? $adopcion->animal->codigo_unico,
                    'destinatario' => $emailAdoptante,
                    'contrato_url' => $adopcion->contrato_url,
                ]);
            } else {
                Log::warning('No se pudo enviar contrato firmado: email de adoptante no válido', [
                    'adopcion_id' => $adopcion->id,
                    'email' => $emailAdoptante,
                ]);
            }
        } catch (\Exception $e) {
            // No interrumpir el flujo si falla el envío
            Log::error('Error enviando correo con contrato firmado', [
                'adopcion_id' => $adopcion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
