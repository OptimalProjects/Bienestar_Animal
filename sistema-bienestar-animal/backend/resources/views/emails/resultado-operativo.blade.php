<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Operativo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f7fb;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        .header {
            padding: 24px;
            text-align: center;
            color: white;
        }
        .header.exitoso {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        .header.fallido {
            background: linear-gradient(135deg, #fd7e14 0%, #e55300 100%);
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .header .icon {
            font-size: 45px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .ticket-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border: 2px dashed #dee2e6;
        }
        .ticket-box .ticket-number {
            font-size: 24px;
            font-weight: 700;
            color: #004884;
            margin: 0;
        }
        .ticket-box .ticket-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .result-badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0;
        }
        .result-exitoso {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        .result-fallido {
            background-color: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }
        .info-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-section h3 {
            margin: 0 0 15px 0;
            color: #004884;
            font-size: 16px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            width: 150px;
            flex-shrink: 0;
        }
        .info-value {
            color: #333;
        }
        .location-box {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .location-box h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .location-box p {
            margin: 0;
            color: #1565c0;
        }
        .animal-card {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
        }
        .animal-card h4 {
            margin: 0 0 15px 0;
            color: #2e7d32;
            font-size: 16px;
        }
        .animal-card .info-row {
            margin-bottom: 8px;
        }
        .animal-card .info-label {
            color: #388e3c;
        }
        .animal-card .info-value {
            color: #1b5e20;
        }
        .observaciones-box {
            background-color: #fff8e1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .observaciones-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .observaciones-box p {
            margin: 0;
            color: #5d4037;
            white-space: pre-line;
        }
        .destino-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        .destino-refugio {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .destino-clinica_veterinaria {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .destino-hogar_paso {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .destino-liberado {
            background-color: #e0f7fa;
            color: #00838f;
        }
        .destino-fallecido {
            background-color: #fce4ec;
            color: #c62828;
        }
        .thank-you-box {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .thank-you-box h4 {
            margin: 0 0 10px 0;
            color: #2e7d32;
        }
        .thank-you-box p {
            margin: 0;
            color: #388e3c;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .footer .logo-alcaldia {
            font-weight: 600;
            color: #004884;
            margin-bottom: 5px;
        }
        @media only screen and (max-width: 480px) {
            .info-row {
                flex-direction: column;
            }
            .info-label {
                width: 100%;
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $exitoso ? 'exitoso' : 'fallido' }}">
            <h1>
                @if($exitoso)
                    Operativo Realizado Exitosamente
                @else
                    Resultado del Operativo
                @endif
            </h1>
        </div>

        <div class="content">
            <p class="greeting">
                Estimado(a) {{ $denunciante->nombre_completo ?? 'ciudadano(a)' }},
            </p>

            <p>
                Le informamos que el operativo relacionado con su denuncia ha sido ejecutado.
                A continuación encontrará los detalles del resultado:
            </p>

            {{-- Número de Ticket --}}
            <div class="ticket-box">
                <p class="ticket-label">Número de Denuncia</p>
                <p class="ticket-number">{{ $denuncia->numero_ticket }}</p>
            </div>

            {{-- Resultado del Operativo --}}
            <div style="text-align: center;">
                <span class="result-badge {{ $exitoso ? 'result-exitoso' : 'result-fallido' }}">
                    @if($exitoso)
                        Operativo Exitoso
                    @else
                        Operativo Concluido
                    @endif
                </span>
            </div>

            {{-- Información del Operativo --}}
            <div class="info-section">
                <h3>Detalles del Operativo</h3>
                <div class="info-row">
                    <span class="info-label">Fecha programada:</span>
                    <span class="info-value">
                        {{ $rescate->fecha_programada ? $rescate->fecha_programada->format('d/m/Y') : 'No especificada' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha ejecución:</span>
                    <span class="info-value">
                        {{ $rescate->fecha_ejecucion ? $rescate->fecha_ejecucion->format('d/m/Y H:i') : 'No registrada' }}
                    </span>
                </div>
                @if($rescate->equipo_rescate)
                <div class="info-row">
                    <span class="info-label">Equipo asignado:</span>
                    <span class="info-value">
                        {{ $rescate->equipo_rescate['nombre'] ?? 'Equipo de Bienestar Animal' }}
                    </span>
                </div>
                @endif
                @if($rescate->destino)
                <div class="info-row">
                    <span class="info-label">Destino:</span>
                    <span class="info-value">
                        @php
                            $destinoTextos = [
                                'refugio' => 'Refugio Municipal',
                                'clinica_veterinaria' => 'Clínica Veterinaria',
                                'hogar_paso' => 'Hogar de Paso',
                                'liberado' => 'Liberado en su Hábitat',
                                'fallecido' => 'Animal Fallecido',
                            ];
                        @endphp
                        <span class="destino-badge destino-{{ $rescate->destino }}">
                            {{ $destinoTextos[$rescate->destino] ?? ucfirst($rescate->destino) }}
                        </span>
                    </span>
                </div>
                @endif
            </div>

            {{-- Ubicación de la Denuncia --}}
            <div class="location-box">
                <h4>Ubicación del Incidente</h4>
                <p><strong>{{ $denuncia->ubicacion ?? 'No especificada' }}</strong></p>
            </div>

            {{-- Animal Rescatado (si aplica) --}}
            @if($animalRescatado)
            <div class="animal-card">
                <h4>Animal Rescatado</h4>
                <div class="info-row">
                    <span class="info-label">Especie:</span>
                    <span class="info-value">{{ ucfirst($animalRescatado->especie ?? 'No especificada') }}</span>
                </div>
                @if($animalRescatado->raza)
                <div class="info-row">
                    <span class="info-label">Raza:</span>
                    <span class="info-value">{{ $animalRescatado->raza }}</span>
                </div>
                @endif
                @if($animalRescatado->color)
                <div class="info-row">
                    <span class="info-label">Color:</span>
                    <span class="info-value">{{ $animalRescatado->color }}</span>
                </div>
                @endif
                @if($rescate->estado_animal_rescate)
                <div class="info-row">
                    <span class="info-label">Estado al rescate:</span>
                    <span class="info-value">{{ ucfirst($rescate->estado_animal_rescate) }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Código registro:</span>
                    <span class="info-value">{{ $animalRescatado->codigo ?? $animalRescatado->id }}</span>
                </div>
            </div>
            @elseif($exitoso)
            <div class="animal-card">
                <h4>Animal Atendido</h4>
                <p style="margin: 0; color: #388e3c;">
                    El animal fue atendido durante el operativo. Los detalles específicos se encuentran
                    registrados en el sistema.
                </p>
            </div>
            @endif

            {{-- Observaciones --}}
            @if($rescate->observaciones)
            <div class="observaciones-box">
                <h4>Observaciones del Operativo</h4>
                <p>{{ $rescate->observaciones }}</p>
            </div>
            @endif

            {{-- Motivo de fallo (si no fue exitoso) --}}
            @if(!$exitoso && $rescate->motivo_fallo)
            <div class="info-section" style="background-color: #fff3cd;">
                <h3 style="color: #856404;">Motivo</h3>
                <p style="margin: 0; color: #856404;">{{ $rescate->motivo_fallo }}</p>
            </div>
            @endif

            {{-- Agradecimiento --}}
            <div class="thank-you-box">
                <h4>Gracias por su Colaboración</h4>
                <p>
                    Su denuncia fue fundamental para que pudiéramos actuar en favor del bienestar animal.
                    Gracias a ciudadanos comprometidos como usted, podemos proteger a los animales de nuestra ciudad.
                </p>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automático generado por el Sistema de Bienestar Animal.<br>
                Para consultas adicionales sobre su denuncia, puede comunicarse a través de nuestros canales oficiales
                citando el número de ticket <strong>{{ $denuncia->numero_ticket }}</strong>.
            </p>
        </div>

        <div class="footer">
            <div class="logo-alcaldia">Alcaldía de Santiago de Cali</div>
            <p>Sistema de Bienestar Animal - Secretaría de Salud Pública</p>
            <p>© {{ date('Y') }} Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
