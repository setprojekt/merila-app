# Strategija za Tiskanje Dokumentov

## Problem
- PDF se generira, vendar format besedila ni pravilen
- Tiskanje iz brskalnika ne ohranja formata kot v brskalniku
- Potrebujemo tiskati: dobavnice, certifikate, poročila

## Rešitev

### 1. PDF Generiranje
**Paket:** `spatie/laravel-pdf` (novejši, boljša podpora za CSS) ali `barryvdh/laravel-dompdf` (starejši, vendar stabilen)

**Prednosti spatie/laravel-pdf:**
- Boljša podpora za modern CSS
- Podpora za Tailwind CSS
- Boljša podpora za fonti
- Hitrejša generacija

### 2. Print CSS Strategija

#### A. Ločeni Print CSS stili
Ustvariti ločeno datoteko `resources/css/print.css` z `@media print` pravili:

```css
/* resources/css/print.css */
@media print {
    /* Skrij nepotrebne elemente */
    .no-print {
        display: none !important;
    }
    
    /* Nastavitve strani */
    @page {
        size: A4;
        margin: 1cm;
    }
    
    /* Osnovni stil */
    body {
        font-family: 'Arial', sans-serif;
        font-size: 12pt;
        line-height: 1.4;
        color: #000;
        background: #fff;
    }
    
    /* Tabele */
    table {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    
    th, td {
        border: 1px solid #000;
        padding: 8px;
    }
    
    /* Prepreči prelom strani */
    .no-break {
        page-break-inside: avoid;
    }
    
    /* Glava in noga */
    .print-header {
        position: fixed;
        top: 0;
        width: 100%;
    }
    
    .print-footer {
        position: fixed;
        bottom: 0;
        width: 100%;
    }
}
```

#### B. Tailwind Print Utilities (če uporabljamo Tailwind)
V `tailwind.config.js` dodati:

```js
module.exports = {
    theme: {
        extend: {},
    },
    plugins: [],
    // Dodaj print variant
    variants: {
        extend: {
            display: ['print'],
        }
    }
}
```

Uporaba:
```html
<div class="hidden print:block">Vidno samo pri tiskanju</div>
<div class="block print:hidden">Skrito pri tiskanju</div>
```

### 3. Struktura za Print Komponente

```
resources/
  ├── views/
  │   ├── print/
  │   │   ├── delivery-note.blade.php
  │   │   ├── certificate.blade.php
  │   │   └── report.blade.php
  │   └── components/
  │       └── print-layout.blade.php
```

### 4. Filament Actions za Tiskanje

#### A. PDF Download Action
```php
use Filament\Actions\Action;
use Spatie\LaravelPdf\Facades\Pdf;

Action::make('print_pdf')
    ->label('Natisni PDF')
    ->icon('heroicon-o-printer')
    ->action(function (DeliveryNote $record) {
        return Pdf::view('print.delivery-note', [
            'deliveryNote' => $record
        ])
        ->download('dobavnica-' . $record->number . '.pdf');
    })
```

#### B. Print Preview Action
```php
Action::make('print_preview')
    ->label('Predogled tiskanja')
    ->icon('heroicon-o-eye')
    ->url(fn (DeliveryNote $record) => route('print.preview', $record))
    ->openUrlInNewTab()
```

### 5. Print Routes

```php
// routes/web.php
Route::get('/print/delivery-note/{deliveryNote}', function (DeliveryNote $deliveryNote) {
    return view('print.delivery-note', [
        'deliveryNote' => $deliveryNote
    ]);
})->name('print.delivery-note');
```

### 6. Print Layout Komponenta

```blade
{{-- resources/views/components/print-layout.blade.php --}}
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dokument' }}</title>
    @vite(['resources/css/app.css', 'resources/css/print.css'])
</head>
<body class="print-body">
    <div class="print-container">
        {{ $slot }}
    </div>
    
    <script>
        // Avtomatsko odpri print dialog ob nalaganju (opcijsko)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
```

### 7. Best Practices

#### A. Fonti
- Uporabi standardne fonti (Arial, Times New Roman) za PDF
- Embed custom fonti če je potrebno
- Preveri, da so fonti dostopni v PDF generatorju

#### B. Barve
- Uporabi črno-belo za tisk (razen če je barva pomembna)
- Preveri kontrast
- Uporabi `print-color-adjust: exact;` za ohranitev barv

#### C. Tabele
- Prepreči prelom strani v tabelah (`page-break-inside: avoid`)
- Uporabi `table-layout: fixed` za konsistentno širino
- Dodaj `thead` in `tfoot` za ponovitev na vsaki strani

#### D. Slike
- Uporabi absolutne poti za slike v PDF
- Optimiziraj velikost slik
- Dodaj `max-width: 100%` za responsivnost

### 8. Implementacija v Filament

#### A. Custom Page za Print Preview
```php
class PrintDeliveryNotePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    
    public function mount(DeliveryNote $deliveryNote)
    {
        $this->deliveryNote = $deliveryNote;
    }
    
    protected function getViewData(): array
    {
        return [
            'deliveryNote' => $this->deliveryNote,
        ];
    }
    
    protected static string $view = 'print.delivery-note';
}
```

#### B. Print Button v Resource
```php
// V DeliveryNoteResource
public static function getPages(): array
{
    return [
        'index' => Pages\ListDeliveryNotes::route('/'),
        'create' => Pages\CreateDeliveryNote::route('/create'),
        'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        'print' => PrintDeliveryNotePage::route('/{record}/print'),
    ];
}
```

### 9. Testiranje

#### Checklist:
- [ ] PDF se generira pravilno
- [ ] Format besedila je konsistenten
- [ ] Tabele so pravilno formatirane
- [ ] Slike se prikažejo
- [ ] Print iz brskalnika deluje
- [ ] Prelomi strani so pravilni
- [ ] Glava in noga se ponavljata (če je potrebno)
- [ ] Barve so pravilne (črno-belo za tisk)
- [ ] Fonti so pravilni

### 10. Troubleshooting

#### Problem: PDF ne ohranja CSS stila
**Rešitev:**
- Uporabi inline stile za kritične elemente
- Preveri, da so CSS datoteke vključene v PDF generator
- Uporabi `spatie/laravel-pdf` namesto `dompdf`

#### Problem: Tiskanje iz brskalnika ne ohranja formata
**Rešitev:**
- Dodaj `@media print` CSS pravila
- Preveri, da so print stili ločeni od screen stilov
- Uporabi `print-color-adjust: exact;` za barve

#### Problem: Prelomi strani niso pravilni
**Rešitev:**
- Uporabi `page-break-inside: avoid;` za elemente, ki se ne smejo prelomiti
- Uporabi `page-break-after: always;` za nove strani
- Preveri `@page` nastavitve

## Implementacija v Projektu

1. **Namesti paket:**
   ```bash
   composer require spatie/laravel-pdf
   ```

2. **Ustvari print CSS:**
   - `resources/css/print.css`

3. **Ustvari print views:**
   - `resources/views/print/delivery-note.blade.php`
   - `resources/views/print/certificate.blade.php`

4. **Dodaj print actions v Filament Resources**

5. **Testiraj na različnih brskalnikih in PDF generatorjih**
