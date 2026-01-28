<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitas de Seguimiento Programadas</title>
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
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
        .visitas-list {
            margin: 25px 0;
        }
        .visita-item {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 15px;
            border-left: 4px solid #17a2b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .visita-item .tipo {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }
        .visita-item .fecha {
            background-color: #17a2b8;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .info-box {
            background-color: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .info-box p {
            margin: 0;
            color: #1565c0;
        }
        .info-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
            color: #1565c0;
        }
        .info-box li {
            margin-bottom: 8px;
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
            .visita-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Visitas de Seguimiento Programadas</h1>
        </div>

        <div class="content">
            <p>
                Estimado/a <strong>{{ $adoptante->nombre_completo ?? $adoptante->nombres }}</strong>,
            </p>

            <p>
                Le informamos que se han programado las visitas de seguimiento post-adopcion para verificar
                el bienestar de su mascota adoptada. Estas visitas son parte del compromiso adquirido en el
                contrato de adopcion.
            </p>

            {{-- Tarjeta del Animal --}}
            <div class="animal-card">
                <p class="nombre">{{ $animal->nombre ?? 'Sin nombre' }}</p>
                <p class="codigo">{{ $animal->codigo_unico }}</p>
            </div>

            {{-- Lista de Visitas Programadas --}}
            <div class="visitas-list">
                <h3 style="color: #004884; margin-bottom: 15px;">Fechas de Visitas Programadas</h3>

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
                <div class="visita-item">
                    <span class="tipo">{{ $tipoTexto }}</span>
                    <span class="fecha">{{ $visita->fecha_programada->format('d/m/Y') }}</span>
                </div>
                @endforeach
            </div>

            {{-- Informacion Importante --}}
            <div class="info-box">
                <h4>Informacion Importante</h4>
                <p>Durante las visitas de seguimiento, un funcionario verificara:</p>
                <ul>
                    <li>El estado de salud y bienestar del animal</li>
                    <li>Las condiciones del hogar donde reside</li>
                    <li>El cumplimiento de los compromisos de adopcion</li>
                    <li>Cualquier necesidad o inquietud que pueda tener</li>
                </ul>
            </div>

            <div class="info-box" style="background-color: #fff8e1; border-left-color: #ffc107;">
                <h4 style="color: #856404;">Recomendaciones</h4>
                <ul style="color: #5d4037;">
                    <li>Asegurese de estar disponible en las fechas indicadas</li>
                    <li>Si necesita reprogramar una visita, contactenos con anticipacion</li>
                    <li>Tenga a mano la documentacion del animal (vacunas, desparasitaciones)</li>
                    <li>Prepare cualquier consulta o inquietud que desee compartir</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/adopciones/{{ $adopcion->id }}"
                   class="cta-button">
                    Ver Detalles de la Adopcion
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
