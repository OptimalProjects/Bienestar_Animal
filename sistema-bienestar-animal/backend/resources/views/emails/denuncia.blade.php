<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Denuncia</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header.asignada {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        .header.en_atencion {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }
        .header.resuelta {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        .header.cerrada {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .header .logo {
            font-size: 45px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .badge-nueva {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-asignada {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-en_atencion {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-resuelta {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-cerrada {
            background-color: #e2e3e5;
            color: #383d41;
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
            font-size: 28px;
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
            width: 140px;
            flex-shrink: 0;
        }
        .info-value {
            color: #333;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .priority-urgente {
            background-color: #dc3545;
            color: white;
        }
        .priority-alta {
            background-color: #fd7e14;
            color: white;
        }
        .priority-media {
            background-color: #ffc107;
            color: #333;
        }
        .priority-baja {
            background-color: #28a745;
            color: white;
        }
        .description-box {
            background-color: #fff8e1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .description-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .description-box p {
            margin: 0;
            color: #5d4037;
            white-space: pre-line;
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
        .action-notice {
            background-color: #d4edda;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .action-notice h4 {
            margin: 0 0 10px 0;
            color: #155724;
        }
        .action-notice p {
            margin: 0;
            color: #155724;
        }
        .action-notice ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
            color: #155724;
        }
        .action-notice li {
            margin-bottom: 5px;
        }
        .type-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .type-maltrato {
            background-color: #f8d7da;
            color: #721c24;
        }
        .type-abandono {
            background-color: #fff3cd;
            color: #856404;
        }
        .type-animal_herido {
            background-color: #cce5ff;
            color: #004085;
        }
        .type-animal_peligroso {
            background-color: #f5c6cb;
            color: #721c24;
        }
        .type-otro {
            background-color: #e2e3e5;
            color: #383d41;
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
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #004884 0%, #0066b3 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 15px 0;
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
        <div class="header {{ $tipo }}">
            <h1>
                @if($tipo === 'nueva')
                    Nueva Denuncia Asignada
                @elseif($tipo === 'asignada')
                    Denuncia Asignada
                @elseif($tipo === 'en_atencion')
                    Denuncia en Atención
                @elseif($tipo === 'resuelta')
                    Denuncia Resuelta
                @else
                    Actualización de Denuncia
                @endif
            </h1>
        </div>

        <div class="content">
            @php
                $badgeClass = match($tipo) {
                    'nueva' => 'badge-nueva',
                    'asignada' => 'badge-asignada',
                    'en_atencion' => 'badge-en_atencion',
                    'resuelta' => 'badge-resuelta',
                    'cerrada' => 'badge-cerrada',
                    default => 'badge-nueva',
                };
                $badgeText = match($tipo) {
                    'nueva' => 'Nueva Denuncia',
                    'asignada' => 'Denuncia Asignada',
                    'en_atencion' => 'En Atención',
                    'resuelta' => 'Resuelta',
                    'cerrada' => 'Cerrada',
                    default => 'Nueva Denuncia',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>

            @if($tipo === 'nueva' || $tipo === 'asignada')
                <h2 style="color: #dc3545; margin-bottom: 10px;">Se te ha asignado una nueva denuncia</h2>
                <p>
                    {{ $responsable->nombres ?? 'Estimado funcionario' }}, se te ha asignado una denuncia que requiere tu atención.
                    Por favor revisa los detalles a continuación y procede según corresponda.
                </p>
            @elseif($tipo === 'en_atencion')
                <h2 style="color: #856404; margin-bottom: 10px;">Operativo de rescate en curso</h2>
                <p>
                    Se ha iniciado un operativo de rescate para esta denuncia. El equipo está en camino o ya se encuentra en el lugar.
                </p>
            @elseif($tipo === 'resuelta')
                <h2 style="color: #155724; margin-bottom: 10px;">Denuncia atendida exitosamente</h2>
                <p>
                    La denuncia ha sido resuelta. A continuación encontrarás el resumen del caso.
                </p>
            @endif

            {{-- Ticket --}}
            <div class="ticket-box">
                <p class="ticket-label">Número de Ticket</p>
                <p class="ticket-number">{{ $denuncia->numero_ticket }}</p>
            </div>

            {{-- Tipo y Prioridad --}}
            <div style="text-align: center; margin: 20px 0;">
                @php
                    $tipoTexto = match($denuncia->tipo_denuncia) {
                        'maltrato' => 'Maltrato Animal',
                        'abandono' => 'Abandono',
                        'animal_herido' => 'Animal Herido',
                        'animal_peligroso' => 'Animal Peligroso',
                        'otro' => 'Otro',
                        default => $denuncia->tipo_denuncia ?? 'No especificado',
                    };
                    $typeClass = 'type-' . ($denuncia->tipo_denuncia ?? 'otro');
                @endphp
                <span class="type-badge {{ $typeClass }}">{{ $tipoTexto }}</span>
                <br>
                <span class="priority-badge priority-{{ $denuncia->prioridad ?? 'media' }}">
                    Prioridad: {{ ucfirst($denuncia->prioridad ?? 'Media') }}
                </span>
            </div>

            {{-- Ubicación --}}
            <div class="location-box">
                <h4>Ubicación del incidente</h4>
                <p><strong>{{ $denuncia->ubicacion ?? 'No especificada' }}</strong></p>
                @if($denuncia->latitud && $denuncia->longitud)
                    <p style="font-size: 12px; margin-top: 8px;">
                        Coordenadas: {{ $denuncia->latitud }}, {{ $denuncia->longitud }}
                    </p>
                @endif
            </div>

            {{-- Descripción --}}
            <div class="description-box">
                <h4>Descripción del caso</h4>
                <p>{{ $denuncia->descripcion ?? 'Sin descripción' }}</p>
            </div>

            {{-- Información del caso --}}
            <div class="info-section">
                <h3>Información del Caso</h3>
                <div class="info-row">
                    <span class="info-label">Fecha reporte:</span>
                    <span class="info-value">{{ $denuncia->fecha_denuncia ? $denuncia->fecha_denuncia->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Canal:</span>
                    <span class="info-value">{{ ucfirst($denuncia->canal_recepcion ?? 'Web') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $denuncia->estado ?? 'Recibida')) }}</span>
                </div>
                @if($denuncia->fecha_asignacion)
                <div class="info-row">
                    <span class="info-label">Fecha asignación:</span>
                    <span class="info-value">{{ $denuncia->fecha_asignacion->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @if($denuncia->fecha_resolucion)
                <div class="info-row">
                    <span class="info-label">Fecha resolución:</span>
                    <span class="info-value">{{ $denuncia->fecha_resolucion->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>

            {{-- Información del denunciante (si no es anónima) --}}
            @if(!$denuncia->es_anonima && $denunciante)
            <div class="info-section">
                <h3>Información del Denunciante</h3>
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $denunciante->nombre_completo ?? 'No disponible' }}</span>
                </div>
                @if($denunciante->telefono)
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $denunciante->telefono }}</span>
                </div>
                @endif
                @if($denunciante->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $denunciante->email }}</span>
                </div>
                @endif
            </div>
            @elseif($denuncia->es_anonima)
            <div class="info-section">
                <h3>Información del Denunciante</h3>
                <p style="color: #666; font-style: italic; margin: 0;">Esta denuncia fue realizada de forma anónima.</p>
            </div>
            @endif

            {{-- Acciones requeridas --}}
            @if($tipo === 'nueva' || $tipo === 'asignada')
            <div class="action-notice">
                <h4>Acciones requeridas</h4>
                <p>Por favor, realiza las siguientes acciones:</p>
                <ul>
                    <li>Revisa la información del caso detalladamente</li>
                    <li>Verifica la ubicación y planifica la ruta</li>
                    @if($denuncia->prioridad === 'urgente')
                        <li><strong>PRIORIDAD URGENTE:</strong> Requiere atención inmediata</li>
                    @elseif($denuncia->prioridad === 'alta')
                        <li><strong>PRIORIDAD ALTA:</strong> Atender dentro de las próximas 24 horas</li>
                    @endif
                    <li>Coordina con el equipo de rescate si es necesario</li>
                    <li>Actualiza el estado de la denuncia en el sistema</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/denuncias/{{ $denuncia->id }}"
                   class="cta-button">
                    Ver Denuncia en el Sistema
                </a>
            </div>
            @endif

            {{-- Observaciones de resolución --}}
            @if($tipo === 'resuelta' && $denuncia->observaciones_resolucion)
            <div class="info-section" style="background-color: #d4edda;">
                <h3 style="color: #155724;">Observaciones de Resolución</h3>
                <p style="margin: 0; color: #155724;">{{ $denuncia->observaciones_resolucion }}</p>
            </div>
            @endif

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automático generado por el Sistema de Bienestar Animal.<br>
                No respondas a este mensaje. Para cualquier consulta, utiliza los canales oficiales.
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
