<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Resumen de Visita de Seguimiento</title>
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
        .resultado-box {
            text-align: center;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .resultado-satisfactoria {
            background-color: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .resultado-observaciones {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
        }
        .resultado-critica {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .resultado-box h3 {
            font-size: 14px;
            margin: 0;
        }
        .evaluacion-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .evaluacion-table th,
        .evaluacion-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .evaluacion-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .eval-excelente, .eval-bueno, .eval-alta, .eval-adecuada, .eval-amplio {
            color: #155724;
            font-weight: bold;
        }
        .eval-regular, .eval-media, .eval-adecuado {
            color: #856404;
            font-weight: bold;
        }
        .eval-malo, .eval-deficiente, .eval-baja, .eval-reducido, .eval-inadecuado {
            color: #721c24;
            font-weight: bold;
        }
        .texto-box {
            background-color: #f8f9fa;
            padding: 12px;
            border-left: 3px solid #004884;
            margin: 10px 0;
        }
        .texto-box p {
            margin: 0;
            white-space: pre-wrap;
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
        .animal-card {
            background-color: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 12px;
            margin: 10px 0;
            text-align: center;
        }
        .animal-card .nombre {
            font-size: 16px;
            font-weight: bold;
            color: #004884;
        }
        .animal-card .codigo {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RESUMEN DE VISITA DE SEGUIMIENTO</h1>
        <p class="subtitulo">Sistema de Bienestar Animal - Alcaldia de Santiago de Cali</p>
        <p class="numero-reporte">Reporte No. {{ $numero_reporte }}</p>
    </div>

    {{-- Resultado de la Visita --}}
    @php
        $resultadoClass = match($visita->resultado) {
            'satisfactoria' => 'resultado-satisfactoria',
            'observaciones' => 'resultado-observaciones',
            'critica' => 'resultado-critica',
            default => 'resultado-satisfactoria',
        };
    @endphp
    <div class="resultado-box {{ $resultadoClass }}">
        <h3>RESULTADO: {{ strtoupper($resultado_texto) }}</h3>
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

    {{-- Datos de la Visita --}}
    <div class="seccion">
        <div class="seccion-titulo">DATOS DE LA VISITA</div>
        <div class="seccion-contenido">
            <table class="dos-columnas">
                <tr>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Tipo de Visita:</span>
                            <span class="info-value">{{ $tipo_visita_texto }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fecha Programada:</span>
                            <span class="info-value">{{ $fecha_programada }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="info-row">
                            <span class="info-label">Fecha Realizada:</span>
                            <span class="info-value">{{ $fecha_realizada }}</span>
                        </div>
                        @if($funcionario)
                        <div class="info-row">
                            <span class="info-label">Funcionario:</span>
                            <span class="info-value">{{ $funcionario['nombre'] }}</span>
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Condiciones del Hogar --}}
    @if(count($condiciones_hogar) > 0)
    <div class="seccion">
        <div class="seccion-titulo">EVALUACION DE CONDICIONES DEL HOGAR</div>
        <div class="seccion-contenido">
            <table class="evaluacion-table">
                <thead>
                    <tr>
                        <th>Aspecto</th>
                        <th>Evaluacion</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($condiciones_hogar['limpieza']))
                    <tr>
                        <td>Limpieza</td>
                        <td class="eval-{{ $condiciones_hogar['limpieza'] }}">{{ ucfirst($condiciones_hogar['limpieza']) }}</td>
                    </tr>
                    @endif
                    @if(isset($condiciones_hogar['espacio']))
                    <tr>
                        <td>Espacio</td>
                        <td class="eval-{{ $condiciones_hogar['espacio'] }}">{{ ucfirst($condiciones_hogar['espacio']) }}</td>
                    </tr>
                    @endif
                    @if(isset($condiciones_hogar['seguridad']))
                    <tr>
                        <td>Seguridad</td>
                        <td class="eval-{{ $condiciones_hogar['seguridad'] }}">{{ ucfirst($condiciones_hogar['seguridad']) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Estado del Animal --}}
    @if(count($estado_animal) > 0)
    <div class="seccion">
        <div class="seccion-titulo">EVALUACION DEL ESTADO DEL ANIMAL</div>
        <div class="seccion-contenido">
            <table class="evaluacion-table">
                <thead>
                    <tr>
                        <th>Aspecto</th>
                        <th>Evaluacion</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($estado_animal['salud']))
                    <tr>
                        <td>Salud</td>
                        <td class="eval-{{ $estado_animal['salud'] }}">{{ ucfirst($estado_animal['salud']) }}</td>
                    </tr>
                    @endif
                    @if(isset($estado_animal['comportamiento']))
                    <tr>
                        <td>Comportamiento</td>
                        <td class="eval-{{ $estado_animal['comportamiento'] }}">{{ ucfirst($estado_animal['comportamiento']) }}</td>
                    </tr>
                    @endif
                    @if(isset($estado_animal['alimentacion']))
                    <tr>
                        <td>Alimentacion</td>
                        <td class="eval-{{ $estado_animal['alimentacion'] }}">{{ ucfirst($estado_animal['alimentacion']) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Observaciones --}}
    @if($observaciones)
    <div class="seccion">
        <div class="seccion-titulo">OBSERVACIONES</div>
        <div class="seccion-contenido">
            <div class="texto-box">
                <p>{{ $observaciones }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Recomendaciones --}}
    @if($recomendaciones)
    <div class="seccion">
        <div class="seccion-titulo">RECOMENDACIONES</div>
        <div class="seccion-contenido">
            <div class="texto-box">
                <p>{{ $recomendaciones }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Firmas --}}
    <div class="firma-section">
        <div style="text-align: center;">
            <div class="firma-line">
                <hr>
                <p><strong>Funcionario Visitador</strong></p>
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
