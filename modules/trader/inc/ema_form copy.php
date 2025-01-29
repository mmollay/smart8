<?php
include(__DIR__ . '/../t_config.php');
//include(__DIR__ . '/../../../../smartform/include_form.php');

function printServerTable($title, $tbodyContent, $serverType)
{
    if (!$tbodyContent)
        return;
    $labelClass = $serverType == 'real' ? 'red' : 'orange';
    $labelText = $serverType == 'real' ? 'Real' : 'Demo';

    echo "<h3><div class='ui label $labelClass'>$labelText</div> $title</h3>
          <table class='ui small compact celled striped single line table' style='max-width:1200px'>
            <thead>
                <tr>
                    <th class='wide four'>Server Details</th>
                    <th>MT5 Server</th>
                    <th colspan='2'>Strategy & Lots</th>
                    <th>Info</th>
                    <th class=''></th>
                    <th>Kill</th>
                </tr>
            </thead>
            <tbody id='{$serverType}-servers-body'>
                $tbodyContent
            </tbody>
          </table>";
}

$serverIps = getAllServerIps($mysqli);
$tr = ['real' => '', 'demo' => ''];

foreach ($serverIps as $server) {
    $serverType = $server['real_account'] == 1 ? 'real' : 'demo';
    $tr[$serverType] .= "
    <tr id='server-row-{$server['server_id']}'>
        <td>
            <b>{$server['name']}</b><br>
            {$server['url']}<br>
            Account: {$server['account']} : {$server['broker_matchcode']}
        </td>
        <td id='mt5-status-{$server['server_id']}'>
            <div class='ui active mini inline loader'></div>
        </td>
        <td colspan='2' id='strategy-status-{$server['server_id']}'>
            <div class='ui active mini inline loader'></div>
        </td>
        <td style='text-align: right;' id='server-position-col-{$server['server_id']}'>
            <span id='server-positions-{$server['server_id']}'>
                <div class='ui active mini inline loader'></div>
            </span>
        </td>
        <td class='center aligned'></td>
        <td id='kill-button-{$server['server_id']}' class='center aligned'>
            <div class='ui active mini inline loader'></div>
        </td>
    </tr>";
}

printServerTable("Servers", $tr['real'] ?? '', 'real');
printServerTable("Servers", $tr['demo'] ?? '', 'demo');
?>

<script>
    $(document).ready(function () {
        const servers = <?php echo json_encode($serverIps); ?>;
        const BATCH_SIZE = 20;

        for (let i = 0; i < servers.length; i += BATCH_SIZE) {
            const batch = servers.slice(i, i + BATCH_SIZE);
            Promise.all(batch.map(server => {
                return new Promise((resolve) => {
                    loadServerDetails(server, resolve);
                });
            }));
        }

        updateServerInfo();

        if (typeof window.serverInfoInterval === 'undefined') {
            window.serverInfoInterval = setInterval(updateServerInfo, 40000);
        }
    });

    function loadServerDetails(server, callback = () => { }) {
        $.ajax({
            url: 'ajax/get_server_status.php',
            method: 'POST',
            data: server,
            success: function (response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.mt5Button && data.strategyContent && data.killButton) {
                        updateServerRow(server.server_id, data);
                    } else {
                        showError(server.server_id);
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    showError(server.server_id);
                }
                callback();
            },
            error: function (xhr, status, error) {
                console.error('Ajax error:', error);
                showError(server.server_id);
                callback();
            }
        });
    }

    function updateServerRow(serverId, data) {
        document.getElementById(`mt5-status-${serverId}`).innerHTML = data.mt5Button;
        document.getElementById(`strategy-status-${serverId}`).innerHTML = data.strategyContent;
        document.getElementById(`kill-button-${serverId}`).innerHTML = data.killButton;
        $('.ui.dropdown').dropdown();
        $('.ui.popup').popup();
    }

    function showError(serverId) {
        ['mt5-status', 'strategy-status', 'kill-button'].forEach(id => {
            document.getElementById(`${id}-${serverId}`).innerHTML =
                '<div class="ui red message tiny">Ladefehler</div>';
        });
    }

    function startStrategy(serverId) {
        const form = $(`#form_start${serverId}`);
        $.ajax({
            url: 'ajax/post.php',
            type: 'POST',
            data: {
                strategy_value: 'startStrategy',
                server_id: serverId,
                size: form.find('[name="size"]').val(),
                strategy: form.find('[name="strategy"]').val(),
                startAuto: form.find('[name="startAuto"]').val()
            },
            success: function (response) {
                after_post_ema(response);
                const server = <?php echo json_encode($serverIps); ?>.find(s => s.server_id === serverId);
                loadServerDetails(server);
            }
        });
    }

    function killServer(serverId) {
        if (confirm('Sind Sie sicher, dass Sie den Server stoppen möchten?')) {
            $.ajax({
                url: 'ajax/post.php',
                type: 'POST',
                data: {
                    kill_all: 1,
                    server_id: serverId
                },
                success: function (response) {
                    after_post_request(response);
                    const server = <?php echo json_encode($serverIps); ?>.find(s => s.server_id === serverId);
                    loadServerDetails(server);
                }
            });
        }
    }
</script>