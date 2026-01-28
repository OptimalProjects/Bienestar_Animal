<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Resumen de Devolucion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #004884;
        }
        .header h1 {
            color: #004884;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header .subtitulo {
            color: #666;
            font-size: 12px;
        }
        .header .numero-reporte {
            color: #004884;
            font-size: 10px;
            margin-top: 8px;
            font-weight: bold;
        }
        .seccion {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .seccion-titulo {
            background-color: #004884;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .seccion-contenido {
            padding: 0 10px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            color: #555;
        }
        .info-value {
            display: table-cell;
            width: 65%;
        }
        .dos-columnas {
            width: 100%;
        }
        .dos-columnas td {
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        .estado-box {
            text-align: center;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .estado-bueno {
            background-color: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .estado-regular {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
        }
        .estado-malo {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .estado-critico {
            background-color: #721c24;
            border: 2px solid #4a0e13;
            color: white;
        }
        .estado-box h3 {
            font-size: 14px;
            margin: 0;
        }
        .motivo-box {
            background-color: #e8f4fd;
            border-left: 4px solid #2196f3;
            padding: 12px;
            margin: 10px 0;
        }
        .motivo-box h4 {
            color: #0d47a1;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .motivo-box p {
            color: #1565c0;
            margin: 0;
        }
        .texto-box {
            background-color: #f8f9fa;
            padding: 12px;
            border-left: 3px solid #6c757d;
            margin: 10px 0;
        }
        .texto-box p {
            margin: 0;
            white-space: pre-wrap;
        }
        .animal-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            margin: 10px 0;
            text-align: center;
        }
        .animal-card .nombre {
            font-size: 16px;
            font-weight: bold;
            color: #495057;
        }
        .animal-card .codigo {
            font-size: 10px;
            color: #666;
        }
        .revision-box {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 10px 0;
        }
        .revision-box h4 {
            color: #856404;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .revision-box p {
            color: #5d4037;
            margin: 0;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .firma-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .firma-line {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin-top: 40px;
        }
        .firma-line hr {
            border: none;
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }
        .firma-line p {
            font-size: 10px;
            color: #666;
        }
        .timeline {
            margin: 15px 0;
        }
        .timeline-item {
            padding: 10px;
            border-left: 3px solid #004884;
            margin-left: 10px;
            margin-bottom: 10px;
        }
        .timeline-item .fecha {
            font-weight: bold;
            color: #004884;
        }
        .timeline-item .descripcion {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RESUMEN DE DEVOLUCION DE ANIMAL</h1>
        <p class="subtitulo">Sistema de Bienestar Animal - Alcaldia de Santiago de Cali</p>
        <p class="numero-reporte">Reporte No. {{ $numero_reporte }}</p>
    </div>

    {{-- Estado del Animal --}}
    @php
        $estadoClass = match($devolucion->estado_animal_devolucion) {
            'bueno' => 'estado-bueno',
            'regular' => 'estado-regular',
            'malo' => 'estado-malo',
            'critico' => 'estado-critico',
            default => 'estado-regular',
        };
    @endphp
    <div class="estado-box {{ $estadoClass }}">
        <h3>ESTADO DEL ANIMAL: {{ strtoupper($estado_animal_texto) }}</h3>
    </div>

    {{-- Datos del Animal --}}
    <div class="seccion">
        <div class="seccion-titulo">DATOS DEL ANIMAL</div>
        <div class="animal-card">
            <p class="nombre">{{ $animal['nombre'] }}</p>
            <p class="codigo">{{ $animal['codigo'] }}</p>
        </div>
        <div class="seccion-contenido">
            <table class="dos-columnas">
                <tr>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Especie:</span>
                            <span class="info-value">{{ $animal['especie'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Raza:</span>
                            <span class="info-value">{{ $animal['raza'] }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Sexo:</span>
                            <span class="info-value">{{ $animal['sexo'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Color:</span>
                            <span class="info-value">{{ $animal['color'] }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Datos del Adoptante --}}
    <div class="seccion">
        <div class="seccion-titulo">DATOS DEL ADOPTANTE</div>
        <div class="seccion-contenido">
            <table class="dos-columnas">
                <tr>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value">{{ $adoptante['nombre_completo'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Documento:</span>
                            <span class="info-value">{{ $adoptante['documento'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Telefono:</span>
                            <span class="info-value">{{ $adoptante['telefono'] ?? 'No registrado' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Direccion:</span>
                            <span class="info-value">{{ $adoptante['direccion'] ?? 'No registrada' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ $adoptante['email'] ?? 'No registrado' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Datos de la Devolucion --}}
    <div class="seccion">
        <div class="seccion-titulo">DATOS DE LA DEVOLUCION</div>
        <div class="seccion-contenido">
            <table class="dos-columnas">
                <tr>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Fecha de Devolucion:</span>
                            <span class="info-value">{{ $fecha_devolucion }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Estado del Proceso:</span>
                            <span class="info-value">{{ $estado_proceso_texto }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Codigo Adopcion:</span>
                            <span class="info-value">{{ $codigo_adopcion }}</span>
                        </div>
                        @if($funcionario)
                        <div class="info-row">
                            <span class="info-label">Registrado por:</span>
                            <span class="info-value">{{ $funcionario['nombre'] }}</span>
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Motivo de la Devolucion --}}
    <div class="seccion">
        <div class="seccion-titulo">MOTIVO DE LA DEVOLUCION</div>
        <div class="seccion-contenido">
            <div class="motivo-box">
                <h4>{{ $motivo_texto }}</h4>
                @if($descripcion_motivo)
                <p>{{ $descripcion_motivo }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Observaciones del Estado --}}
    @if($observaciones_estado)
    <div class="seccion">
        <div class="seccion-titulo">OBSERVACIONES SOBRE EL ESTADO DEL ANIMAL</div>
        <div class="seccion-contenido">
            <div class="texto-box">
                <p>{{ $observaciones_estado }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Revision Veterinaria --}}
    @if($fecha_revision_programada)
    <div class="seccion">
        <div class="seccion-titulo">REVISION VETERINARIA</div>
        <div class="seccion-contenido">
            <div class="revision-box">
                <h4>Revision Programada</h4>
                <p>El animal sera evaluado el dia {{ $fecha_revision_programada }} para determinar su estado de salud y definir los proximos pasos.</p>
            </div>
            @if($revision_completada)
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">Revision completada</span>
            </div>
            @else
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">Pendiente de revision</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Historial de la Adopcion --}}
    <div class="seccion">
        <div class="seccion-titulo">HISTORIAL DE LA ADOPCION</div>
        <div class="seccion-contenido">
            <div class="timeline">
                <div class="timeline-item">
                    <span class="fecha">{{ $fecha_adopcion }}</span>
                    <span class="descripcion"> - Adopcion completada</span>
                </div>
                <div class="timeline-item">
                    <span class="fecha">{{ $fecha_devolucion }}</span>
                    <span class="descripcion"> - Devolucion registrada</span>
                </div>
            </div>
            <div class="info-row">
                <span class="info-label">Tiempo en adopcion:</span>
                <span class="info-value">{{ $tiempo_en_adopcion }}</span>
            </div>
        </div>
    </div>

    {{-- Firmas --}}
    <div class="firma-section">
        <div style="text-align: center;">
            <div class="firma-line">
                <hr>
                <p><strong>Funcionario que Registra</strong></p>
                <p>{{ $funcionario['nombre'] ?? 'No asignado' }}</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>
            Sistema de Bienestar Animal - Alcaldia de Santiago de Cali<br>
            Documento generado el {{ $fecha_generacion }} | Este documento es de caracter informativo
        </p>
    </div>
</body>
</html>
