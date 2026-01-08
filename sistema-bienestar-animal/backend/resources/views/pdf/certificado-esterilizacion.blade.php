<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="html; charset=utf-8"/>
    <title>Certificado de Esterilización</title>
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
            background-color: #7C3AED;
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
            border-left: 3px solid #7C3AED;
            background-color: #f8f9fa;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #6B21A8;
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
            background-color: #F3E8FF;
            border-left: 4px solid #7C3AED;
            text-align: center;
        }
        .certification h3 {
            color: #6B21A8;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .certification p {
            font-size: 10px;
            color: #7C3AED;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #7C3AED;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CERTIFICADO DE ESTERILIZACIÓN</h1>
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

    @if($cirugia)
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL PROCEDIMIENTO</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="label">Fecha de Realización:</div>
                    <div class="value">{{ \Carbon\Carbon::parse($cirugia->fecha_realizacion)->format('d/m/Y') }}</div>
                </div>
                <div class="info-cell">
                    <div class="label">Tipo:</div>
                    <div class="value">{{ ucfirst($cirugia->tipo_cirugia) }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <div class="label">Veterinario:</div>
                    <div class="value">
                        {{ $cirugia->cirujano->nombres ?? 'No especificado' }}
                        {{ $cirugia->cirujano->apellidos ?? '' }}
                    </div>
                </div>
                @if(isset($cirugia->cirujano->numero_tarjeta_profesional))
                <div class="info-cell">
                    <div class="label">Tarjeta Profesional:</div>
                    <div class="value">{{ $cirugia->cirujano->numero_tarjeta_profesional }}</div>
                </div>
                @endif
            </div>
        </div>
        
        @if($cirugia->descripcion)
        <div style="margin-top: 10px;">
            <div class="label">Descripción del Procedimiento:</div>
            <p style="margin-top: 5px;">{{ $cirugia->descripcion }}</p>
        </div>
        @endif
    </div>
    @endif

    <div class="certification">
        <h3>CERTIFICACIÓN</h3>
        <p>
            Se certifica que el animal identificado con código <strong>{{ $animal->codigo_unico }}</strong>
            ha sido esterilizado satisfactoriamente según consta en nuestros registros.
        </p>
    </div>

    <div class="footer">
        Sistema de Bienestar Animal - Documento generado automáticamente<br>
        Animal ID: {{ $animal->id }} | Código: {{ $animal->codigo_unico }}
    </div>
</body>
</html>