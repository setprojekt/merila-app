<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opozorilo o Preteku Usposobljenosti</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background-color: #059669; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #e5e7eb; }
        .expired { color: #dc2626; }
        .urgent { color: #ea580c; }
        .warning { color: #ca8a04; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background-color: white; }
        th { background-color: #f3f4f6; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #e5e7eb; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Opozorilo o Preteku Usposobljenosti</h1>
        <p>MUS Matrika usposobljenosti - {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="content">
        <p>Pozdravljeni,</p>
        <p>To je avtomatsko opozorilo o zakonsko predpisanih usposobljenostih, ki kmalu potečejo ali so že potekle.</p>

        @if($expired->count() > 0)
        <div class="section">
            <div class="section-title expired">Pretečene usposobljenosti ({{ $expired->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Zaposleni</th>
                        <th>Usposobljenost</th>
                        <th>Velja do</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expired as $entry)
                    <tr>
                        <td>{{ $entry->user->full_name }}</td>
                        <td>{{ $entry->competencyItem->name }}</td>
                        <td>{{ $entry->valid_until->format('d.m.Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($urgent->count() > 0)
        <div class="section">
            <div class="section-title urgent">Nujno - poteče v manj kot 30 dneh ({{ $urgent->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Zaposleni</th>
                        <th>Usposobljenost</th>
                        <th>Velja do</th>
                        <th>Dni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($urgent as $entry)
                    <tr>
                        <td>{{ $entry->user->full_name }}</td>
                        <td>{{ $entry->competencyItem->name }}</td>
                        <td>{{ $entry->valid_until->format('d.m.Y') }}</td>
                        <td>{{ now()->diffInDays($entry->valid_until) }} dni</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($warning->count() > 0)
        <div class="section">
            <div class="section-title warning">Opozorilo - poteče v 30+ dneh ({{ $warning->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Zaposleni</th>
                        <th>Usposobljenost</th>
                        <th>Velja do</th>
                        <th>Dni</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warning as $entry)
                    <tr>
                        <td>{{ $entry->user->full_name }}</td>
                        <td>{{ $entry->competencyItem->name }}</td>
                        <td>{{ $entry->valid_until->format('d.m.Y') }}</td>
                        <td>{{ now()->diffInDays($entry->valid_until) }} dni</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            <p>To je avtomatsko sporočilo. Prosimo, ne odgovarjajte na ta email.</p>
        </div>
    </div>
</body>
</html>
