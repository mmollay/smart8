<?php
$versions = require(__DIR__ . '/version.php');

function formatChangelog($changelog)
{
    $html = '';
    foreach ($changelog as $version => $data) {
        $html .= "<div class='version-block'>";
        $html .= "<h3 class='ui dividing header'>Version {$version} <small>({$data['date']})</small></h3>";

        foreach ($data['changes'] as $category => $changes) {
            $iconClass = '';
            $colorClass = '';

            switch ($category) {
                case 'Neu':
                    $iconClass = 'plus circle';
                    $colorClass = 'green';
                    break;
                case 'Verbessert':
                    $iconClass = 'arrow circle up';
                    $colorClass = 'blue';
                    break;
                case 'Behoben':
                    $iconClass = 'check circle';
                    $colorClass = 'orange';
                    break;
            }

            $html .= "<h4 class='ui {$colorClass} header'><i class='{$iconClass} icon'></i>{$category}</h4>";
            $html .= "<div class='ui list'>";
            foreach ($changes as $change) {
                $html .= "<div class='item'><i class='small chevron right icon'></i>{$change}</div>";
            }
            $html .= "</div>";
        }

        $html .= "</div>";
    }
    return $html;
}
?>

<div class="ui modal" id="changelog-modal">
    <i class="close icon"></i>
    <div class="header">
        <i class="history icon"></i> Changelog - SSI Newsletter
    </div>
    <div class="scrolling content">
        <?php echo formatChangelog($versions['changelog']); ?>
    </div>
    <div class="actions">
        <div class="ui positive button">Schließen</div>
    </div>
</div>

<style>
    .version-block {
        margin-bottom: 2em;
    }

    .version-block:last-child {
        margin-bottom: 0;
    }

    .version-block h3 small {
        color: #666;
        font-weight: normal;
        margin-left: 0.5em;
    }

    .version-block .ui.list {
        margin-left: 1.5em;
    }

    .version-block .ui.list .item {
        line-height: 1.5em;
        margin-bottom: 0.5em;
    }
</style>