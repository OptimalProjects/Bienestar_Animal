<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmacion de Devolucion</title>
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
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .header .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .animal-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #dee2e6;
        }
        .animal-card .nombre {
            font-size: 24px;
            font-weight: 700;
            color: #495057;
            margin: 0 0 5px 0;
        }
        .animal-card .codigo {
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
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            color: #333;
            font-size: 15px;
            margin-top: 2px;
        }
        .estado-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .estado-bueno {
            background-color: #d4edda;
            color: #155724;
        }
        .estado-regular {
            background-color: #fff3cd;
            color: #856404;
        }
        .estado-malo {
            background-color: #f8d7da;
            color: #721c24;
        }
        .estado-critico {
            background-color: #721c24;
            color: white;
        }
        .motivo-box {
            background-color: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .motivo-box h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .motivo-box p {
            margin: 0;
            color: #1565c0;
        }
        .descripcion-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .descripcion-box h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .descripcion-box p {
            margin: 0;
            color: #333;
            white-space: pre-line;
        }
        .revision-notice {
            background-color: #fff8e1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .revision-notice h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .revision-notice p {
            margin: 0;
            color: #5d4037;
        }
        .pdf-notice {
            background-color: #e8f5e9;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #c8e6c9;
        }
        .pdf-notice p {
            margin: 0;
            color: #2e7d32;
            font-size: 14px;
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
        @media only screen and (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">📋</div>
            <h1>Confirmacion de Devolucion</h1>
        </div>

        <div class="content">
            <p>
                Estimado/a <strong>{{ $adoptante->nombre_completo ?? $adoptante->nombres }}</strong>,
            </p>

            <p>
                Le confirmamos que hemos recibido y registrado la devolucion del animal que tenia bajo su cuidado.
                A continuacion encontrara el resumen del proceso.
            </p>

            {{-- Tarjeta del Animal --}}
            <div class="animal-card">
                <p class="nombre">{{ $animal->nombre ?? 'Sin nombre' }}</p>
                <p class="codigo">{{ $animal->codigo_unico }}</p>
                <div style="margin-top: 15px;">
                    @php
                        $estadoClass = match($devolucion->estado_animal_devolucion) {
                            'bueno' => 'estado-bueno',
                            'regular' => 'estado-regular',
                            'malo' => 'estado-malo',
                            'critico' => 'estado-critico',
                            default => 'estado-regular',
                        };
                    @endphp
                    <span class="estado-badge {{ $estadoClass }}">
                        Estado: {{ $estadoAnimalTexto }}
                    </span>
                </div>
            </div>

            {{-- Motivo de Devolucion --}}
            <div class="motivo-box">
                <h4>Motivo de Devolucion</h4>
                <p><strong>{{ $motivoTexto }}</strong></p>
            </div>

            {{-- Descripcion si existe --}}
            @if($devolucion->descripcion_motivo)
            <div class="descripcion-box">
                <h4>Descripcion</h4>
                <p>{{ $devolucion->descripcion_motivo }}</p>
            </div>
            @endif

            {{-- Detalles del Proceso --}}
            <div class="info-section">
                <h3>Detalles del Proceso</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Fecha de Devolucion</div>
                        <div class="info-value">{{ $devolucion->fecha_devolucion?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Codigo de Adopcion</div>
                        <div class="info-value">{{ $adopcion->codigo ?? 'ADP-' . $adopcion->id }}</div>
                    </div>
                    @if($devolucion->registradoPor)
                    <div class="info-item">
                        <div class="info-label">Registrado por</div>
                        <div class="info-value">{{ $devolucion->registradoPor->nombre_completo ?? $devolucion->registradoPor->nombres }}</div>
                    </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Estado del Proceso</div>
                        <div class="info-value">{{ $devolucion->estado_proceso_texto ?? 'Recibido' }}</div>
                    </div>
                </div>
            </div>

            {{-- Observaciones del estado --}}
            @if($devolucion->observaciones_estado)
            <div class="descripcion-box">
                <h4>Observaciones sobre el Estado del Animal</h4>
                <p>{{ $devolucion->observaciones_estado }}</p>
            </div>
            @endif

            {{-- Aviso de revision veterinaria --}}
            @if($devolucion->fecha_revision_programada)
            <div class="revision-notice">
                <h4>Revision Veterinaria Programada</h4>
                <p>
                    El animal sera evaluado por el equipo veterinario el dia
                    <strong>{{ $devolucion->fecha_revision_programada->format('d/m/Y') }}</strong>
                    para determinar su estado de salud y proximos pasos.
                </p>
            </div>
            @endif

            {{-- Aviso de PDF adjunto --}}
            <div class="pdf-notice">
                <p>
                    Se adjunta el resumen completo de la devolucion en formato PDF para sus registros.
                </p>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Agradecemos el tiempo que dedicó al cuidado de este animal.<br>
                Si tiene alguna pregunta sobre este proceso, no dude en contactarnos.
            </p>
        </div>

        <div class="footer">
            <div class="logo-alcaldia">Alcaldia de Santiago de Cali</div>
            <p>Sistema de Bienestar Animal - Secretaria de Salud Publica</p>
            <p>&copy; {{ date('Y') }} Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
