# Change Log des File Managers

## Version 2.7 (07.04.2025)

### Verbesserung der Verzeichnis-Cache-Invalidierung

- **Optimierung des Bildrotations-Prozesses in image_rotate.php**
  - Implementierung der automatischen Verzeichnis-Cache-Invalidierung
  - Aktualisierung des Verzeichnis-Zeitstempels nach Bildrotation mit `touch(dirname($filename))`
  - Behebt das Problem, dass Thumbnails erst nach dem Löschen oder Hinzufügen einer anderen Datei korrekt angezeigt wurden
  - Nutzt den gleichen Mechanismus wie beim Löschen/Hinzufügen von Dateien für konsistentes Verhalten

## Version 2.6 (04.04.2025 - 05.04.2025)

### Verbesserungen bei der Bildrotation

#### Server-seitige Änderungen:
1. **Cache-Invalidierung in image_rotate.php**
   - Implementierung einer automatischen Cache-Löschung nach der Bildrotation
   - Identifizierung und Löschen aller zugehörigen Thumbnail-Cache-Dateien
   - Aktualisierung des Dateizeitstempels mit `touch()` zur Erzwingung neuer Cache-Parameter

2. **Optimierung der HTTP-Header in file_manager.php**
   - Modifikation der `U.header`-Funktion zur speziellen Behandlung von Bildtypen
   - Implementierung von `cache-control: no-store, must-revalidate` für Bilddateien
   - Hinzufügung von `pragma: no-cache` und `expires: 0` für optimale Browser-Kompatibilität

3. **Fehlerbehebung im Document-Konstruktor**
   - Behebung des Problems mit undefinierter `$gadget`-Variable in der Document-Klasse
   - Importieren der globalen Variablen in den entsprechenden Methoden

#### Client-seitige Änderungen:
1. **Umfassende Überarbeitung der `updateImageSourceIfFilenameFound()`-Funktion**
   - Vollständige Neugenerierung der Bild-URLs ohne Cache-Parameter
   - Implementierung einer intelligenten Erfassung aller relevanten DOM-Elemente:
     - `img.files-img` (Thumbnail-Bilder in der Galerieansicht)
     - `a.files-a-img` (Links zu den Bildern)
     - `.pswp__img` (Bilder im Lightbox/Popup-Viewer)
   - Hinzufügung von Zeitstempeln zu allen Bildquellen zur Umgehung des Browser-Caches

2. **Behandlung von Bildgeometrie nach Rotation**
   - Automatische Anpassung des Seitenverhältnisses rotierter Bilder
   - Aktualisierung der CSS-Variable `--ratio` zur korrekten Darstellung

3. **Verbesserte Popup-Behandlung**
   - Verzögerte Seiten-Aktualisierung bei geöffnetem Popup
   - Optimierte Timing-Parameter für zuverlässiges Neuladen

### Zusammenfassung der Problemlösung:
Das ursprüngliche Problem bestand darin, dass nach der Rotation eines Bildes die Thumbnail-Versionen im Browser nicht aktualisiert wurden, obwohl das Originalbild korrekt rotiert wurde. Dies war auf mehrere Faktoren zurückzuführen:

1. Die Cache-Dateien wurden nicht automatisch ungültig gemacht
2. Browser-Caching verhinderte das Neuladen der Bilder
3. Die DOM-Manipulation aktualisierte die Bild-URLs nicht vollständig

Durch die implementierten Änderungen wird nun sichergestellt, dass nach einer Bildrotation:
- Alle Cache-Dateien auf dem Server gelöscht werden
- Der Browser keine gecachten Versionen verwendet
- Alle Bild-URLs im DOM mit neuen Zeitstempeln aktualisiert werden
- Das Bild korrekt dargestellt wird, auch bei verändertem Seitenverhältnis

Diese Verbesserungen sorgen für ein nahtloses Benutzererlebnis ohne manuelles Leeren des Caches oder Neuladen der Seite nach Bildrotationen.
- 2025-04-06 21:36:14: Bild 'Schmetterling_Schildkröte.jpg' um 90° rechts gedreht. Neue Dimensionen: 1536 x 1024
- 2025-04-06 21:36:23: Bild 'Schmetterling_Schildkröte.jpg' um 90° links gedreht. Neue Dimensionen: 1024 x 1536
- 2025-04-06 21:36:34: Bild 'Schmetterling_Schildkröte.jpg' um 90° rechts gedreht. Neue Dimensionen: 1536 x 1024
- 2025-04-06 21:36:43: Bild 'Schmetterling_Schildkröte.jpg' um 90° links gedreht. Neue Dimensionen: 1024 x 1536
