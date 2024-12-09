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

<div class="ui container">
    <!-- Header -->
    <div class="ui basic segment center aligned">
        <div class="ui text container">
            <h1 class="ui header">

                <div class="content">
                    <i class="envelope open outline icon  blue"></i>SSI - Newsletter System
                    <div class="sub header" style="margin-top: 1em;">
                        Ihr professionelles E-Mail-Marketing-Tool:
                        Newsletter erstellen, Empfänger verwalten und Erfolge messen - alles in einem System.
                    </div>
                </div>
            </h1>

        </div>
    </div>

    <div class="ui stackable grid">
        <!-- Linke Spalte: Statistiken -->
        <div class="six wide column">
            <div class="ui segments">
                <div class="ui secondary segment">
                    <h4 class="ui header">Statistiken</h4>
                </div>
                <div class="ui segment">
                    <div class="ui list">
                        <div class="item">
                            <i class="envelope icon"></i>
                            <div class="content">
                                <div class="header"><?php echo number_format($stats['newsletters']); ?> Newsletter</div>
                            </div>
                        </div>
                        <div class="item">
                            <i class="users icon"></i>
                            <div class="content">
                                <div class="header"><?php echo number_format($stats['recipients']); ?> Empfänger</div>
                            </div>
                        </div>
                        <div class="item">
                            <i class="paper plane icon"></i>
                            <div class="content">
                                <div class="header"><?php echo number_format($stats['sent']); ?> Versendet</div>
                            </div>
                        </div>
                        <div class="item">
                            <i class="eye icon"></i>
                            <div class="content">
                                <div class="header"><?php echo number_format($stats['opened']); ?> Geöffnet</div>
                            </div>
                        </div>
                        <div class="item">
                            <i class="mouse pointer icon"></i>
                            <div class="content">
                                <div class="header"><?php echo number_format($stats['clicked']); ?> Geklickt</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechte Spalte: Navigation & Version -->
        <div class="ten wide column">


            <!-- Version -->
            <div class="ui segments">
                <div class="ui secondary segment">
                    <h4 class="ui header">Version <?php echo htmlspecialchars($versions['version']); ?></h4>
                </div>
                <div class="ui segment">
                    <div class="ui accordion">
                        <?php
                        $isFirst = true;
                        foreach ($versions['changelog'] as $version => $info):
                            ?>
                            <div class="<?php echo $isFirst ? 'active' : ''; ?> title">
                                <i class="dropdown icon"></i>
                                Version <?php echo htmlspecialchars($version); ?>
                                <small>(<?php echo date('d.m.Y', strtotime($info['date'])); ?>)</small>
                            </div>
                            <div class="<?php echo $isFirst ? 'active' : ''; ?> content">
                                <div class="ui small list">
                                    <?php foreach ($info['changes'] as $category => $changes): ?>
                                        <div class="header"><b><?php echo htmlspecialchars($category); ?></b></div>
                                        <?php foreach ($changes as $change): ?>
                                            <div class="item"> - <?php echo htmlspecialchars($change); ?></div>
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
</div>

<script>
    $(document).ready(function () {
        $('.ui.accordion').accordion();
    });
</script>