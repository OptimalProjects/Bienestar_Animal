<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Denuncia</title>
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
            padding: 24px;
            text-align: center;
            color: white;
        }
        .header.recibida {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        }
        .header.en_revision {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        .header.asignada {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        .header.en_atencion {
            background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
        }
        .header.resuelta {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        .header.cerrada {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        .header.desestimada {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
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
            font-size: 24px;
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
        .status-timeline {
            margin: 30px 0;
            padding: 0;
        }
        .status-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            position: relative;
        }
        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .status-icon.completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-icon.current {
            background-color: #007bff;
            color: white;
            animation: pulse 2s infinite;
        }
        .status-icon.pending {
            background-color: #e9ecef;
            color: #6c757d;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
        }
        .status-content h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 16px;
        }
        .status-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .message-box {
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .message-box.info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        .message-box.success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }
        .message-box.warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .message-box.danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .message-box h4 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .message-box p {
            margin: 0;
            font-size: 14px;
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
        .next-steps {
            background-color: #e8f5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .next-steps h4 {
            margin: 0 0 10px 0;
            color: #2e7d32;
        }
        .next-steps p {
            margin: 0;
            color: #388e3c;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
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
        <div class="header {{ $nuevoEstado }}">
            <h1>
                @switch($nuevoEstado)
                    @case('recibida')
                        Denuncia Recibida
                        @break
                    @case('en_revision')
                        En Revisión
                        @break
                    @case('asignada')
                        Equipo Asignado
                        @break
                    @case('en_atencion')
                        Operativo en Curso
                        @break
                    @case('resuelta')
                        Denuncia Resuelta
                        @break
                    @case('cerrada')
                        Caso Cerrado
                        @break
                    @case('desestimada')
                        Denuncia Desestimada
                        @break
                    @default
                        Actualización de Denuncia
                @endswitch
            </h1>
        </div>

        <div class="content">
            <p class="greeting">
                Estimado(a) {{ $denunciante->nombre_completo ?? 'ciudadano(a)' }},
            </p>

            {{-- Mensaje principal según el estado --}}
            @switch($nuevoEstado)
                @case('recibida')
                    <p>
                        Hemos recibido su denuncia exitosamente. Su caso ha sido registrado en nuestro sistema
                        y será revisado por nuestro equipo a la brevedad.
                    </p>
                    @break

                @case('en_revision')
                    <p>
                        Le informamos que su denuncia está siendo revisada por nuestro equipo técnico.
                        Estamos evaluando la información proporcionada para determinar las acciones a seguir.
                    </p>
                    @break

                @case('asignada')
                    <p>
                        Nos complace informarle que se ha asignado un equipo de trabajo a su denuncia.
                        Un operador de rescate está revisando su caso y coordinará las acciones necesarias.
                    </p>
                    @break

                @case('en_atencion')
                    <p>
                        Le informamos que se ha iniciado un <strong>operativo de rescate</strong> para atender su denuncia.
                        Nuestro equipo está en camino o ya se encuentra en el lugar reportado.
                    </p>
                    @break

                @case('resuelta')
                    <p>
                        Nos complace informarle que su denuncia ha sido <strong>resuelta satisfactoriamente</strong>.
                        Gracias a su colaboración, pudimos atender este caso de bienestar animal.
                    </p>
                    @break

                @case('cerrada')
                    <p>
                        Le informamos que el caso correspondiente a su denuncia ha sido <strong>cerrado</strong>.
                        Todas las acciones pertinentes han sido completadas.
                    </p>
                    @break

                @case('desestimada')
                    <p>
                        Después de revisar cuidadosamente la información proporcionada, lamentamos informarle
                        que su denuncia no pudo ser procesada. Esto puede deberse a diversas razones como
                        información insuficiente, duplicidad o estar fuera de nuestra jurisdicción.
                    </p>
                    @break
            @endswitch

            {{-- Número de Ticket --}}
            <div class="ticket-box">
                <p class="ticket-label">Número de Seguimiento</p>
                <p class="ticket-number">{{ $denuncia->numero_ticket }}</p>
            </div>

            {{-- Timeline de estados --}}
            <div class="status-timeline">
                @php
                    $estados = ['recibida', 'en_revision', 'asignada', 'en_atencion', 'resuelta'];
                    $estadoActualIndex = array_search($nuevoEstado, $estados);
                    if ($estadoActualIndex === false) $estadoActualIndex = -1;

                    $estadoLabels = [
                        'recibida' => 'Recibida',
                        'en_revision' => 'En Revisión',
                        'asignada' => 'Asignada',
                        'en_atencion' => 'En Atención',
                        'resuelta' => 'Resuelta',
                    ];
                @endphp

                @foreach($estados as $index => $estado)
                    <div class="status-step">
                        <div class="status-icon {{ $index < $estadoActualIndex ? 'completed' : ($index == $estadoActualIndex ? 'current' : 'pending') }}">
                            @if($index < $estadoActualIndex)
                                &#10003;
                            @elseif($index == $estadoActualIndex)
                                &#9679;
                            @else
                                &#9675;
                            @endif
                        </div>
                        <div class="status-content">
                            <h4>{{ $estadoLabels[$estado] }}</h4>
                            @if($index == $estadoActualIndex)
                                <p><strong>Estado actual</strong></p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Información del operativo (si está en atención) --}}
            @if($nuevoEstado === 'en_atencion' && $rescate)
            <div class="message-box warning">
                <h4>Detalles del Operativo</h4>
                <p>
                    <strong>Fecha programada:</strong> {{ $rescate->fecha_programada ? $rescate->fecha_programada->format('d/m/Y') : 'Por confirmar' }}<br>
                    @if($rescate->equipo_rescate)
                    <strong>Equipo:</strong> {{ $rescate->equipo_rescate['nombre'] ?? 'Equipo de Bienestar Animal' }}
                    @endif
                </p>
            </div>
            @endif

            {{-- Observaciones de resolución --}}
            @if(in_array($nuevoEstado, ['resuelta', 'cerrada']) && $denuncia->observaciones_resolucion)
            <div class="observaciones-box">
                <h4>Observaciones de Resolución</h4>
                <p>{{ $denuncia->observaciones_resolucion }}</p>
            </div>
            @endif

            {{-- Información del caso --}}
            <div class="info-section">
                <h3>Resumen de su Denuncia</h3>
                <div class="info-row">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value">
                        @php
                            $tipoTextos = [
                                'maltrato' => 'Maltrato Animal',
                                'abandono' => 'Abandono',
                                'animal_herido' => 'Animal Herido',
                                'animal_peligroso' => 'Animal Peligroso',
                                'otro' => 'Otro',
                            ];
                        @endphp
                        {{ $tipoTextos[$denuncia->tipo_denuncia] ?? $denuncia->tipo_denuncia }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha registro:</span>
                    <span class="info-value">{{ $denuncia->fecha_denuncia ? $denuncia->fecha_denuncia->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ubicación:</span>
                    <span class="info-value">{{ $denuncia->ubicacion ?? 'No especificada' }}</span>
                </div>
                @if($denuncia->fecha_resolucion)
                <div class="info-row">
                    <span class="info-label">Fecha resolución:</span>
                    <span class="info-value">{{ $denuncia->fecha_resolucion->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>

            {{-- Próximos pasos según el estado --}}
            @if(!in_array($nuevoEstado, ['resuelta', 'cerrada', 'desestimada']))
            <div class="next-steps">
                <h4>¿Qué sigue?</h4>
                @switch($nuevoEstado)
                    @case('recibida')
                        <p>
                            Su denuncia será revisada por nuestro equipo para evaluar la prioridad
                            y asignar los recursos necesarios. Le mantendremos informado sobre el avance.
                        </p>
                        @break
                    @case('en_revision')
                        <p>
                            Nuestro equipo está analizando la información. Una vez completada la revisión,
                            se asignará un operador de rescate para atender su caso.
                        </p>
                        @break
                    @case('asignada')
                        <p>
                            El operador asignado coordinará las acciones necesarias.
                            Le notificaremos cuando se programe o inicie el operativo de rescate.
                        </p>
                        @break
                    @case('en_atencion')
                        <p>
                            Nuestro equipo está atendiendo el caso. Una vez finalizado el operativo,
                            le informaremos sobre el resultado y las acciones tomadas.
                        </p>
                        @break
                @endswitch
            </div>
            @elseif($nuevoEstado === 'resuelta' || $nuevoEstado === 'cerrada')
            <div class="message-box success">
                <h4>Gracias por su Colaboración</h4>
                <p>
                    Su participación ciudadana es fundamental para proteger a los animales de nuestra ciudad.
                    Gracias a personas como usted, podemos actuar oportunamente en favor del bienestar animal.
                </p>
            </div>
            @endif

            <div class="divider"></div>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Este es un correo automático generado por el Sistema de Bienestar Animal.<br>
                Puede consultar el estado de su denuncia en cualquier momento usando su número de ticket
                <strong>{{ $denuncia->numero_ticket }}</strong> en nuestro portal web.
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
