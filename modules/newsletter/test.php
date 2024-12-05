<?php
// test_urls.php

// Simuliere Umgebungsvariable
$_ENV['APP_URL'] = 'https://example.com';

// Die zu testende Funktion
function makeUrlsAbsolute($content, $baseUrl)
{
    $baseUrl = rtrim($baseUrl, '/');

    // Array von Mustern und ihren Attributen
    $patterns = [
        // Bilder - verbessertes Pattern
        ['pattern' => '/(src=)"(\/users\/[^"]+)"/i', 'attr' => 'src'],
        // Links - verbessertes Pattern
        ['pattern' => '/(href=)"(\/users\/[^"]+)"/i', 'attr' => 'href'],
    ];

    foreach ($patterns as $p) {
        $content = preg_replace_callback(
            $p['pattern'],
            function ($matches) use ($baseUrl) {
                // $matches[1] enthält jetzt "src=" oder "href="
                // $matches[2] enthält den Pfad
                return $matches[1] . '"' . $baseUrl . $matches[2] . '"';
            },
            $content
        );
    }

    return $content;
}

// Testfälle
$testCases = [
    [
        'name' => 'Test 1: Einfaches Bild',
        'input' => '<img src="/users/123/test.jpg">',
        'expected' => '<img src="https://example.com/users/123/test.jpg">'
    ],
    [
        'name' => 'Test 2: Bild mit zusätzlichen Attributen',
        'input' => '<img class="test" src="/users/123/image.png" alt="Test">',
        'expected' => '<img class="test" src="https://example.com/users/123/image.png" alt="Test">'
    ],
    [
        'name' => 'Test 3: Link',
        'input' => '<a href="/users/123/document.pdf">Download</a>',
        'expected' => '<a href="https://example.com/users/123/document.pdf">Download</a>'
    ],
    [
        'name' => 'Test 4: Komplexer HTML-Code',
        'input' => '<p>Test</p><img src="/users/123/test.jpg"><a href="/users/123/doc.pdf">Link</a>',
        'expected' => '<p>Test</p><img src="https://example.com/users/123/test.jpg"><a href="https://example.com/users/123/doc.pdf">Link</a>'
    ],
    [
        'name' => 'Test 5: Newsletter-ähnlicher Content',
        'input' => '<p>{{anrede}} {{vorname}},<br><img class="image_resized" style="width:25%;" src="/users/1317/newsletters/209/test.png"></p>',
        'expected' => '<p>{{anrede}} {{vorname}},<br><img class="image_resized" style="width:25%;" src="https://example.com/users/1317/newsletters/209/test.png"></p>'
    ]
];

// Tests durchführen
echo "<h1>URL Replacement Tests</h1>";
echo "<style>
    .test-case { margin: 20px; padding: 15px; border: 1px solid #ddd; }
    .success { color: green; }
    .failure { color: red; }
    pre { background: #f5f5f5; padding: 10px; }
</style>";

foreach ($testCases as $test) {
    echo "<div class='test-case'>";
    echo "<h3>{$test['name']}</h3>";

    $result = makeUrlsAbsolute($test['input'], $_ENV['APP_URL']);
    $success = $result === $test['expected'];

    echo "<h4>Input:</h4>";
    echo "<pre>" . htmlspecialchars($test['input']) . "</pre>";

    echo "<h4>Expected Output:</h4>";
    echo "<pre>" . htmlspecialchars($test['expected']) . "</pre>";

    echo "<h4>Actual Output:</h4>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";

    echo "<p class='" . ($success ? 'success' : 'failure') . "'>";
    echo $success ? "✓ Test erfolgreich" : "✗ Test fehlgeschlagen";
    echo "</p>";

    if (!$success) {
        echo "<p class='failure'>Unterschiede gefunden!</p>";
    }
    echo "</div>";
}

// Live-Test-Formular
echo "<div class='test-case'>";
echo "<h3>Live-Test</h3>";
echo "<form method='post'>";
echo "<textarea name='test_content' style='width: 100%; height: 150px;'>" .
    htmlspecialchars($_POST['test_content'] ?? '') .
    "</textarea><br><br>";
echo "<input type='submit' value='URLs ersetzen'>";
echo "</form>";

if (isset($_POST['test_content'])) {
    echo "<h4>Ergebnis:</h4>";
    echo "<pre>" . htmlspecialchars(makeUrlsAbsolute($_POST['test_content'], $_ENV['APP_URL'])) . "</pre>";
}
echo "</div>";