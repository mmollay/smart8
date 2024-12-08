<?
function makeUrlsAbsolute($content, $baseUrl)
{
    $baseUrl = rtrim($baseUrl, '/');

    $patterns = [
        ['pattern' => '/(src\s*=\s*)"(\/users\/[^"]+)"/i', 'attr' => 'src'],
        ['pattern' => '/(href\s*=\s*)"(\/users\/[^"]+)"/i', 'attr' => 'href'],
    ];

    foreach ($patterns as $p) {
        $content = preg_replace_callback(
            $p['pattern'],
            function ($matches) use ($baseUrl) {
                $oldUrl = $matches[2];
                $newUrl = $baseUrl . $oldUrl;
                return $matches[1] . '"' . $newUrl . '"';
            },
            $content
        );
    }

    return $content;
}


function prepareHtmlForEmail($content)
{
    // Bereinige Style-Attribute
    $content = str_replace('=3D', '=', $content);

    // Array mit Ausrichtungen und ihren Styles
    $alignments = [
        'center' => 'display: block; margin: 0 auto; text-align: center',
        'left' => 'float: left; margin-right: 20px',
        'right' => 'float: right; margin-left: 20px',
        'side' => 'float: right; margin-left: 20px'
    ];

    foreach ($alignments as $align => $styles) {
        // Für figure-Tags
        $pattern = '/<figure(.*?)class="(.*?)image-style-' .
            ($align === 'side' ? 'side' : 'align-' . $align) .
            '(.*?)"(.*?)style="width:(\d+)px(.*?)"><img(.*?)>/i';

        $replacement = '<div$1class="$2image-style-align-' .
            ($align === 'side' ? 'right' : $align) .
            '$3"$4style="' . $styles . '; width: $5px;"><img$7 width="$5">';

        $content = preg_replace($pattern, $replacement, $content);

        // Für img-Tags
        $imgPattern = '/<img([^>]*?)class="([^"]*?)image_resized([^"]*?)image-style-align-' .
            $align . '([^"]*?)"([^>]*?)style="width:(\d+)px(.*?)"/i';

        $imgReplacement = '<div class="$2image_resized$3image-style-align-' . $align .
            '$4" style="' . $styles . '; width: $6px;"><img$1class="$2$3$4"$5 width="$6">';

        $content = preg_replace($imgPattern, $imgReplacement, $content);
    }

    // Ersetze übrige figure-Tags
    $content = str_replace('figure', 'div', $content);

    // Bereinige das HTML
    $content = preg_replace('/\s+/', ' ', $content);
    $content = preg_replace('/;\s*;/', ';', $content);
    $content = preg_replace('/";\s*"/', '"', $content);
    $content = preg_replace('/;\s*"/', '"', $content);

    return $content;
}
