# Deploy cPanel con Git Version Control

Questo progetto e' una applicazione Laravel. Il repository contiene il codice sorgente, non contiene `.env`, `vendor`, `node_modules` o build asset generati.

## Requisiti hosting

- PHP 8.3 o superiore.
- Estensioni PHP richieste da Laravel/Filament.
- Database MySQL configurato.
- Accesso al pannello cPanel con Git Version Control.
- Possibilita' di eseguire Composer dal pannello cPanel, oppure dipendenze gia' installate sul server.

## Collegamento repository

Repository GitHub:

```text
https://github.com/lauruccia/modulosim_multinegozio.git
```

In cPanel:

1. Apri Git Version Control.
2. Crea un nuovo repository/clona da URL.
3. Usa l'URL GitHub sopra.
4. Imposta il branch `main`.
5. Esegui il deploy/pull dal pannello quando pubblichi nuove modifiche.

## Configurazione Laravel sul server

Nel file `.env` del server imposta almeno:

```env
APP_NAME=Sharers
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tuodominio.it

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_database
DB_USERNAME=utente_database
DB_PASSWORD=password_database

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="${APP_NAME}"

FORM_ADMIN_EMAIL=tua-email-amministrazione@dominio.it
```

## Dopo il primo deploy

Da cPanel, se disponibile, esegui:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
```

Se cPanel non offre terminale o strumenti Composer/Artisan, serve abilitare una funzione equivalente nel pannello hosting o fare eseguire questi comandi dall'assistenza hosting. Senza migrazioni il database non avra' le tabelle multinegozio.

## Document root

Il document root del dominio deve puntare alla cartella `public` del progetto Laravel, non alla root del repository.

Esempio:

```text
/home/utente/repositories/modulosim_multinegozio/public
```

## Primo accesso

Dopo le migrazioni, l'utente esistente riceve il ruolo default `super_admin`. Dal pannello admin potrai creare:

- negozi
- utenti amministratori
- utenti negozio associati a un negozio

Link pubblico per ogni negozio:

```text
https://tuodominio.it/negozi/slug-negozio/attivazione/dati
```
