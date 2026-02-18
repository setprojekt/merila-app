# Alternativne zasnove migracije na Ubuntu VM

Trenutno imate **FileZilla + ročni SSH** za prenos in nastavitev na strežniku.  
Za **Ubuntu v virtualni napravi (VM)** lahko uporabite enostavnejše ali bolj avtomatizirane pristope.

---

## 1. Trenutni pristop (FileZilla + ročno)

**Potek:** Prenos datotek z FileZillo → SSH na VM → `composer install`, `npm run build`, migracije, nastavitve.

**Prednosti:** Enostavno, brez dodatnih orodij.  
**Slabosti:** Veliko ročnih korakov, napačke pri pozabljenih korakih, težje ponovljivo.

---

## 2. Alternativa A: Git + en sam `deploy` ukaz na VM

**Zasnova:** Na VM imate **git**. Kodno bazo prenesete s **git clone** (ali `git pull`), ne prek FileZille. Na VM poženete **en skript** `deploy.sh`, ki naredi vse (composer, npm, migrate, cache, restart PHP-FPM).

### Koraki

**Na VM (enkrat):**

- Namestite PHP, MySQL, Redis, Nginx, Composer, Node (npr. po `DEPLOYMENT.md`).
- Klonirate repozitorij v `/var/www/merila-app` (ali kjer koli).
- Ustvarite `.env`, nastavite bazo, `php artisan key:generate`, `storage:link`, pravice.
- V repozitorij dodate `deploy.sh` (glej spodaj) in ga naredite izvedljivega.

**Ob vsaki posodobitvi:**

- Lokalno: `git push` (ali pa na VM naredite `git pull` iz vašega repozitorija).
- Na VM: `cd /var/www/merila-app && ./deploy.sh`

**Prednosti:** Ni FileZille, hitro posodabljanje, en ukaz.  
**Slabosti:** Na VM morate imeti git in dostop do repozitorija (zasebni repo = SSH ključ ali token).

---

## 3. Alternativa B: Docker / Docker Compose na VM

**Zasnova:** Na Ubuntu VM teče **Docker**. Aplikacija, PHP, MySQL, Redis, Nginx so v **kontejnerjih**. Na VM samo `docker compose up -d` (ali podobno). Enaka oblika delovanja kot pri Sailu, v produkcijski različici.

### Primer sheme

- `docker-compose.production.yml` (ali podobno ime) z:
  - PHP-FPM + Nginx (ali en kontejner z nginx+php)
  - MySQL
  - Redis
- Volumni za `storage`, `bootstrap/cache`, mysql podatke.
- `.env` za produkcijo.

**Ob posodobitvi:** Prenesete novo kodo (git pull ali prenos), nato `docker compose build --no-cache` (po potrebi) in `docker compose up -d`, znotraj kontejnerjev `composer install`, `npm run build`, `migrate`.

**Prednosti:** Izolirano okolje, enako na razvoju (Sail) in na VM.  
**Slabosti:** Treba vzdrževati Docker datoteke in proces deploya.

---

## 4. Alternativa C: En sam „setup VM“ skript (Ansible ali bash)

**Zasnova:** **En skript** (npr. bash ali Ansible playbook), ki:

1. Na svežo Ubuntu VM namesti PHP, MySQL, Redis, Nginx, Composer, Node,
2. Klonira aplikacijo (ali jo skopira),
3. Nastavi `.env`, bazo, pravice, `key:generate`, `storage:link`,
4. Zažene migracije in prvi build.

Po tem imate na VM že tekočo aplikacijo. Kasneje za posodobitve uporabite `deploy.sh` (git pull + build + migrate).

**Prednosti:** Novo VM v nekaj minutah, ponovljivo.  
**Slabosti:** Začetna priprava skripta, v vsakem okolju malo drugače (poti, uporabniki).

---

## 5. Alternativa D: „Posodobitev prek SCP“ + deploy skript na VM

**Zasnova:** Namesto FileZille uporabite **SCP** (ali **rsync**):

```bash
# Z vašega računalnika (PowerShell ali WSL)
scp -r ./merila-app/* uporabnik@ip-vm:/var/www/merila-app/
```

Na VM imate **`deploy.sh`**, ki ob vsakem prenosu poteče (ga poženete ročno ali npr. s cronom po prenosu): posodobi odvisnosti, zgradi frontend, migracije, cache, restart PHP-FPM.

**Prednosti:** Ni FileZille, en ukaz za prenos, deploy ločen v en skript.  
**Slabosti:** Še vedno ročni prenos (ali ga lahko dodate v svojo skripto).

---

## 6. Primerjalna tabela

| Pristop | FileZilla | Git na VM | Docker | Začetna zahtevnost | Ponovljivost |
|--------|-----------|-----------|--------|--------------------|--------------|
| Trenutni (FileZilla + ročno) | Da | Ne | Ne | Nizka | Nizka |
| A: Git + deploy.sh | Ne | Da | Ne | Srednja | Visoka |
| B: Docker na VM | Ne | Opcijsko | Da | Višja | Zelo visoka |
| C: Setup VM skript | Ne | Da (običajno) | Ne | Višja | Zelo visoka |
| D: SCP + deploy.sh | Ne | Ne | Ne | Nizka | Srednja |

---

## 7. Priporočilo za vaš scenarij (Ubuntu VM)

- Če **že imate ali planirate git** (GitHub, GitLab, samo git na strežniku):  
  **Alternativa A** (Git + `deploy.sh`) je dober kompromis – brez FileZille, hitro posodabljanje, en ukaz na VM.

- Če želite **čim bolj enako okolje kot Sail** in vam ustreza Docker:  
  **Alternativa B** (Docker na VM).

- Če želite **ohraniti prenos datotek**, a zmanjšati ročno delo na VM:  
  **Alternativa D** (SCP/rsync + `deploy.sh`) ali pa **enkrat še FileZilla** za začetno namestitev, nato **A** ali **D** za posodobitve.

---

## 8. Konkretna dopolnitev: `deploy.sh` v repozitoriju

V korenu projekta je **`deploy.sh`**. Na VM ga poženete ob vsaki posodobitvi (po `git pull` ali po prenosu prek SCP/FileZilla).

### Uporaba na VM

```bash
cd /var/www/merila-app
chmod +x deploy.sh
./deploy.sh
```

Ob **posodobitvi prek FileZilla/SCP** (brez git):

```bash
./deploy.sh --no-git
```

Skript privzeto predvideva mapo `/var/www/merila-app`. Če je drugače, nastavite `APP_DIR`:

```bash
APP_DIR=/home/user/merila-app ./deploy.sh
```

Ob koncu reloada PHP-FPM in (če obstaja) queue workerja. Poti in imena storitev lahko v skripti prilagodite.

---

Če povzamemo: **aplikacija lahko ostane isto** – spremenite le **način prenosa** (FileZilla → git ali SCP) in **deploy** (ročni ukazi → en sam `deploy.sh` ali Docker). Za migracijo na Ubuntu VM je to že velika olajšava.
