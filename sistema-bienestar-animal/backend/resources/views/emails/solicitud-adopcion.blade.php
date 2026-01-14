<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Adopción</title>
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
            background: linear-gradient(135deg, #004884 0%, #3366CC 100%);
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
            font-size: 40px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .badge-nueva {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .badge-aprobada {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .badge-rechazada {
            background-color: #ffebee;
            color: #c62828;
        }
        .badge-completada {
            background-color: #f3e5f5;
            color: #7b1fa2;
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
        .animal-card {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .animal-card h2 {
            margin: 0 0 10px 0;
            color: #2e7d32;
        }
        .animal-card .species {
            font-size: 14px;
            color: #558b2f;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #004884 0%, #3366CC 100%);
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
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
            <div class="logo">🐾</div>
            <h1>Sistema de Bienestar Animal</h1>
        </div>

        <div class="content">
            @if($tipo === 'nueva')
                <span class="badge badge-nueva">Nueva Solicitud</span>
                <h2>Se ha recibido una nueva solicitud de adopción</h2>
                <p>Un ciudadano ha mostrado interés en adoptar a uno de nuestros animales. A continuación los detalles:</p>
            @elseif($tipo === 'aprobada')
                <span class="badge badge-aprobada">Solicitud Aprobada</span>
                <h2>¡Buenas noticias!</h2>
                <p>La solicitud de adopción ha sido aprobada. El siguiente paso es la firma del contrato de adopción.</p>
            @elseif($tipo === 'rechazada')
                <span class="badge badge-rechazada">Solicitud Rechazada</span>
                <h2>Solicitud no aprobada</h2>
                <p>Lamentamos informar que la solicitud de adopción no ha sido aprobada en esta ocasión.</p>
            @elseif($tipo === 'completada')
                <span class="badge badge-completada">Adopción Completada</span>
                <h2>¡Felicidades!</h2>
                <p>La adopción se ha completado exitosamente. Un nuevo animal ha encontrado un hogar.</p>
            @endif

            <div class="animal-card">
                <h2>{{ $animal->nombre ?? 'Sin nombre' }}</h2>
                <p class="species">
                    {{ ucfirst($animal->especie ?? 'N/A') }}
                    @if($animal->raza) - {{ $animal->raza }} @endif
                </p>
                <p style="margin: 5px 0; font-size: 14px; color: #666;">
                    Código: <strong>{{ $animal->codigo_unico ?? 'N/A' }}</strong>
                </p>
            </div>

            <div class="info-section">
                <h3>Datos del Solicitante</h3>
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $adoptante->nombres ?? '' }} {{ $adoptante->apellidos ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Documento:</span>
                    <span class="info-value">{{ $adoptante->tipo_documento ?? '' }} {{ $adoptante->numero_documento ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $adoptante->telefono ?? 'No registrado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $adoptante->email ?? 'No registrado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $adoptante->direccion ?? 'No registrada' }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Detalles de la Solicitud</h3>
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value">{{ $adopcion->created_at ? $adopcion->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $adopcion->estado ?? 'pendiente')) }}</span>
                </div>
                @if($adopcion->motivo_adopcion)
                <div class="info-row">
                    <span class="info-label">Motivo:</span>
                    <span class="info-value">{{ $adopcion->motivo_adopcion }}</span>
                </div>
                @endif
                @if($adopcion->experiencia_mascotas)
                <div class="info-row">
                    <span class="info-label">Experiencia:</span>
                    <span class="info-value">{{ $adopcion->experiencia_mascotas }}</span>
                </div>
                @endif
            </div>

            @if($tipo === 'nueva')
                <p style="text-align: center;">
                    <strong>Por favor, revise esta solicitud a la brevedad posible.</strong>
                </p>
            @endif

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automático generado por el Sistema de Bienestar Animal.
                Por favor no responda a este mensaje.
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
