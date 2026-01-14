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
                <span class="badge badge-nueva">Solicitud en Revisión</span>
                <h2>¡Hemos recibido tu solicitud de adopción!</h2>
                <p>Gracias por tu interés en darle un hogar a uno de nuestros animales. Tu solicitud ha sido registrada exitosamente y se encuentra <strong>en proceso de revisión</strong> por nuestro equipo.</p>
                <p style="margin-top: 15px;">Te notificaremos por este medio cuando tengamos una respuesta. A continuación encontrarás el resumen de tu solicitud:</p>
            @elseif($tipo === 'aprobada')
                <span class="badge badge-aprobada">Solicitud Aprobada</span>
                <h2>¡Buenas noticias!</h2>
                <p>Tu solicitud de adopción ha sido <strong>aprobada</strong>. El siguiente paso es la firma del contrato de adopción. Nos pondremos en contacto contigo para coordinar los detalles.</p>
            @elseif($tipo === 'rechazada')
                <span class="badge badge-rechazada">Solicitud No Aprobada</span>
                <h2>Información sobre tu solicitud</h2>
                <p>Lamentamos informarte que tu solicitud de adopción no ha sido aprobada en esta ocasión. Te invitamos a conocer otros animales disponibles o a intentarlo nuevamente más adelante.</p>
            @elseif($tipo === 'completada')
                <span class="badge badge-completada">Adopción Completada</span>
                <h2>¡Felicidades por tu nuevo compañero!</h2>
                <p>La adopción se ha completado exitosamente. Gracias por abrir tu corazón y tu hogar. Recuerda que estaremos disponibles para cualquier consulta sobre el cuidado de tu nueva mascota.</p>
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
                <div style="background-color: #fff3cd; border-radius: 8px; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107;">
                    <p style="margin: 0; color: #856404;">
                        <strong>¿Qué sigue?</strong><br>
                        Nuestro equipo revisará tu solicitud y realizará una evaluación. Este proceso puede tomar algunos días hábiles. Te mantendremos informado(a) sobre el estado de tu solicitud.
                    </p>
                </div>
            @endif

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automático generado por el Sistema de Bienestar Animal.<br>
                Si tienes alguna duda, puedes comunicarte con nosotros a través de los canales oficiales de la Alcaldía de Santiago de Cali.
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
