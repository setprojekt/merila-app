# Excel datoteka za uvoz meril

## Navodila za Excel format

Če želite uporabiti Excel datoteko namesto CSV, uporabite naslednjo strukturo:

### Struktura stolpcev (vrstica 1 - glava):

| A | B | C | D | E | F | G | H |
|---|---|---|---|---|---|---|---|
| Notranja številka | Ime merila | Vrsta merila | Lokacija | Frekvenca pregleda (leta) | Datum zadnjega pregleda | Status | Odgovoren uporabnik |

### Primer podatkov (vrstice 2+):

| Notranja številka | Ime merila | Vrsta merila | Lokacija | Frekvenca pregleda (leta) | Datum zadnjega pregleda | Status | Odgovoren uporabnik |
|---|---|---|---|---|---|---|---|
| MER-001 | Merilna palica 1000mm | Merilna palica | Delavnica A | 2 | 2024-01-15 | USTREZA | admin@example.com |
| MER-002 | Merilni valjček 50mm | Merilni valjček | Delavnica B | 1 | 2024-03-20 | USTREZA | admin@example.com |
| MER-003 | Merilna tehtnica 500kg | Tehtnica | Magacin | 2 | 2023-12-10 | USTREZA | admin@example.com |
| MER-004 | Merilni kotnik 90° | Kotnik | Delavnica A | 1 | 2024-02-05 | USTREZA | admin@example.com |
| MER-005 | Merilna ravnilo 2000mm | Ravnilo | Delavnica B | 2 | 2023-11-30 | NE_USTREZA | admin@example.com |

### Navodila:

1. Odprite Excel (ali LibreOffice Calc, Google Sheets)
2. Ustvarite novo datoteko
3. V prvo vrstico vnesite glavo stolpcev (kot je prikazano zgoraj)
4. V naslednje vrstice vnesite podatke meril
5. Shranite kot CSV (Comma Separated Values) - to je pomembno!
6. Uporabite shranjeno CSV datoteko za uvoz v aplikacijo

### Pomembno:

- Excel datoteke (.xlsx) trenutno niso neposredno podprte
- Shranite Excel datoteko kot CSV format za uvoz
- Prva vrstica mora vsebovati glavo stolpcev
- Ne dodajajte dodatnih vrstic ali stolpcev pred glavo
