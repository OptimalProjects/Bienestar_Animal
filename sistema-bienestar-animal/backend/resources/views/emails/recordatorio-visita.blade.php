<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Visita de Seguimiento</title>
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
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #333;
            padding: 24px;
            text-align: center;
        }
        .header.hoy {
            background: linear-gradient(135deg, #fd7e14 0%, #e06d10 100%);
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
        .countdown-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #ffc107;
        }
        .countdown-box.hoy {
            background: linear-gradient(135deg, #ffe5d0 0%, #ffd4b8 100%);
            border-color: #fd7e14;
        }
        .countdown-box .dias {
            font-size: 48px;
            font-weight: 700;
            color: #856404;
            margin: 0;
        }
        .countdown-box.hoy .dias {
            color: #c35a00;
        }
        .countdown-box .texto {
            font-size: 16px;
            color: #856404;
            margin: 5px 0 0 0;
        }
        .countdown-box.hoy .texto {
            color: #c35a00;
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
        .visita-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .visita-info h3 {
            margin: 0 0 15px 0;
            color: #004884;
            font-size: 16px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #666;
            font-weight: 600;
        }
        .info-value {
            color: #333;
            font-weight: 500;
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
        .preparacion-box {
            background-color: #d4edda;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .preparacion-box h4 {
            margin: 0 0 15px 0;
            color: #155724;
        }
        .preparacion-box ul {
            margin: 0;
            padding-left: 20px;
            color: #155724;
        }
        .preparacion-box li {
            margin-bottom: 8px;
        }
        .contacto-box {
            background-color: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .contacto-box h4 {
            margin: 0 0 10px 0;
            color: #0d47a1;
        }
        .contacto-box p {
            margin: 0;
            color: #1565c0;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $diasRestantes === 0 ? 'hoy' : '' }}">
            <h1>
                @if($diasRestantes === 0)
                    Visita de Seguimiento HOY
                @else
                    Recordatorio de Visita de Seguimiento
                @endif
            </h1>
        </div>

        <div class="content">
            <p>
                Estimado/a <strong>{{ $adoptante->nombre_completo ?? $adoptante->nombres }}</strong>,
            </p>

            @if($diasRestantes === 0)
            <p>
                Le recordamos que <strong>HOY</strong> esta programada una visita de seguimiento para
                verificar el bienestar de su mascota adoptada.
            </p>
            @else
            <p>
                Le recordamos que en <strong>{{ $diasRestantes }} dia(s)</strong> esta programada una visita
                de seguimiento para verificar el bienestar de su mascota adoptada.
            </p>
            @endif

            {{-- Contador de dias --}}
            <div class="countdown-box {{ $diasRestantes === 0 ? 'hoy' : '' }}">
                @if($diasRestantes === 0)
                    <p class="dias">HOY</p>
                    <p class="texto">{{ $visita->fecha_programada->format('d/m/Y') }}</p>
                @else
                    <p class="dias">{{ $diasRestantes }}</p>
                    <p class="texto">dia(s) restantes</p>
                @endif
            </div>

            {{-- Tarjeta del Animal --}}
            <div class="animal-card">
                <p class="nombre">{{ $animal->nombre ?? 'Sin nombre' }}</p>
                <p class="codigo">{{ $animal->codigo_unico }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-tipo">{{ $tipoVisitaTexto }}</span>
                </div>
            </div>

            {{-- Informacion de la Visita --}}
            <div class="visita-info">
                <h3>Detalles de la Visita</h3>
                <div class="info-row">
                    <span class="info-label">Tipo de Visita:</span>
                    <span class="info-value">{{ $tipoVisitaTexto }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha Programada:</span>
                    <span class="info-value">{{ $visita->fecha_programada->format('d/m/Y') }}</span>
                </div>
                @if($visita->visitador)
                <div class="info-row">
                    <span class="info-label">Funcionario Asignado:</span>
                    <span class="info-value">{{ $visita->visitador->nombre_completo ?? $visita->visitador->nombres }}</span>
                </div>
                @endif
            </div>

            {{-- Preparacion para la visita --}}
            <div class="preparacion-box">
                <h4>Preparese para la visita</h4>
                <ul>
                    <li>Asegurese de estar presente en el domicilio registrado</li>
                    <li>Tenga a mano el carnet de vacunacion del animal</li>
                    <li>Prepare cualquier documentacion veterinaria reciente</li>
                    <li>Si tiene consultas o inquietudes, este es un buen momento para compartirlas</li>
                    <li>El animal debe estar presente durante la visita</li>
                </ul>
            </div>

            {{-- Contacto para reprogramar --}}
            <div class="contacto-box">
                <h4>¿Necesita reprogramar?</h4>
                <p>
                    Si no puede estar disponible en la fecha indicada, por favor contactenos
                    lo antes posible para coordinar una nueva fecha. Las visitas de seguimiento
                    son un requisito del proceso de adopcion.
                </p>
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
