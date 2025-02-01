<?php
require_once(__DIR__ . '/../t_config.php');

// Lade Trading-Parameter Models
function loadParameterModels($db)
{
    $models_query = "SELECT id, name FROM trading_parameter_models ORDER BY name";
    return $db->query($models_query)->fetch_all(MYSQLI_ASSOC);
}

// Lade aktive User
function loadActiveUsers($db)
{
    $users_query = "SELECT id, username FROM users WHERE active = 1 ORDER BY username";
    return $db->query($users_query)->fetch_all(MYSQLI_ASSOC);
}

// Hauptdaten laden
$models = loadParameterModels($db);
$users = loadActiveUsers($db);

// HTML Header
require_once(__DIR__ . '/../includes/header.php');
?>


<div class="ui container">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <h3 class="ui header" style="margin: 0;">Aktuelles Signal</h3>
        <div class="ui label" id="connection-status">
            <i class="circle icon grey"></i>
            Connecting...
        </div>

    </div>

    <div id="signalContainer">
        <div class="ui placeholder">
            <div class="paragraph">
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="ui divider"></div>

    <form id="tradeForm" class="ui form">
        <div class="ui segments">
            <!-- User & Model Selection -->
            <div class="ui segment">
                <h4 class="ui dividing header">Grundeinstellungen</h4>
                <div class="two fields">
                    <div class="field">
                        <label>User</label>
                        <select class="ui dropdown" name="user_id" id="userId" required>
                            <option value="">User auswählen</option>
                            <?php
                            $stmt = $db->prepare("
                                SELECT 
                                    u.id,
                                    u.username,
                                    u.company,
                                    m.name as model_name
                                FROM users u
                                LEFT JOIN trading_parameter_models m ON m.id = u.default_parameter_model_id
                                WHERE u.active = 1
                                ORDER BY u.company ASC
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()) {
                                $modelInfo = $row['model_name'] ? " (Modell: {$row['model_name']})" : "";
                                echo "<option value=\"{$row['id']}\">{$row['company']}{$modelInfo}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Parameter Model</label>
                        <select class="ui dropdown" name="parameter_model" id="parameterModel">
                            <option value="">Model auswählen</option>
                            <?php
                            $stmt = $db->prepare("
                                SELECT id, name, description
                                FROM trading_parameter_models
                                WHERE is_active = 1
                                ORDER BY name ASC
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value=\"{$row['id']}\" title=\"{$row['description']}\">{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Trade Details -->
            <div class="ui segment">
                <h4 class="ui dividing header">Trade Details</h4>
                <div class="three fields">
                    <div class="field">
                        <label>Symbol</label>
                        <select class="ui dropdown" name="symbol" required>
                            <option value="ETHUSDT">ETH/USDT</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Side</label>
                        <select class="ui dropdown" name="side" id="tradeSide" required>
                            <option value="">Direction wählen</option>
                            <option value="buy">Long</option>
                            <option value="sell">Short</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Position Size (ETH)</label>
                        <input type="number" name="position_size" id="positionSize" step="0.001" required>
                    </div>
                </div>
            </div>

            <!-- Price Settings -->
            <div class="ui segment">
                <h4 class="ui dividing header">Preiseinstellungen</h4>
                <div class="four fields">
                    <div class="field">
                        <label>Entry Price</label>
                        <div class="ui right labeled input">
                            <input type="number" name="entry_price" id="entryPrice" step="0.01" required>
                            <div class="ui basic label">USDT</div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Take Profit</label>
                        <div class="ui right labeled input">
                            <input type="number" name="take_profit" id="takeProfit" step="0.01" required>
                            <div class="ui basic label">USDT</div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Stop Loss</label>
                        <div class="ui right labeled input">
                            <input type="number" name="stop_loss" id="stopLoss" step="0.01" required>
                            <div class="ui basic label">USDT</div>
                        </div>
                    </div>
                    <div class="field">
                        <label>Leverage</label>
                        <select class="ui dropdown" name="leverage" id="tradeLeverage" required>
                            <option value="">Leverage wählen</option>
                            <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?>x</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="ui segment">
                <button class="ui primary button" type="submit">
                    <i class="paper plane icon"></i>
                    Trade platzieren
                </button>
                <button class="ui button" type="button" id="useRecommendation">
                    <i class="magic icon"></i>
                    Empfehlung übernehmen
                </button>
            </div>
        </div>
    </form>

    <!-- Trade History -->
    <div class="ui segment">
        <h3 class="ui dividing header">
            <i class="history icon"></i>
            Trade History
        </h3>
        <div id="content">
            <!-- wird durch AJAX gefüllt -->
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="../assets/js/trading.js"></script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>