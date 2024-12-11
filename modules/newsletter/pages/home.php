<?php
include __DIR__ . '/../n_config.php';
$versions = require(__DIR__ . "/../version.php");

// Statistiken
$stats = [
    'newsletters' => $db->query("SELECT COUNT(*) as count FROM email_contents WHERE user_id = '$userId'")->fetch_object()->count,
    'sent' => $db->query("SELECT COUNT(DISTINCT content_id) as count FROM email_jobs WHERE status IN ('send', 'open', 'click')")->fetch_object()->count,
    'recipients' => $db->query("SELECT COUNT(*) as count FROM recipients WHERE user_id = '$userId' AND unsubscribed = 0")->fetch_object()->count,
    'groups' => $db->query("SELECT COUNT(*) as count FROM groups WHERE user_id = '$userId'")->fetch_object()->count,
    'opened' => $db->query("SELECT COUNT(DISTINCT recipient_id) as count FROM email_jobs WHERE status = 'open'")->fetch_object()->count,
    'clicked' => $db->query("SELECT COUNT(DISTINCT recipient_id) as count FROM email_jobs WHERE status = 'click'")->fetch_object()->count
];
?>

<div class="ui container" style="padding-top: 2em; padding-bottom: 2em;">
    <div class="ui basic segment center aligned">
        <h1 class="ui header">
            <div><i class="envelope open outline icon blue"></i> SSI - Newsletter System</div>
            <div class="sub header" style="margin-top: 1em; color: #555;">
                Ihr professionelles E-Mail-Marketing-Tool:<br>
                Newsletter erstellen, Empfänger verwalten und Erfolge messen.
            </div>
        </h1>
    </div>

    <div class="ui stackable grid">
        <div class="six wide column">
            <div class="ui segments" style="border-radius: 5px;">
                <div class="ui secondary segment" style="background-color: #f7f7f7;">
                    <h4 class="ui header" style="margin-bottom: 0;">Statistiken</h4>
                </div>
                <div class="ui segment">
                    <div class="ui relaxed list">
                        <div class="item">
                            <i class="envelope icon"></i>
                            <div class="content">
                                <strong><?php echo number_format($stats['newsletters']); ?></strong> Newsletter
                            </div>
                        </div>
                        <div class="item">
                            <i class="users icon"></i>
                            <div class="content">
                                <strong><?php echo number_format($stats['recipients']); ?></strong> Empfänger
                            </div>
                        </div>
                        <div class="item">
                            <i class="paper plane icon"></i>
                            <div class="content">
                                <strong><?php echo number_format($stats['sent']); ?></strong> Versendet
                            </div>
                        </div>
                        <div class="item">
                            <i class="eye icon"></i>
                            <div class="content">
                                <strong><?php echo number_format($stats['opened']); ?></strong> Geöffnet
                            </div>
                        </div>
                        <div class="item">
                            <i class="mouse pointer icon"></i>
                            <div class="content">
                                <strong><?php echo number_format($stats['clicked']); ?></strong> Geklickt
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ten wide column">
            <div class="ui segments" style="border-radius: 5px;">
                <div class="ui secondary segment" style="background-color: #f7f7f7;">
                    <h4 class="ui header" style="margin-bottom:0;">Version
                        <?php echo htmlspecialchars($versions['version']); ?>
                    </h4>
                </div>
                <div class="ui segment">
                    <div class="ui accordion">
                        <?php
                        $isFirst = true;
                        foreach ($versions['changelog'] as $version => $info):
                            ?>
                            <div class="<?php echo $isFirst ? 'active' : ''; ?> title">
                                <i class="dropdown icon"></i>
                                <strong>Version <?php echo htmlspecialchars($version); ?></strong>
                                <small style="color: #777;">(<?php echo date('d.m.Y', strtotime($info['date'])); ?>)</small>
                            </div>
                            <div class="<?php echo $isFirst ? 'active' : ''; ?> content">
                                <div class="ui small relaxed list">
                                    <?php foreach ($info['changes'] as $category => $changes): ?>
                                        <div class="header" style="margin-top:0.5em;">
                                            <b><?php echo htmlspecialchars($category); ?></b>
                                        </div>
                                        <?php foreach ($changes as $change): ?>
                                            <div class="item" style="color: #555;">- <?php echo htmlspecialchars($change); ?></div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php
                            $isFirst = false;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.ui.accordion').accordion();
    });
</script>