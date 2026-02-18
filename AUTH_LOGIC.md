# SPECIFIKACIJA: Intranet Login (Ločen PIN in Email)

## 1. Baza podatkov (Database Repair)
Tabela `users` mora podpirati uporabnike, ki nimajo e-maila ali gesla.
- Stolpec `email`: Mora biti spremenjen v **NULLABLE** (dovoli prazno vrednost).
- Stolpec `password`: Mora biti spremenjen v **NULLABLE** (uporabnik s PIN-om ne rabi gesla).
- Stolpec `pin_code` (string, nullable): Shrani hashiran PIN.
- Stolpec `name` (string): Ostane obvezen.
- Validacija: Pri kreiranju ali urejanju mora biti izpolnjen ali (Email+Geslo) ali (PIN).

## 2. Login Stran (UI & Logic)
Povozi/Refaktoriraj stran `app/Filament/Pages/Auth/Login.php`.
Na vrhu obrazca uporabi Filament `Radio` komponento za preklop načina:
- **Opcija A: "E-pošta" (Privzeto)**
  - Pokaže polja: `email`, `password`, `remember`.
- **Opcija B: "PIN koda"**
  - Pokaže SAMO polje: `login_pin` (Label: "PIN koda", Type: Password/Masked).
  - Skrije polja: `email`, `password`.
  - Uporabi `live()` ali `reactive()`, da se polja skrivajo takoj ob kliku.

## 3. Logika Avtentikacije (Authenticate Method)
V metodi `authenticate($data)`:
1. Preveri vrednost radio gumba.
2. **Če je izbran PIN način:**
   - Pridobi vse userje s PIN-om: `User::whereNotNull('pin_code')->get()`.
   - Iteriraj (loop) in preveri `Hash::check($input_pin, $user->pin_code)`.
   - Če najdeš ujemanje -> `Auth::login($user)` in return.
   - Če ne najdeš -> Vrzi napako.
3. **Če je izbran Email način:**
   - Uporabi standardni `Auth::attempt`.

## 4. Vsiljena menjava (Middleware)
- Middleware `CheckForForcedRenews` preveri:
  - Če je `force_renew_pin` == true -> preusmeri na stran za menjavo PIN-a.
  - Če je `force_renew_password` == true -> preusmeri na stran za menjavo gesla.

### 5. Urejanje Profila (Edit Profile)
Refaktoriraj/Ustvari `app/Filament/Pages/Auth/EditProfile.php`.
- **Cilj:** Omogoči uporabniku menjavo PIN-a in gesla.
- **Obrazec (Schema):**
  1. Sekcija "Osebni podatki":
     - `name` (Required).
     - `email` (Nullable - NE SME biti obvezen!).
  2. Sekcija "Varnost":
     - `password` (Confirmed, Nullable) -> Label: "Novo geslo".
     - `pin_code` (Confirmed, Nullable, Numeric, Digits: 4) -> Label: "Nova PIN koda".
- **Logika shranjevanja:**
  - Če uporabnik vpiše geslo, ga hashiraj.
  - Če uporabnik vpiše PIN, ga hashiraj in shrani v `pin_code`.
  - Če so polja prazna, jih ignoriraj (ne povozi starih).