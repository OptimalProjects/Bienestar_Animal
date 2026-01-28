<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Cirugía</title>
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
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .header.exitosa {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        .header.complicaciones {
            background: linear-gradient(135deg, #fd7e14 0%, #e06d10 100%);
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
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin: 5px 3px;
        }
        .badge-tipo {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .resultado-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 15px;
            font-weight: 600;
        }
        .resultado-exitosa {
            background-color: #d4edda;
            color: #155724;
        }
        .resultado-con_complicaciones {
            background-color: #fff3cd;
            color: #856404;
        }
        .resultado-fallida {
            background-color: #f8d7da;
            color: #721c24;
        }
        .estado-animal-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .estado-en_tratamiento {
            background-color: #fff3cd;
            color: #856404;
        }
        .estado-en_recuperacion {
            background-color: #cce5ff;
            color: #004085;
        }
        .estado-estable {
            background-color: #d4edda;
            color: #155724;
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
        .postoperatorio-box {
            background-color: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .postoperatorio-box h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .postoperatorio-box p {
            margin: 0;
            color: #1565c0;
            white-space: pre-line;
        }
        .complicaciones-box {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .complicaciones-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .complicaciones-box p {
            margin: 0;
            color: #856404;
            white-space: pre-line;
        }
        .recordatorios-box {
            background-color: #d4edda;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .recordatorios-box h4 {
            margin: 0 0 15px 0;
            color: #155724;
        }
        .recordatorios-box ul {
            margin: 0;
            padding-left: 20px;
            color: #155724;
        }
        .recordatorios-box li {
            margin-bottom: 8px;
        }
        .seguimiento-alert {
            background-color: #cce5ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #004085;
            text-align: center;
        }
        .seguimiento-alert h4 {
            margin: 0 0 10px 0;
            color: #004085;
        }
        .seguimiento-alert p {
            margin: 0;
            color: #004085;
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
        .veterinario-info {
            background-color: #f1f3f4;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .veterinario-info p {
            margin: 5px 0;
            color: #555;
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
            $headerClass = match($cirugia->resultado) {
                'exitosa' => 'exitosa',
                'con_complicaciones' => 'complicaciones',
                default => '',
            };
        @endphp
        <div class="header {{ $headerClass }}">
            <h1>Notificación de Cirugía</h1>
        </div>

        <div class="content">
            <p>
                Estimado/a <strong>{{ $adoptante->nombre_completo ?? $adoptante->nombres }}</strong>,
            </p>

            <p>
                Le informamos que se ha realizado un procedimiento quirúrgico a su mascota.
                A continuación encontrará el resumen completo de la intervención.
            </p>

            {{-- Tarjeta del Animal --}}
            <div class="animal-card">
                <p class="nombre">{{ $animal->nombre ?? 'Sin nombre' }}</p>
                <p class="codigo">{{ $animal->codigo_unico }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-tipo">{{ $tipoCirugiaTexto }}</span>
                </div>
            </div>

            {{-- Resultado de la Cirugía --}}
            <div style="text-align: center; margin: 25px 0;">
                @php
                    $resultadoTexto = match($cirugia->resultado) {
                        'exitosa' => 'Cirugía Exitosa',
                        'con_complicaciones' => 'Cirugía con Complicaciones',
                        'fallida' => 'Cirugía No Exitosa',
                        default => 'Cirugía Realizada',
                    };
                    $resultadoClass = 'resultado-' . ($cirugia->resultado ?? 'exitosa');
                @endphp
                <span class="resultado-badge {{ $resultadoClass }}">{{ $resultadoTexto }}</span>

                @if($cirugia->estado_animal)
                <br><br>
                @php
                    $estadoAnimalTexto = match($cirugia->estado_animal) {
                        'en_tratamiento' => 'En Tratamiento',
                        'en_recuperacion' => 'En Recuperación',
                        'estable' => 'Estable',
                        default => ucfirst(str_replace('_', ' ', $cirugia->estado_animal)),
                    };
                    $estadoAnimalClass = 'estado-' . $cirugia->estado_animal;
                @endphp
                <span class="estado-animal-badge {{ $estadoAnimalClass }}">Estado del Animal: {{ $estadoAnimalTexto }}</span>
                @endif
            </div>

            {{-- Detalles de la Cirugía --}}
            <div class="info-section">
                <h3>Detalles de la Cirugía</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Tipo de Cirugía</div>
                        <div class="info-value">{{ $tipoCirugiaTexto }}</div>
                    </div>

                    @if($cirugia->fecha_realizacion)
                    <div class="info-item">
                        <div class="info-label">Fecha de Realización</div>
                        <div class="info-value">{{ $cirugia->fecha_realizacion->format('d/m/Y H:i') }}</div>
                    </div>
                    @elseif($cirugia->fecha_programada)
                    <div class="info-item">
                        <div class="info-label">Fecha Programada</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($cirugia->fecha_programada)->format('d/m/Y') }}</div>
                    </div>
                    @endif

                    @if($cirugia->duracion)
                    <div class="info-item">
                        <div class="info-label">Duración</div>
                        <div class="info-value">
                            @if($cirugia->duracion >= 60)
                                {{ floor($cirugia->duracion / 60) }}h {{ $cirugia->duracion % 60 }}min
                            @else
                                {{ $cirugia->duracion }} minutos
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($cirugia->tipo_anestesia)
                    <div class="info-item">
                        <div class="info-label">Tipo de Anestesia</div>
                        <div class="info-value">{{ $cirugia->tipo_anestesia }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Descripción --}}
            @if($cirugia->descripcion)
            <div class="info-section">
                <h3>Descripción del Procedimiento</h3>
                <p style="margin: 0; color: #333; white-space: pre-line;">{{ $cirugia->descripcion }}</p>
            </div>
            @endif

            {{-- Veterinario Cirujano --}}
            @if($cirugia->cirujano)
            <div class="veterinario-info">
                <p><strong>Cirujano:</strong> Dr(a). {{ $cirugia->cirujano->nombres }} {{ $cirugia->cirujano->apellidos }}</p>
                @if($cirugia->cirujano->numero_tarjeta_profesional)
                <p><small>Tarjeta Profesional: {{ $cirugia->cirujano->numero_tarjeta_profesional }}</small></p>
                @endif
            </div>
            @endif

            {{-- Complicaciones --}}
            @if($cirugia->complicaciones)
            <div class="complicaciones-box">
                <h4>Complicaciones Registradas</h4>
                <p>{{ $cirugia->complicaciones }}</p>
            </div>
            @endif

            {{-- Notas Postoperatorias --}}
            @if($cirugia->postoperatorio)
            <div class="postoperatorio-box">
                <h4>Indicaciones Postoperatorias</h4>
                <p>{{ $cirugia->postoperatorio }}</p>
            </div>
            @endif

            {{-- Recordatorios de Cuidado --}}
            <div class="recordatorios-box">
                <h4>Recordatorios Importantes para el Cuidado</h4>
                <ul>
                    <li><strong>Reposo:</strong> Mantenga a su mascota en un lugar tranquilo y cómodo durante los primeros días.</li>
                    <li><strong>Herida:</strong> Vigile la zona de la incisión. No permita que su mascota se lama o muerda los puntos.</li>
                    <li><strong>Collar isabelino:</strong> Use el collar protector si fue indicado para evitar que se toque la herida.</li>
                    <li><strong>Alimentación:</strong> Ofrezca alimento en pequeñas cantidades las primeras 24 horas.</li>
                    <li><strong>Medicamentos:</strong> Administre los medicamentos recetados según las indicaciones del veterinario.</li>
                    <li><strong>Actividad:</strong> Evite saltos, carreras y juegos bruscos durante el período de recuperación.</li>
                    @if($cirugia->tipo_cirugia === 'esterilizacion' || $cirugia->tipo_cirugia === 'castracion')
                    <li><strong>Puntos:</strong> Los puntos generalmente se retiran entre 10-14 días después de la cirugía.</li>
                    @endif
                    <li><strong>Signos de alerta:</strong> Contacte al veterinario si nota sangrado excesivo, inflamación severa, fiebre, vómitos persistentes o falta de apetito prolongada.</li>
                </ul>
            </div>

            {{-- Seguimiento Requerido --}}
            @if($cirugia->seguimiento_requerido)
            <div class="seguimiento-alert">
                <h4>Seguimiento Requerido</h4>
                <p>
                    Esta cirugía requiere seguimiento veterinario. Por favor, agende una cita de control
                    para verificar la evolución de su mascota.
                </p>
            </div>
            @endif

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/animales/{{ $animal->id }}"
                   class="cta-button">
                    Ver Historial del Animal
                </a>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automático generado por el Sistema de Bienestar Animal.<br>
                Para cualquier consulta sobre la cirugía, comuníquese con el equipo veterinario.
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
