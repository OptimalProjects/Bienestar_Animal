<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Adopción Firmado</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .logo {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .success-badge {
            display: inline-block;
            background-color: #d4edda;
            color: #155724;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .animal-card {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }
        .animal-card h2 {
            margin: 0 0 10px 0;
            color: #2e7d32;
            font-size: 28px;
        }
        .animal-card .species {
            font-size: 16px;
            color: #558b2f;
        }
        .animal-card .code {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
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
            width: 160px;
            flex-shrink: 0;
        }
        .info-value {
            color: #333;
        }
        .attachment-notice {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .attachment-notice p {
            margin: 0;
            color: #0d47a1;
        }
        .attachment-notice .icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .recommendations {
            background-color: #fff8e1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .recommendations h4 {
            margin: 0 0 15px 0;
            color: #f57f17;
        }
        .recommendations ul {
            margin: 0;
            padding-left: 20px;
            color: #5d4037;
        }
        .recommendations li {
            margin-bottom: 8px;
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
        <div class="header">
            <div class="logo">🎉</div>
            <h1>¡Adopción Completada!</h1>
        </div>

        <div class="content">
            <span class="success-badge">Contrato Firmado Exitosamente</span>

            <h2 style="color: #2e7d32; margin-bottom: 10px;">¡Felicidades, {{ $adoptante->nombres ?? 'Adoptante' }}!</h2>

            <p>
                El proceso de adopción se ha completado exitosamente. Ahora eres oficialmente el responsable
                de <strong>{{ $animal->nombre ?? 'tu nuevo compañero' }}</strong>.
            </p>

            <div class="animal-card">
                <h2>{{ $animal->nombre ?? 'Tu nuevo amigo' }}</h2>
                <p class="species">
                    {{ ucfirst($animal->especie ?? 'N/A') }}
                    @if($animal->raza) - {{ $animal->raza }} @endif
                </p>
                <p class="code">Código: <strong>{{ $animal->codigo_unico ?? 'N/A' }}</strong></p>
            </div>

            <div class="attachment-notice">
                <p>
                    <span class="icon">📎</span>
                    <strong>Documento adjunto:</strong> Encontrarás tu contrato de adopción firmado en los archivos adjuntos de este correo. Te recomendamos guardarlo en un lugar seguro.
                </p>
            </div>

            <div class="info-section">
                <h3>Datos del Contrato</h3>
                <div class="info-row">
                    <span class="info-label">Fecha de firma:</span>
                    <span class="info-value">{{ $adopcion->fecha_entrega ? $adopcion->fecha_entrega->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Adoptante:</span>
                    <span class="info-value">{{ $adoptante->nombres ?? '' }} {{ $adoptante->apellidos ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Documento:</span>
                    <span class="info-value">{{ $adoptante->tipo_documento ?? '' }} {{ $adoptante->numero_documento ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Animal adoptado:</span>
                    <span class="info-value">{{ $animal->nombre ?? 'Sin nombre' }} ({{ $animal->codigo_unico }})</span>
                </div>
            </div>

            <div class="recommendations">
                <h4>Recuerda tus compromisos:</h4>
                <ul>
                    <li>Proporcionar alimentación adecuada y agua fresca permanentemente</li>
                    <li>Brindar atención veterinaria incluyendo vacunación y desparasitación</li>
                    <li>Mantener al animal en condiciones de bienestar apropiadas</li>
                    <li>Permitir las visitas de seguimiento programadas</li>
                    <li>No abandonar ni maltratar al animal</li>
                </ul>
            </div>

            <div class="info-section">
                <h3>Próximos pasos</h3>
                <p style="margin: 0; color: #555;">
                    Nuestro equipo realizará visitas de seguimiento para verificar el bienestar de
                    <strong>{{ $animal->nombre ?? 'tu mascota' }}</strong>. Estas visitas están programadas para:
                </p>
                <ul style="margin-top: 10px; color: #555;">
                    @if(isset($visitas) && $visitas->count() > 0)
                        @foreach($visitas as $visita)
                            @php
                                $tipoTexto = match($visita->tipo_visita) {
                                    'seguimiento_1mes' => 'Seguimiento 1 Mes',
                                    'seguimiento_3meses' => 'Seguimiento 3 Meses',
                                    'seguimiento_6meses' => 'Seguimiento 6 Meses',
                                    'extraordinaria' => 'Visita Extraordinaria',
                                    default => ucfirst(str_replace('_', ' ', $visita->tipo_visita)),
                                };
                            @endphp
                            <li><strong>{{ $visita->fecha_programada->format('d/m/Y') }}:</strong> {{ $tipoTexto }}</li>
                        @endforeach
                    @else
                        <li><strong>1 mes:</strong> Primera visita de seguimiento</li>
                        <li><strong>3 meses:</strong> Segunda visita de seguimiento</li>
                        <li><strong>6 meses:</strong> Tercera visita de seguimiento</li>
                    @endif
                </ul>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Gracias por elegir adoptar y darle una segunda oportunidad a un animal que lo necesitaba.<br>
                Si tienes alguna duda sobre el cuidado de tu mascota, no dudes en contactarnos.
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
