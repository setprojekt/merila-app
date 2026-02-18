<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dobavnica {{ $deliveryNote->number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            padding: 15mm;
            background: white;
        }
        
        .document-header {
            margin-bottom: 10mm;
            text-align: right;
        }
        
        .company-info {
            text-align: right;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .company-details {
            font-size: 9pt;
            line-height: 1.6;
        }
        
        .document-title {
            float: right;
            width: 45%;
            text-align: right;
        }
        
        .document-title h1 {
            font-size: 22pt;
            font-weight: bold;
            margin-bottom: 3mm;
            letter-spacing: 1px;
        }
        
        .document-number {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .document-date {
            font-size: 10pt;
            color: #333;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .parties-section {
            margin-top: 5mm;
            margin-bottom: 8mm;
        }
        
        .recipient-info {
            width: 50%;
            float: left;
        }
        
        .party-label {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }
        
        .party-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .party-address {
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
        }
        
        .items-section {
            margin-top: 5mm;
            margin-bottom: 10mm;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5mm;
            padding-bottom: 2mm;
            border-bottom: 2px solid #000;
        }
        
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
        }
        
        table.items thead {
            background-color: #e0e0e0;
            border: 1px solid #000;
        }
        
        table.items th {
            padding: 1.5mm 2mm;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #000;
        }
        
        table.items tbody td {
            padding: 1.5mm 2mm;
            border: 1px solid #ccc;
            font-size: 9pt;
            vertical-align: top;
        }
        
        table.items tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .item-number {
            text-align: center;
            font-weight: bold;
        }
        
        .item-id {
            font-weight: bold;
        }
        
        .notes-section {
            margin-top: 5mm;
            padding: 4mm;
            border: 1px solid #ccc;
            background: #fffef0;
        }
        
        .notes-section .label {
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .signatures {
            position: fixed;
            bottom: 25mm;
            left: 15mm;
            right: 15mm;
            page-break-inside: avoid;
        }
        
        .signature-row {
            display: table;
            width: 100%;
            margin-bottom: 15mm;
        }
        
        .signature-box {
            display: table-cell;
            width: 48%;
            text-align: center;
        }
        
        .signature-box.left {
            padding-right: 2%;
        }
        
        .signature-box.right {
            padding-left: 2%;
        }
        
        .signature-label {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 10mm;
            text-transform: uppercase;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 2mm;
            font-size: 8pt;
        }
        
        .signature-name {
            min-height: 5mm;
        }
        
        .signature-place {
            font-size: 8pt;
            color: #666;
        }
        
        .footer {
            margin-top: 8mm;
            padding-top: 3mm;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            /* A4, robovi; glavo in nogo (URL, datum) izklopi v nastavitvah tiskanja brskalnika */
            @page {
                margin: 15mm;
                size: A4 portrait;
            }
        }
        
        .print-hint {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 200px;
            padding: 8px 12px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            font-size: 11px;
            color: #856404;
            z-index: 999;
        }
        
        .action-button {
            position: fixed;
            top: 20px;
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        
        .action-button:hover {
            background-color: #000;
        }
        
        .print-button {
            right: 20px;
        }
        
        .close-button {
            right: 140px;
            background-color: #666;
        }
        
        .close-button:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <button class="action-button close-button no-print" onclick="if (window.history.length > 1) { window.history.back(); } else { window.close(); }">✖ Zapri</button>
    <button class="action-button print-button no-print" onclick="window.print()">🖨️ Natisni</button>
    <p class="print-hint no-print">Pri tiskanju v pogovornem oknu izklopite <strong>Glavo in nogo</strong> (Več nastavitev → odkljukajte), da se ne tiskata URL in datum.</p>
    
    <div class="document-header clearfix">
        <div class="company-info">
            <div class="company-name">{{ $deliveryNote->sender_name ?? 'Podjetje' }}</div>
            <div class="company-details">{!! nl2br(e($deliveryNote->sender_address ?? '')) !!}</div>
        </div>
    </div>
    
    <div class="parties-section clearfix">
        <div class="recipient-info">
            <div class="party-label">Prejemnik</div>
            <div class="party-name">{{ $deliveryNote->recipient ?? 'Ni navedeno' }}</div>
            <div class="party-address">{!! nl2br(e($deliveryNote->recipient_address ?? '')) !!}</div>
        </div>
        
        <div class="document-title">
            <h1>DOBAVNICA</h1>
            <div class="document-number">Št.: {{ $deliveryNote->number }}</div>
            <div class="document-date">Datum: {{ $deliveryNote->delivery_date ? $deliveryNote->delivery_date->format('d.m.Y') : $deliveryNote->created_at->format('d.m.Y') }}</div>
        </div>
    </div>
    
    <div class="clearfix"></div>
    
    <div class="items-section">
        <div class="section-title">Seznam meril</div>
        
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 15%;">Interna št.</th>
                    <th style="width: 45%;">Naziv merila</th>
                    <th style="width: 15%;">Tip</th>
                    <th style="width: 25%;">Opombe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $index => $item)
                <tr>
                    <td class="item-id">{{ $item->instrument->internal_id ?? 'N/A' }}</td>
                    <td>{{ $item->instrument->name ?? 'N/A' }}</td>
                    <td>{{ $item->instrument->type ?? '-' }}</td>
                    <td style="font-size: 8pt;">{{ $item->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-box left">
                <div class="signature-label">Merila predal</div>
                <div class="signature-name">&nbsp;</div>
                <div class="signature-line">
                    <div>Podpis:</div>
                    <div class="signature-place">Kraj in datum: ________________</div>
                </div>
            </div>
            
            <div class="signature-box right">
                <div class="signature-label">Merila prevzel</div>
                <div class="signature-name">&nbsp;</div>
                <div class="signature-line">
                    <div>Podpis:</div>
                    <div class="signature-place">Kraj in datum: ________________</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>{{ $deliveryNote->sender_name ?? 'SET Trade d.o.o.' }}</p>
    </div>
</body>
</html>
