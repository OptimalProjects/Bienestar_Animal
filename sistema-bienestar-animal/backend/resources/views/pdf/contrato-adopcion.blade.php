<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Adopcion - {{ $numero_contrato }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3366CC;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #004884;
            font-size: 18pt;
            margin-bottom: 5px;
        }

        .header h2 {
            color: #3366CC;
            font-size: 14pt;
            font-weight: normal;
        }

        .contract-number {
            text-align: right;
            font-size: 10pt;
            color: #666;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #E8F0FE;
            color: #004884;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px;
            border-left: 4px solid #3366CC;
        }

        .data-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .data-row {
            display: table-row;
        }

        .data-label {
            display: table-cell;
            width: 35%;
            padding: 5px 10px;
            font-weight: bold;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        .data-value {
            display: table-cell;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }

        .two-columns {
            width: 100%;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }

        .intro-text {
            text-align: justify;
            margin-bottom: 15px;
        }

        .commitments-list {
            margin: 15px 0;
            padding-left: 20px;
        }

        .commitments-list li {
            margin-bottom: 8px;
            text-align: justify;
        }

        .clause {
            margin-bottom: 15px;
        }

        .clause-title {
            font-weight: bold;
            color: #004884;
            margin-bottom: 5px;
        }

        .clause-content {
            text-align: justify;
            padding-left: 10px;
        }

        .signatures-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signatures-grid {
            width: 100%;
            margin-top: 30px;
        }

        .signatures-grid td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 20px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }

        .signature-name {
            font-weight: bold;
        }

        .signature-role {
            font-size: 10pt;
            color: #666;
        }

        .signature-doc {
            font-size: 9pt;
            color: #888;
        }

        .signature-image {
            max-width: 200px;
            max-height: 80px;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }

        .stamp-section {
            text-align: center;
            margin-top: 20px;
        }

        .stamp-placeholder {
            display: inline-block;
            width: 100px;
            height: 100px;
            border: 2px dashed #ccc;
            border-radius: 50%;
            line-height: 100px;
            color: #999;
            font-size: 10pt;
        }

        .signed-badge {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            display: inline-block;
        }

        .animal-photo {
            width: 120px;
            height: 120px;
            border: 2px solid #3366CC;
            border-radius: 8px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institucion['nombre'] }}</h1>
        <h2>CONTRATO DE ADOPCION RESPONSABLE DE ANIMAL</h2>
    </div>

    <div class="contract-number">
        <strong>No. {{ $numero_contrato }}</strong><br>
        Fecha: {{ $fecha_contrato }}
    </div>

    @if(isset($contrato_firmado) && $contrato_firmado)
    <div style="text-align: center;">
        <span class="signed-badge">CONTRATO FIRMADO - {{ $fecha_firma ?? $fecha_contrato }}</span>
    </div>
    @endif

    <div class="section">
        <p class="intro-text">
            En {{ $institucion['municipio'] }}, a los {{ $fecha_contrato_texto }}, entre el
            <strong>{{ $institucion['nombre'] }}</strong>, en adelante "LA ENTIDAD", y el/la ciudadano(a)
            <strong>{{ $adoptante['nombre_completo'] }}</strong>, identificado(a) con {{ $adoptante['tipo_documento'] }}
            No. <strong>{{ $adoptante['numero_documento'] }}</strong>, en adelante "EL/LA ADOPTANTE",
            se celebra el presente contrato de adopcion responsable de animal, sujeto a las siguientes clausulas:
        </p>
    </div>

    <div class="section">
        <div class="section-title">DATOS DEL ADOPTANTE</div>
        <table class="data-grid">
            <tr class="data-row">
                <td class="data-label">Nombre completo</td>
                <td class="data-value">{{ $adoptante['nombre_completo'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Documento de identidad</td>
                <td class="data-value">{{ $adoptante['tipo_documento'] }} {{ $adoptante['numero_documento'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Direccion</td>
                <td class="data-value">{{ $adoptante['direccion'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Telefono</td>
                <td class="data-value">{{ $adoptante['telefono'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Correo electronico</td>
                <td class="data-value">{{ $adoptante['email'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Tipo de vivienda</td>
                <td class="data-value">{{ $adoptante['tipo_vivienda'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">DATOS DEL ANIMAL</div>
        <table class="data-grid">
            <tr class="data-row">
                <td class="data-label">Codigo de identificacion</td>
                <td class="data-value"><strong>{{ $animal['codigo'] }}</strong></td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Nombre</td>
                <td class="data-value">{{ $animal['nombre'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Especie</td>
                <td class="data-value">{{ $animal['especie'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Raza</td>
                <td class="data-value">{{ $animal['raza'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Sexo</td>
                <td class="data-value">{{ $animal['sexo'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Color</td>
                <td class="data-value">{{ $animal['color'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Edad aproximada</td>
                <td class="data-value">{{ $animal['edad'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Tamanio</td>
                <td class="data-value">{{ $animal['tamanio'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Esterilizado</td>
                <td class="data-value">{{ $animal['esterilizado'] }}</td>
            </tr>
            <tr class="data-row">
                <td class="data-label">Estado de salud</td>
                <td class="data-value">{{ $animal['estado_salud'] }}</td>
            </tr>
            @if($animal['microchip'] !== 'No aplica')
            <tr class="data-row">
                <td class="data-label">Microchip</td>
                <td class="data-value">{{ $animal['microchip'] }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="section-title">CLAUSULAS DEL CONTRATO</div>

        <div class="clause">
            <div class="clause-title">CLAUSULA PRIMERA - OBJETO</div>
            <div class="clause-content">
                El presente contrato tiene por objeto la entrega en adopcion responsable del animal
                descrito anteriormente, por parte de LA ENTIDAD a EL/LA ADOPTANTE, quien se compromete
                a brindarle todos los cuidados necesarios para garantizar su bienestar.
            </div>
        </div>

        <div class="clause">
            <div class="clause-title">CLAUSULA SEGUNDA - COMPROMISOS DEL ADOPTANTE</div>
            <div class="clause-content">
                EL/LA ADOPTANTE se compromete a:
                <ol class="commitments-list">
                    @foreach($compromisos as $compromiso)
                    <li>{{ $compromiso }}</li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="clause">
            <div class="clause-title">CLAUSULA TERCERA - SEGUIMIENTO</div>
            <div class="clause-content">
                LA ENTIDAD realizara visitas de seguimiento al animal adoptado durante los primeros
                seis (6) meses posteriores a la adopcion, en los periodos de un (1) mes, tres (3) meses
                y seis (6) meses. EL/LA ADOPTANTE se compromete a permitir dichas visitas y a brindar
                la informacion requerida sobre el estado del animal.
            </div>
        </div>

        <div class="clause">
            <div class="clause-title">CLAUSULA CUARTA - REVOCATORIA</div>
            <div class="clause-content">
                LA ENTIDAD se reserva el derecho de revocar la adopcion y recuperar al animal en caso
                de que se compruebe maltrato, abandono, incumplimiento de los compromisos adquiridos,
                o cualquier situacion que ponga en riesgo el bienestar del animal. En tal caso,
                EL/LA ADOPTANTE debera entregar al animal de manera inmediata.
            </div>
        </div>

        <div class="clause">
            <div class="clause-title">CLAUSULA QUINTA - DISPOSICIONES FINALES</div>
            <div class="clause-content">
                El incumplimiento de cualquiera de las clausulas del presente contrato dara lugar a
                las acciones legales correspondientes, de acuerdo con la normatividad vigente sobre
                proteccion animal. Las partes declaran haber leido y comprendido el contenido del
                presente documento y se comprometen a cumplirlo en su totalidad.
            </div>
        </div>
    </div>

    <div class="signatures-section">
        <div class="section-title">FIRMAS</div>
        <p style="text-align: center; margin-bottom: 20px;">
            En constancia de lo anterior, las partes firman el presente contrato.
        </p>

        <table class="signatures-grid">
            <tr>
                <td>
                    @if(isset($firma_base64))
                    <img src="{{ $firma_base64 }}" alt="Firma del adoptante" class="signature-image">
                    @elseif(isset($firma_url))
                    <img src="{{ $firma_url }}" alt="Firma del adoptante" class="signature-image">
                    @endif
                    <div class="signature-line">
                        <div class="signature-name">{{ $adoptante['nombre_completo'] }}</div>
                        <div class="signature-role">EL/LA ADOPTANTE</div>
                        <div class="signature-doc">{{ $adoptante['tipo_documento'] }} {{ $adoptante['numero_documento'] }}</div>
                    </div>
                </td>
                <td>
                    <div class="signature-line" style="margin-top: 80px;">
                        @if($evaluador)
                        <div class="signature-name">Aprobado por: {{ $evaluador['nombre'] }}</div>
                        <div class="signature-role">{{ $evaluador['cargo'] }}</div>
                        @else
                        <div class="signature-name">Aprobado por: LA ENTIDAD</div>
                        <div class="signature-role">Representante Legal</div>
                        @endif
                        <div class="signature-doc">{{ $institucion['nombre'] }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>
            <strong>{{ $institucion['nombre'] }}</strong><br>
            {{ $institucion['direccion'] }} | Tel: {{ $institucion['telefono'] }}<br>
            Documento generado el {{ now()->format('d/m/Y H:i:s') }}
        </p>
        <p style="font-size: 8pt; margin-top: 10px;">
            Este documento es valido como comprobante de adopcion responsable.
            ID de Adopcion: {{ $adopcion_id }}
        </p>
    </div>
</body>
</html>
