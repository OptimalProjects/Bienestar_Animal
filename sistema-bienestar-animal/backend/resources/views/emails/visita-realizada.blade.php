<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Visita de Seguimiento</title>
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
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header.observaciones {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #333;
        }
        .header.critica {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
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
            color: #004884;
            margin: 0 0 5px 0;
        }
        .animal-card .codigo {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .resultado-badge {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            margin: 15px 0;
        }
        .resultado-satisfactoria {
            background-color: #d4edda;
            color: #155724;
        }
        .resultado-observaciones {
            background-color: #fff3cd;
            color: #856404;
        }
        .resultado-critica {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-tipo {
            background-color: #e3f2fd;
            color: #1565c0;
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
        .condicion-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .condicion-item:last-child {
            border-bottom: none;
        }
        .condicion-label {
            color: #666;
        }
        .condicion-value {
            font-weight: 600;
        }
        .condicion-excelente, .condicion-bueno, .condicion-alta, .condicion-adecuada, .condicion-amplio {
            color: #155724;
        }
        .condicion-regular, .condicion-media, .condicion-adecuado {
            color: #856404;
        }
        .condicion-malo, .condicion-deficiente, .condicion-baja, .condicion-reducido, .condicion-inadecuado {
            color: #721c24;
        }
        .observaciones-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .observaciones-box h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .observaciones-box p {
            margin: 0;
            color: #333;
            white-space: pre-line;
        }
        .recomendaciones-box {
            background-color: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .recomendaciones-box h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .recomendaciones-box p {
            margin: 0;
            color: #1565c0;
            white-space: pre-line;
        }
        .alerta-box {
            background-color: #f8d7da;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .alerta-box h4 {
            margin: 0 0 10px 0;
            color: #721c24;
        }
        .alerta-box p {
            margin: 0;
            color: #721c24;
        }
        .pdf-notice {
            background-color: #d4edda;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        .pdf-notice p {
            margin: 0;
            color: #155724;
            font-size: 14px;
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
        @php
            $headerClass = match($visita->resultado) {
                'satisfactoria' => '',
                'observaciones' => 'observaciones',
                'critica' => 'critica',
                default => '',
            };
        @endphp
        <div class="header {{ $headerClass }}">
            <h1>Resumen de Visita de Seguimiento</h1>
        </div>

        <div class="content">
            <p>
                Estimado/a <strong>{{ $adoptante->nombre_completo ?? $adoptante->nombres }}</strong>,
            </p>

            <p>
                Le informamos que se ha realizado la visita de seguimiento programada para su mascota adoptada.
                A continuacion encontrara el resumen de la evaluacion.
            </p>

            {{-- Tarjeta del Animal --}}
            <div class="animal-card">
                <p class="nombre">{{ $animal->nombre ?? 'Sin nombre' }}</p>
                <p class="codigo">{{ $animal->codigo_unico }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-tipo">{{ $tipoVisitaTexto }}</span>
                </div>
            </div>

            {{-- Resultado de la Visita --}}
            <div style="text-align: center; margin: 25px 0;">
                @php
                    $resultadoClass = 'resultado-' . ($visita->resultado ?? 'satisfactoria');
                @endphp
                <span class="resultado-badge {{ $resultadoClass }}">
                    Resultado: {{ $resultadoTexto }}
                </span>
            </div>

            {{-- Detalles de la Visita --}}
            <div class="info-section">
                <h3>Detalles de la Visita</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Tipo de Visita</div>
                        <div class="info-value">{{ $tipoVisitaTexto }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Fecha Realizada</div>
                        <div class="info-value">{{ $visita->fecha_realizada?->format('d/m/Y H:i') ?? 'No registrada' }}</div>
                    </div>
                    @if($visita->visitador)
                    <div class="info-item">
                        <div class="info-label">Funcionario</div>
                        <div class="info-value">{{ $visita->visitador->nombre_completo ?? $visita->visitador->nombres }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Condiciones del Hogar --}}
            @if($visita->condiciones_hogar && count($visita->condiciones_hogar) > 0)
            <div class="info-section">
                <h3>Condiciones del Hogar</h3>
                @if(isset($visita->condiciones_hogar['limpieza']))
                <div class="condicion-item">
                    <span class="condicion-label">Limpieza:</span>
                    <span class="condicion-value condicion-{{ $visita->condiciones_hogar['limpieza'] }}">
                        {{ ucfirst($visita->condiciones_hogar['limpieza']) }}
                    </span>
                </div>
                @endif
                @if(isset($visita->condiciones_hogar['espacio']))
                <div class="condicion-item">
                    <span class="condicion-label">Espacio:</span>
                    <span class="condicion-value condicion-{{ $visita->condiciones_hogar['espacio'] }}">
                        {{ ucfirst($visita->condiciones_hogar['espacio']) }}
                    </span>
                </div>
                @endif
                @if(isset($visita->condiciones_hogar['seguridad']))
                <div class="condicion-item">
                    <span class="condicion-label">Seguridad:</span>
                    <span class="condicion-value condicion-{{ $visita->condiciones_hogar['seguridad'] }}">
                        {{ ucfirst($visita->condiciones_hogar['seguridad']) }}
                    </span>
                </div>
                @endif
            </div>
            @endif

            {{-- Estado del Animal --}}
            @if($visita->estado_animal && count($visita->estado_animal) > 0)
            <div class="info-section">
                <h3>Estado del Animal</h3>
                @if(isset($visita->estado_animal['salud']))
                <div class="condicion-item">
                    <span class="condicion-label">Salud:</span>
                    <span class="condicion-value condicion-{{ $visita->estado_animal['salud'] }}">
                        {{ ucfirst($visita->estado_animal['salud']) }}
                    </span>
                </div>
                @endif
                @if(isset($visita->estado_animal['comportamiento']))
                <div class="condicion-item">
                    <span class="condicion-label">Comportamiento:</span>
                    <span class="condicion-value condicion-{{ $visita->estado_animal['comportamiento'] }}">
                        {{ ucfirst($visita->estado_animal['comportamiento']) }}
                    </span>
                </div>
                @endif
                @if(isset($visita->estado_animal['alimentacion']))
                <div class="condicion-item">
                    <span class="condicion-label">Alimentacion:</span>
                    <span class="condicion-value condicion-{{ $visita->estado_animal['alimentacion'] }}">
                        {{ ucfirst($visita->estado_animal['alimentacion']) }}
                    </span>
                </div>
                @endif
            </div>
            @endif

            {{-- Observaciones --}}
            @if($visita->observaciones)
            <div class="observaciones-box">
                <h4>Observaciones</h4>
                <p>{{ $visita->observaciones }}</p>
            </div>
            @endif

            {{-- Recomendaciones --}}
            @if($visita->recomendaciones)
            <div class="recomendaciones-box">
                <h4>Recomendaciones</h4>
                <p>{{ $visita->recomendaciones }}</p>
            </div>
            @endif

            {{-- Alerta para resultado critico --}}
            @if($visita->resultado === 'critica')
            <div class="alerta-box">
                <h4>Atencion Requerida</h4>
                <p>
                    La visita ha registrado un resultado critico. Un representante del programa
                    se pondra en contacto con usted para dar seguimiento a esta situacion.
                </p>
            </div>
            @endif

            {{-- Aviso de PDF adjunto --}}
            <div class="pdf-notice">
                <p>
                    Se adjunta el resumen completo de la visita en formato PDF para sus registros.
                    Tambien puede descargarlo desde el sistema en cualquier momento.
                </p>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/adopciones/{{ $adopcion->id }}/visitas"
                   class="cta-button">
                    Ver Historial de Visitas
                </a>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automatico generado por el Sistema de Bienestar Animal.<br>
                Si tiene alguna pregunta, comuniquese con el equipo de adopciones.
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
