# MötesPlatsen
Ett community-forum byggt i PHP och MySQL där användare kan skapa konton,
gå med i grupper kring sina intressen och diskutera i trådar.

## Funktioner
- Registrering och inloggning med säker lösenordshantering (password_hash)
- Skapa grupper och ansöka om medlemskap
- Admins godkänner ansökningar och kan sätta roller (medlem/admin)
- Diskussioner: starta ämnen och svara med inlägg
- Engångs-inbjudningslänkar som är giltiga i 24 timmar

## Kom igång
Kräver Docker Desktop.
    docker compose up -d --build

Öppna sedan:
- Forumet: http://localhost:8080
- phpMyAdmin: http://localhost:8081

## Databas
Vid första start behöver tabellerna skapas. Kör innehållet i
`src/schema.sql` i phpMyAdmin (fliken SQL) mot databasen `forum`.

## Teknik
- PHP 8.2 (Apache)
- MySQL 8
- PDO med prepared statements
- Säkerhet: lösenordshashning, CSRF-skydd, server-side behörighetskontroll