<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificado de Salud</title>
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
            background-color: #059669;
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
            border-left: 3px solid #059669;
            background-color: #f8f9fa;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #065F46;
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
        .list-item {
            padding: 5px 0;
            font-size: 10px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #059669;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CERTIFICADO DE SALUD</h1>
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
        <div class="section-title">ESTADO DE SALUD</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="label">Estado General:</div>
                    <div class="value">{{ ucfirst($historial->estado_general ?? 'No especificado') }}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Esterilizado:</div>
                    <div class="value">{{ $animal->esterilizacion ? 'Sí' : 'No' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($ultima_consulta)
    <div class="section">
        <div class="section-title">ÚLTIMA CONSULTA</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="label">Fecha:</div>
                    <div class="value">{{ \Carbon\Carbon::parse($ultima_consulta->fecha_consulta)->format('d/m/Y') }}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Tipo:</div>
                    <div class="value">{{ ucfirst($ultima_consulta->tipo_consulta) }}</div>
                </div>
            </div>
        </div>
        @if($ultima_consulta->diagnostico)
        <div style="margin-top: 8px;">
            <div class="label">Diagnóstico:</div>
            <p style="margin-top: 3px;">{{ $ultima_consulta->diagnostico }}</p>
        </div>
        @endif
    </div>
    @endif

    @if($vacunas_recientes && count($vacunas_recientes) > 0)
    <div class="section">
        <div class="section-title">VACUNAS RECIENTES</div>
        @foreach($vacunas_recientes->take(5) as $vacuna)
        <div class="list-item">
            • {{ $vacuna->nombre_vacuna ?? $vacuna->tipoVacuna->nombre ?? 'Vacuna' }} - 
            {{ \Carbon\Carbon::parse($vacuna->fecha_aplicacion)->format('d/m/Y') }}
        </div>
        @endforeach
    </div>
    @endif

    <div class="certification">
        <h3>CERTIFICACIÓN</h3>
        <p>
            Se certifica que el animal identificado con código <strong>{{ $animal->codigo_unico }}</strong>
            se encuentra en buen estado de salud según los registros del sistema.
        </p>
    </div>

    <div class="footer">
        Sistema de Bienestar Animal - Documento generado automáticamente<br>
        Animal ID: {{ $animal->id }} | Código: {{ $animal->codigo_unico }}
    </div>
</body>
</html>