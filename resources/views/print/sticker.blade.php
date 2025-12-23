<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 100mm 100mm; /* Tamaño Etiqueta */
            margin: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 5mm;
            text-align: center;
            border: 2px solid #000;
            height: 90mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        h2 { margin: 5px 0; font-size: 32px; font-weight: bold; }
        .info { margin: 10px 0; font-size: 14px; text-align: left; padding-left: 10mm; }
        .info p { margin: 5px 0; }
        .footer { font-size: 10px; margin-top: 10px; }
        .qr { margin-top: 10px; }
    </style>
</head>
<body onload="window.print()">
    <h1>REPROCESO</h1>
    <hr>
    <div class="info">
        <p><strong>PRODUCTO:</strong> {{ $record->product->name }}</p>
        <p><strong>LÍNEA:</strong> {{ $record->line->name }}</p>
        <p><strong>FECHA:</strong> {{ $record->date }}</p>
        <p><strong>TURNO:</strong> {{ $record->shift->name }}</p>
        <p><strong>DETALLE:</strong> {{ Str::limit($record->cause_comment, 30) }}</p>
    </div>
    <hr>
    <h2>{{ $record->weight_kg }} Kg</h2>
    
    <div class="footer">
        Responsable: {{ $record->operator->name ?? 'N/A' }} <br>
        Reg: #{{ $record->id }}
    </div>
</body>
</html>