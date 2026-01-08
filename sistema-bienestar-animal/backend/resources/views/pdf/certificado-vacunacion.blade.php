<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificado de Vacunación</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            background-color: #3366CC;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 10px;
        }
        .section {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 3px solid #3366CC;
            background-color: #f8f9fa;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #004884;
            margin-bottom: 8px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 3px 5px;
            width: 50%;
        }
        .label {
            font-size: 9px;
            color: #666;
        }
        .value {
            font-weight: bold;
            color: #000;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #E8F0FE;
            color: #004884;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .table td {
            border-bottom: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 9px;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #3366CC;
            padding-top: 10px;
        }
        .certification {
            margin-top: 20px;
            padding: 15px;
            background-color: #D1FAE5;
            border-left: 4px solid #059669;
            text-align: center;
        }
        .certification h3 {
            color: #065F46;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .certification p {
            font-size: 10px;
            color: #047857;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CERTIFICADO DE VACUNACIÓN</h1>
        <div class="subtitle">Sistema de Bienestar Animal</div>
        <div class="subtitle">Fecha de emisión: {{ $fecha_emision }}</div>
    </div>

    <div class="section">
        <div class="section-title">INFORMACIÓN DEL ANIMAL</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="label">Nombre:</div>
                    <div class="value">{{ $animal->nombre ?? 'Sin nombre' }}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Código Único:</div>
                    <div class="value">{{ $animal->codigo_unico }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <div class="label">Especie:</div>
                    <div class="value">{{ ucfirst($animal->especie) }}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Sexo:</div>
                    <div class="value">{{ ucfirst($animal->sexo) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">REGISTRO DE VACUNAS</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Vacuna</th>
                    <th>Dosis</th>
                    <th>Lote</th>
                    <th>Veterinario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacunas as $vacuna)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($vacuna->fecha_aplicacion)->format('d/m/Y') }}</td>
                    <td>{{ $vacuna->nombre_vacuna ?? $vacuna->tipoVacuna->nombre ?? 'N/A' }}</td>
                    <td>{{ $vacuna->numero_dosis ?? 'N/A' }}</td>
                    <td>{{ $vacuna->lote ?? 'N/A' }}</td>
                    <td>{{ $vacuna->veterinario->nombres ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay vacunas registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="certification">
        <h3>CERTIFICACIÓN</h3>
        <p>
            Se certifica que el animal identificado con código <strong>{{ $animal->codigo_unico }}</strong>
            tiene el siguiente registro de vacunación según consta en nuestros archivos.
        </p>
    </div>

    <div class="footer">
        Sistema de Bienestar Animal - Documento generado automáticamente<br>
        Animal ID: {{ $animal->id }} | Código: {{ $animal->codigo_unico }}
    </div>
</body>
</html>