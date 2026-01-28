<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Animal Registrado</title>
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
        .animal-card .codigo {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .animal-card .nombre {
            font-size: 28px;
            font-weight: 700;
            color: #004884;
            margin: 0;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin: 5px 3px;
        }
        .badge-especie {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .badge-sexo-macho {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .badge-sexo-hembra {
            background-color: #fce4ec;
            color: #c2185b;
        }
        .badge-sexo-desconocido {
            background-color: #f5f5f5;
            color: #616161;
        }
        .estado-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .estado-en_refugio {
            background-color: #fff3cd;
            color: #856404;
        }
        .estado-en_tratamiento {
            background-color: #f8d7da;
            color: #721c24;
        }
        .estado-en_adopcion {
            background-color: #d4edda;
            color: #155724;
        }
        .estado-en_calle {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .salud-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .salud-critico {
            background-color: #dc3545;
            color: white;
        }
        .salud-grave {
            background-color: #fd7e14;
            color: white;
        }
        .salud-estable {
            background-color: #ffc107;
            color: #333;
        }
        .salud-bueno {
            background-color: #28a745;
            color: white;
        }
        .salud-excelente {
            background-color: #20c997;
            color: white;
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
        .action-notice {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .action-notice h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .action-notice p {
            margin: 0;
            color: #1565c0;
        }
        .action-notice ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
            color: #1565c0;
        }
        .action-notice li {
            margin-bottom: 5px;
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
        <div class="header">
            <h1>Nuevo Animal Registrado</h1>
        </div>

        <div class="content">
            @if($veterinario)
                <p>
                    Estimado/a <strong>{{ $veterinario->nombres }} {{ $veterinario->apellidos }}</strong>,
                </p>
            @else
                <p>Estimado equipo veterinario,</p>
            @endif

            <p>
                Se ha registrado un nuevo animal en el Sistema de Bienestar Animal que requiere atención veterinaria.
                A continuación encontrarás los detalles del ingreso.
            </p>

            {{-- Tarjeta del Animal --}}
            <div class="animal-card">
                <p class="codigo">{{ $animal->codigo_unico }}</p>
                <p class="nombre">{{ $animal->nombre ?? 'Sin nombre asignado' }}</p>
                <div style="margin-top: 15px;">
                    @php
                        $especieTexto = match($animal->especie) {
                            'canino' => 'Canino',
                            'felino' => 'Felino',
                            'ave' => 'Ave',
                            'roedor' => 'Roedor',
                            'reptil' => 'Reptil',
                            default => ucfirst($animal->especie ?? 'No especificada'),
                        };
                    @endphp
                    <span class="badge badge-especie">{{ $especieTexto }}</span>

                    @php
                        $sexoClass = match($animal->sexo) {
                            'macho' => 'badge-sexo-macho',
                            'hembra' => 'badge-sexo-hembra',
                            default => 'badge-sexo-desconocido',
                        };
                        $sexoTexto = match($animal->sexo) {
                            'macho' => 'Macho',
                            'hembra' => 'Hembra',
                            default => 'Sexo desconocido',
                        };
                    @endphp
                    <span class="badge {{ $sexoClass }}">{{ $sexoTexto }}</span>
                </div>
            </div>

            {{-- Estado y Salud --}}
            <div style="text-align: center; margin: 20px 0;">
                @php
                    $estadoTexto = match($animal->estado) {
                        'en_calle' => 'En calle',
                        'en_refugio' => 'En refugio',
                        'en_adopcion' => 'En adopción',
                        'adoptado' => 'Adoptado',
                        'fallecido' => 'Fallecido',
                        'en_tratamiento' => 'En tratamiento',
                        default => ucfirst(str_replace('_', ' ', $animal->estado ?? 'No especificado')),
                    };
                    $estadoClass = 'estado-' . ($animal->estado ?? 'en_refugio');
                @endphp
                <span class="estado-badge {{ $estadoClass }}">Estado: {{ $estadoTexto }}</span>
                <br><br>

                @if($animal->estado_salud)
                    @php
                        $saludTexto = match($animal->estado_salud) {
                            'critico' => 'Critico',
                            'grave' => 'Grave',
                            'estable' => 'Estable',
                            'bueno' => 'Bueno',
                            'excelente' => 'Excelente',
                            default => ucfirst($animal->estado_salud),
                        };
                        $saludClass = 'salud-' . $animal->estado_salud;
                    @endphp
                    <span class="salud-badge {{ $saludClass }}">Estado de Salud: {{ $saludTexto }}</span>
                @endif
            </div>

            {{-- Información del Animal --}}
            <div class="info-section">
                <h3>Datos del Animal</h3>
                <div class="info-grid">
                    @if($animal->raza)
                    <div class="info-item">
                        <div class="info-label">Raza</div>
                        <div class="info-value">{{ $animal->raza }}</div>
                    </div>
                    @endif

                    @if($animal->edad_aproximada)
                    <div class="info-item">
                        <div class="info-label">Edad aproximada</div>
                        <div class="info-value">
                            @if($animal->edad_aproximada >= 12)
                                {{ floor($animal->edad_aproximada / 12) }} {{ floor($animal->edad_aproximada / 12) == 1 ? 'año' : 'años' }}
                                @if($animal->edad_aproximada % 12 > 0)
                                    y {{ $animal->edad_aproximada % 12 }} {{ ($animal->edad_aproximada % 12) == 1 ? 'mes' : 'meses' }}
                                @endif
                            @else
                                {{ $animal->edad_aproximada }} {{ $animal->edad_aproximada == 1 ? 'mes' : 'meses' }}
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($animal->peso_actual)
                    <div class="info-item">
                        <div class="info-label">Peso</div>
                        <div class="info-value">{{ number_format($animal->peso_actual, 1) }} kg</div>
                    </div>
                    @endif

                    @if($animal->color)
                    <div class="info-item">
                        <div class="info-label">Color</div>
                        <div class="info-value">{{ $animal->color }}</div>
                    </div>
                    @endif

                    @if($animal->tamanio)
                    <div class="info-item">
                        <div class="info-label">Tamaño</div>
                        <div class="info-value">
                            @php
                                $tamanioTexto = match($animal->tamanio) {
                                    'pequenio' => 'Pequeño',
                                    'mediano' => 'Mediano',
                                    'grande' => 'Grande',
                                    'muy_grande' => 'Muy grande',
                                    default => ucfirst($animal->tamanio),
                                };
                            @endphp
                            {{ $tamanioTexto }}
                        </div>
                    </div>
                    @endif

                    <div class="info-item">
                        <div class="info-label">Esterilizado</div>
                        <div class="info-value">{{ $animal->esterilizacion ? 'Sí' : 'No' }}</div>
                    </div>
                </div>
            </div>

            {{-- Información del Rescate --}}
            @if($animal->fecha_rescate || $animal->ubicacion_rescate)
            <div class="info-section">
                <h3>Información del Rescate</h3>
                @if($animal->fecha_rescate)
                <div class="info-item">
                    <div class="info-label">Fecha de rescate</div>
                    <div class="info-value">{{ $animal->fecha_rescate->format('d/m/Y') }}</div>
                </div>
                @endif
                @if($animal->ubicacion_rescate)
                <div class="info-item">
                    <div class="info-label">Ubicación del rescate</div>
                    <div class="info-value">{{ $animal->ubicacion_rescate }}</div>
                </div>
                @endif
            </div>
            @endif

            {{-- Señas particulares --}}
            @if($animal->senias_particulares)
            <div class="info-section">
                <h3>Señas Particulares</h3>
                <p style="margin: 0; color: #333;">{{ $animal->senias_particulares }}</p>
            </div>
            @endif

            {{-- Observaciones --}}
            @if($animal->observaciones)
            <div class="observaciones-box">
                <h4>Observaciones</h4>
                <p>{{ $animal->observaciones }}</p>
            </div>
            @endif

            {{-- Acciones requeridas --}}
            <div class="action-notice">
                <h4>Acciones recomendadas</h4>
                <p>Por favor, considera las siguientes acciones:</p>
                <ul>
                    <li>Revisar el historial clínico del animal en el sistema</li>
                    <li>Programar una evaluación veterinaria inicial si es necesario</li>
                    @if($animal->estado_salud === 'critico' || $animal->estado_salud === 'grave')
                        <li><strong>ATENCIÓN PRIORITARIA:</strong> El animal presenta un estado de salud {{ $animal->estado_salud }}</li>
                    @endif
                    @if(!$animal->esterilizacion)
                        <li>Evaluar programación de esterilización</li>
                    @endif
                    <li>Actualizar el estado de salud después de la evaluación</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/animales/{{ $animal->id }}"
                   class="cta-button">
                    Ver Animal en el Sistema
                </a>
            </div>

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
