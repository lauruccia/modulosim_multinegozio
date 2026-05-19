# Deploy cPanel con Git Version Control

Questo progetto e' una applicazione Laravel. Per questo hosting senza terminale, il repository contiene anche `vendor` e `public/build`, cosi' cPanel puo' pubblicare il codice senza eseguire Composer o NPM sul server.

Il repository non contiene `.env`, `node_modules` o il dump SQL.

## Requisiti hosting

- PHP 8.3 o superiore.
- Estensioni PHP richieste da Laravel/Filament.
- Database MySQL configurato.
- Accesso al pannello cPanel con Git Version Control.
- PHP 8.3 o superiore sul dominio cPanel.

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

## Deploy con document root bloccato su public_html

Su questo hosting il document root e' bloccato su:

```text
/home/shars/public_html
```

Per questo il repository resta clonato in:

```text
/home/shars/repositories/modulosim_multinegozio
```

Il file `.cpanel.yml` copia automaticamente i file pubblici Laravel dentro `public_html` e sostituisce `public_html/index.php` con una versione compatibile che carica l'applicazione dalla cartella `repositories/modulosim_multinegozio`.

Questa e' la struttura corretta:

```text
/home/shars/public_html/index.php
/home/shars/public_html/.htaccess
/home/shars/repositories/modulosim_multinegozio/app
/home/shars/repositories/modulosim_multinegozio/bootstrap
/home/shars/repositories/modulosim_multinegozio/vendor
/home/shars/repositories/modulosim_multinegozio/.env
```

Non copiare tutta Laravel dentro `public_html`.

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

Se cPanel non offre terminale o strumenti Composer/Artisan, importa il file SQL generato localmente da phpMyAdmin. In questo caso non eseguire le migrazioni sul server.

Il dump locale pronto per phpMyAdmin si trova in:

```text
exports/sharers_phpmyadmin.sql
```

Questo file non viene pubblicato su GitHub.

## Document root

Il document root resta `public_html`. La compatibilita' e' gestita da `.cpanel.yml` e da `cpanel-public-index.php`.

## Primo accesso

Dopo le migrazioni, l'utente esistente riceve il ruolo default `super_admin`. Dal pannello admin potrai creare:

- negozi
- utenti amministratori
- utenti negozio associati a un negozio

Link pubblico per ogni negozio:

```text
https://tuodominio.it/negozi/slug-negozio/attivazione/dati
```
