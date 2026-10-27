# My Recipe Book

Eine selbst gehostete Rezeptverwaltung auf Basis von PHP, MySQL/MariaDB, HTML, CSS und JavaScript. Das Projekt trennt Koch- und Backrezepte, unterstützt Suche und Filter und ermöglicht das Speichern von Rezepten inklusive Bild.

> Privates Nebenprojekt. KI-Werkzeuge wurden bei der Entwicklung unterstützend eingesetzt.

## Funktionen

- Getrennte Übersichten für Koch- und Backrezepte
- Suche nach Rezeptname, Zutaten und Beschreibung
- Filter nach Gesamtzeit, Schwierigkeit und Ernährungseigenschaften
- Sortierung nach Neuheit, Zubereitungszeit und Name
- Favoriten im Browser (`localStorage`; werden nicht zwischen Geräten synchronisiert)
- Rezepte mit Bild, Zutaten, Zubereitung, Portionen und Eigenschaften hinzufügen
- Responsives Layout für Desktop und Smartphone

## Technologie und Voraussetzungen

- PHP 8 oder neuer empfohlen
- MySQL oder MariaDB
- PHP-Erweiterungen `pdo_mysql` und `mbstring`
- Webserver, der PHP ausführt (z. B. Apache)
- Schreibbarer Ordner `images/` für hochgeladene Rezeptbilder

GitHub Pages allein reicht nicht aus, da die Anwendung PHP und eine Datenbank benötigt.

## Installation

### 1. Projekt herunterladen

```bash
git clone <DEINE-REPOSITORY-URL>
cd my-recipe-book
```

Alternativ das Repository als ZIP herunterladen und in das Verzeichnis des PHP-Webservers entpacken.

### 2. Datenbank und Tabellen erstellen

Die Anwendung erwartet eine MySQL-/MariaDB-Datenbank sowie zwei Tabellen mit den exakten Namen `recepies` und `backing`. **`recepies` ist absichtlich so geschrieben**, weil die PHP-Dateien diesen Tabellennamen verwenden.

Du kannst den SQL-Code unten in phpMyAdmin im Tab **SQL** ausführen oder die Datei [`database/schema.sql`](database/schema.sql) importieren. Das Skript erstellt die Datenbank `my_recipe_book` und beide Tabellen. Wenn du einen anderen Datenbanknamen verwenden willst, passe ihn anschließend auch in `mysql.php` an.

```sql
CREATE DATABASE IF NOT EXISTS my_recipe_book
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE my_recipe_book;

CREATE TABLE IF NOT EXISTS recepies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL,
  image VARCHAR(255) NOT NULL,
  duration INT UNSIGNED DEFAULT NULL,
  prep_time INT UNSIGNED DEFAULT NULL,
  ingredients TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  difficulty ENUM('Einfach', 'Mittel', 'Schwer') DEFAULT NULL,
  servings INT UNSIGNED DEFAULT NULL,
  vegetarian TINYINT(1) NOT NULL DEFAULT 0,
  vegan TINYINT(1) NOT NULL DEFAULT 0,
  high_protein TINYINT(1) NOT NULL DEFAULT 0,
  gluten_free TINYINT(1) NOT NULL DEFAULT 0,
  lactose_free TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS backing (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(255) NOT NULL,
  image VARCHAR(255) NOT NULL,
  duration INT UNSIGNED DEFAULT NULL,
  prep_time INT UNSIGNED DEFAULT NULL,
  ingredients TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  difficulty ENUM('Einfach', 'Mittel', 'Schwer') DEFAULT NULL,
  servings INT UNSIGNED DEFAULT NULL,
  vegetarian TINYINT(1) NOT NULL DEFAULT 0,
  vegan TINYINT(1) NOT NULL DEFAULT 0,
  high_protein TINYINT(1) NOT NULL DEFAULT 0,
  gluten_free TINYINT(1) NOT NULL DEFAULT 0,
  lactose_free TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Wichtig:** `CREATE TABLE IF NOT EXISTS` ergänzt keine fehlenden Spalten, falls die Tabellen bereits existieren. Bei bestehenden Tabellen zuerst ein Backup erstellen und nötige Änderungen separat migrieren.

### 3. Datenbankzugang konfigurieren

Öffne `mysql.php` und ersetze die Platzhalter durch die Daten deines Datenbank-Hosters:

```php
$host = "HOST-ADRESS";   // z. B. localhost
$name = "DATABASE-NAME"; // hier: my_recipe_book
$user = "USERNAME";      // Datenbank-Benutzername
$passwort = "PASSWORD";  // Datenbank-Passwort
```

Diese Werte müssen zu der Datenbank passen, die du erstellt hast. Echte Zugangsdaten niemals in ein öffentliches GitHub-Repository committen. Für den produktiven Betrieb sollten Geheimnisse möglichst über Umgebungsvariablen oder eine nicht versionierte Konfigurationsdatei bereitgestellt werden.

### 4. Zugriffscode prüfen

Die aktuelle Version verwendet einen Zugriffscode in der URL (`?code=...`). Der Code ist in `index.php`, `view.php` und `create_recipe.php` hinterlegt. Die Werte müssen momentan übereinstimmen.

- Suche in diesen drei Dateien nach `$validCode`.
- Ersetze den Platzhalter für lokale Tests durch denselben temporären Wert.
- Verwende das nicht als echte Anmeldung und veröffentliche die Anwendung nicht mit einem geheimen Code in der URL.

Ein Zugriffscode in der URL kann in Browser-Verlauf, Server-Logs oder geteilten Links auftauchen. Vor einem öffentlichen Einsatz sollte dieser Mechanismus durch eine richtige Anmeldung mit serverseitigen Sessions und Berechtigungsprüfung ersetzt werden.

### 5. Dateien, Bilder und Routen prüfen

- Stelle sicher, dass `index.php`, `view.php`, `create_recipe.php`, `mysql.php` und `style.css` im erwarteten Verzeichnis liegen.
- `index.php` lädt `/script.js`. Der Dateiname muss daher exakt `script.js` lauten, oder der Pfad muss angepasst werden.
- Erstelle den Ordner `images/` im Projektverzeichnis und erlaube dem PHP-Prozess, dort Bilder zu speichern.
- Die Bilddateien werden aus `/images/` geladen. Das Favicon verweist derzeit auf `/Images/logo.png`. Auf Linux-Systemen sind Groß- und Kleinschreibung von Pfaden relevant.
- Die Links verwenden `/cooking` und `/backing`. Richte dafür passende Rewrite-Regeln auf dem Webserver ein oder ändere die Routen in den PHP-Dateien.
- Die Pfade mit führendem `/` beziehen sich auf das Domain-Wurzelverzeichnis. Bei Installation in einem Unterverzeichnis müssen die Pfade gegebenenfalls angepasst werden.

### 6. Funktion testen

1. Öffne die Anwendung über deinen lokalen PHP-Webserver.
2. Prüfe die Koch- und Backübersicht.
3. Lege ein Testrezept an und lade ein JPG-, PNG- oder WebP-Bild hoch.
4. Teste Suche, Filter, Sortierung und Favoriten.
5. Prüfe, ob ein Rezept auch nach einem Neuladen noch in der Datenbank vorhanden ist.

## Datenbankschema

Beide Tabellen besitzen dieselben Spalten:

| Spalte | Typ | Bedeutung |
|---|---|---|
| `id` | `INT UNSIGNED` | Automatisch erzeugte ID und Primärschlüssel |
| `Name` | `VARCHAR(255)` | Rezeptname |
| `image` | `VARCHAR(255)` | Dateiname des Rezeptbildes im Ordner `images/` |
| `duration` | `INT UNSIGNED` | Gesamtzubereitungszeit in Minuten |
| `prep_time` | `INT UNSIGNED` | Aktive Vorbereitungszeit in Minuten |
| `ingredients` | `TEXT` | Zutaten |
| `description` | `TEXT` | Zubereitung/Beschreibung |
| `difficulty` | `ENUM` | `Einfach`, `Mittel` oder `Schwer` |
| `servings` | `INT UNSIGNED` | Anzahl der Portionen |
| `vegetarian` | `TINYINT(1)` | Vegetarisch: `0` nein, `1` ja |
| `vegan` | `TINYINT(1)` | Vegan: `0` nein, `1` ja |
| `high_protein` | `TINYINT(1)` | Proteinreich: `0` nein, `1` ja |
| `gluten_free` | `TINYINT(1)` | Glutenfrei: `0` nein, `1` ja |
| `lactose_free` | `TINYINT(1)` | Laktosefrei: `0` nein, `1` ja |

`recepies` speichert Kochrezepte, `backing` speichert Backrezepte. Die Spaltennamen müssen exakt erhalten bleiben, da die PHP-Abfragen sie direkt verwenden.

## Anpassungs-Checkliste

- [ ] `mysql.php`: Datenbank-Host, Datenbankname, Benutzername und Passwort eintragen
- [ ] `index.php`, `view.php`, `create_recipe.php`: Zugriffscode prüfen; vor öffentlichem Einsatz richtige Anmeldung implementieren
- [ ] `index.php`: JavaScript-, CSS-, Favicon- und Bildpfade prüfen
- [ ] `images/`: Ordner erstellen und Schreibrechte prüfen
- [ ] Webserver: `/cooking` und `/backing` korrekt auf `index.php` routen
- [ ] Repository-URL in den Git-Befehlen ersetzen
- [ ] `LICENSE`: Rechteinhaber und Lizenzbedingungen prüfen
- [ ] Nur Rezepttexte und Bilder verwenden, die du selbst erstellt hast oder weitergeben darfst

## Sicherheit und Betrieb

- Keine Zugangsdaten oder privaten Daten committen.
- Der aktuelle URL-Code ist kein Ersatz für eine sichere Anmeldung.
- Für Hosting im Internet HTTPS verwenden.
- PHP und Datenbankserver aktuell halten.
- Datenbankfehler nicht mit technischen Details an Besucher ausgeben.
- Upload-Ordner so konfigurieren, dass dort hochgeladene Dateien nicht als serverseitiger Code ausgeführt werden.
- Datenbank und hochgeladene Bilder regelmäßig sichern.
- Vor dem produktiven Einsatz Eingaben, Uploads und Zugriffsrechte testen.

## Lizenz

Das Projekt wird unter der individuellen nicht-kommerziellen Lizenz in [`LICENSE`](LICENSE) bereitgestellt. Die Nutzung, Änderung, Selbst-Hosting und Weitergabe sind im Rahmen dieser Bedingungen erlaubt; kommerzielle Nutzung benötigt eine vorherige schriftliche Genehmigung. Es handelt sich nicht um eine standardisierte OSI-anerkannte Open-Source-Lizenz. Rechte an Drittmaterial werden durch diese Lizenz nicht automatisch übertragen.

## Mitarbeit

Verbesserungen und Hinweise sind willkommen. Bei Änderungen an den Rezeptfeldern müssen die SQL-Tabellen und die verwendenden PHP-Dateien gemeinsam aktualisiert werden.

## Haftungsausschluss

Die Software wird ohne Gewährleistung bereitgestellt. Vor allem vor einem Einsatz auf einem öffentlich erreichbaren Server sollte der Code geprüft und getestet werden.
