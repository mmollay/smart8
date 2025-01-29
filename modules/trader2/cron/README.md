# BitGet Trade Sync Cron Setup

Dieses Verzeichnis enthält Skripte für die automatische Synchronisation von BitGet Trades und PnL-Daten.

## Skripte

- `sync_all_trades.php`: Synchronisiert Trades und PnL für alle aktiven Benutzer und konfigurierten Symbole
- `sync.log`: Log-Datei mit Zeitstempeln und Statusmeldungen

## Cron Installation

### 1. Berechtigungen setzen
```bash
chmod +x /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync_all_trades.php
chmod 666 /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync.log
```

### 2. Cron Job einrichten
Führen Sie `crontab -e` aus und fügen Sie eine der folgenden Zeilen hinzu:

```bash
# Alle 5 Minuten
*/5 * * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync_all_trades.php

# ODER: Jede Minute (höhere Last)
* * * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync_all_trades.php

# ODER: Alle 15 Minuten (geringere Last)
*/15 * * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync_all_trades.php
```

### 3. Log Rotation einrichten (optional)
Um zu verhindern, dass die Log-Datei zu groß wird:

```bash
# /etc/logrotate.d/bitget_sync
/Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    create 666 root root
}
```

## Monitoring

### Log überprüfen
```bash
tail -f /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync.log
```

### Cron Status
```bash
# Alle Cron Jobs anzeigen
crontab -l

# Cron Service Status
service cron status
```

## Fehlerbehebung

1. **Logs prüfen**
   ```bash
   tail -n 50 /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync.log
   ```

2. **PHP Fehler**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log
   ```

3. **Manuelle Ausführung**
   ```bash
   /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/smart8/modules/trader2/cron/sync_all_trades.php
   ```

## Konfiguration

Die Synchronisationsintervalle können je nach Bedarf angepasst werden:
- 1 Minute: Höchste Aktualität, höchste Server-Last
- 5 Minuten: Empfohlener Standardwert
- 15 Minuten: Geringere Last, verzögerte Aktualisierung

Die zu synchronisierenden Symbole können in `sync_all_trades.php` angepasst werden:
```php
$symbols = ['BTCUSDT', 'ETHUSDT']; // Weitere Symbole hier hinzufügen
```
