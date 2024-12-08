<?php
$versions = require(__DIR__ . "/../version.php");
?>

<div class="ui container">
    <div class="ui raised very padded text segment">
        <h1 class="ui blue header">
            <div align='center'>
                Willkommen bei SSI - Newsletter<br>
                <i class="big envelope outline icon"></i>
            </div>
        </h1>
        <div align='center'>Sende wichtige Mail an deine Kontakte</div>
    </div>
    <br>

    <div class="ui segments">
        <div class="ui blue segment">
            <h3 class="ui header">
                <i class="info circle icon"></i>
                System Information
                <div class="sub header">Version <?php echo htmlspecialchars($versions['version']); ?></div>
            </h3>
        </div>

        <div class="ui segment">
            <div class="ui styled fluid accordion">
                <?php
                $isFirst = true;
                foreach ($versions['changelog'] as $version => $info):
                    ?>
                    <div class="<?php echo $isFirst ? 'active' : ''; ?> title">
                        <i class="dropdown icon"></i>
                        Version <?php echo htmlspecialchars($version); ?>
                        <span class="ui small gray text">
                            (<?php echo date('d.m.Y', strtotime($info['date'])); ?>)
                        </span>
                    </div>
                    <div class="<?php echo $isFirst ? 'active' : ''; ?> content">
                        <div class="ui compact segment">
                            <?php foreach ($info['changes'] as $category => $changes): ?>
                                <div class="ui small header"><?php echo htmlspecialchars($category); ?></div>
                                <div class="ui small list">
                                    <?php foreach ($changes as $change): ?>
                                        <div class="item">
                                            <i class="<?php
                                            echo match ($category) {
                                                'Neu' => 'plus',
                                                'Verbessert' => 'arrow up',
                                                'Behoben' => 'check',
                                                default => 'circle'
                                            };
                                            ?> icon"></i>
                                            <?php echo htmlspecialchars($change); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $isFirst = false; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.ui.accordion').accordion({
            exclusive: false,
            closeNested: true
        });
    });
</script>

<style>
    .ui.accordion .title {
        font-weight: bold !important;
        padding: 0.5em 1em !important;
    }

    .ui.accordion .content {
        padding: 0.5em 1em !important;
    }

    .ui.list .item {
        padding: 0.2em 0 !important;
    }

    .ui.gray.text {
        color: #767676;
    }

    .ui.compact.segment {
        margin: 0;
    }

    .ui.small.header {
        margin: 0.5em 0 0.3em 0;
    }

    .ui.small.list {
        margin-top: 0.2em;
        margin-bottom: 0.5em;
    }
</style>