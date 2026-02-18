<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opozorilo o Poteku Meril</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .expired {
            color: #dc2626;
        }
        .urgent {
            color: #ea580c;
        }
        .warning {
            color: #ca8a04;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: white;
        }
        th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-expired {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-urgent {
            background-color: #ffedd5;
            color: #9a3412;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 Opozorilo o Poteku Meril</h1>
        <p>{{ now()->format('d.m.Y H:i') }}</p>
    </div>
    
    <div class="content">
        <p>Pozdravljeni,</p>
        <p>To je avtomatsko opozorilo o merilih, ki kmalu potečejo ali so že potekla.</p>
        
        @if($expired->count() > 0)
        <div class="section">
            <div class="section-title expired">🔴 Pretečena Merila ({{ $expired->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Številka</th>
                        <th>Naziv</th>
                        <th>Lokacija</th>
                        <th>Potek</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expired as $instrument)
                    <tr>
                        <td><strong>{{ $instrument->internal_id }}</strong></td>
                        <td>{{ $instrument->name }}</td>
                        <td>{{ $instrument->location }}</td>
                        <td>{{ $instrument->next_check_date->format('d.m.Y') }}</td>
                        <td><span class="badge badge-expired">PRETEČENO</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        @if($urgent->count() > 0)
        <div class="section">
            <div class="section-title urgent">🟠 Nujno - Poteče v manj kot 5 dneh ({{ $urgent->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Številka</th>
                        <th>Naziv</th>
                        <th>Lokacija</th>
                        <th>Potek</th>
                        <th>Dni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($urgent as $instrument)
                    <tr>
                        <td><strong>{{ $instrument->internal_id }}</strong></td>
                        <td>{{ $instrument->name }}</td>
                        <td>{{ $instrument->location }}</td>
                        <td>{{ $instrument->next_check_date->format('d.m.Y') }}</td>
                        <td><span class="badge badge-urgent">{{ now()->diffInDays($instrument->next_check_date) }} dni</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        @if($warning->count() > 0)
        <div class="section">
            <div class="section-title warning">🟡 Opozorilo - Poteče v 5-30 dneh ({{ $warning->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Številka</th>
                        <th>Naziv</th>
                        <th>Lokacija</th>
                        <th>Potek</th>
                        <th>Dni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warning as $instrument)
                    <tr>
                        <td><strong>{{ $instrument->internal_id }}</strong></td>
                        <td>{{ $instrument->name }}</td>
                        <td>{{ $instrument->location }}</td>
                        <td>{{ $instrument->next_check_date->format('d.m.Y') }}</td>
                        <td><span class="badge badge-warning">{{ now()->diffInDays($instrument->next_check_date) }} dni</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        <div class="footer">
            <p><strong>SET Trade d.o.o.</strong></p>
            <p>To je avtomatsko sporočilo. Prosimo, ne odgovarjajte na ta email.</p>
            <p>Za vprašanja se obrnite na: info@set-trade.si</p>
        </div>
    </div>
</body>
</html>
