<?php
// websocket.php
require('vendor/autoload.php');
require_once(__DIR__ . '/t_config.php');
require_once(__DIR__ . '/classes/BitgetTrading.php');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;

class BitgetWebSocketServer implements \Ratchet\MessageComponentInterface
{
    protected $clients;
    protected $trading;
    protected $db;
    protected $lastError = null;

    public function __construct($db)
    {
        $this->clients = new \SplObjectStorage;
        $this->db = $db;

        try {
            // Debug: API Credentials Abfrage
            $query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials 
                    WHERE platform = 'Bitget' AND is_active = 1 
                    ORDER BY id DESC LIMIT 1";

            echo "Ausführe Query: " . $query . "\n";

            $result = $this->db->query($query);

            if (!$result) {
                throw new Exception('Datenbankfehler: ' . $this->db->error);
            }

            if ($result->num_rows === 0) {
                throw new Exception('Keine aktiven API-Credentials gefunden');
            }

            $credentials = $result->fetch_assoc();

            // Debug: Credentials prüfen
            echo "Gefundene Credentials:\n";
            echo "API Key: " . substr($credentials['api_key'], 0, 5) . "...\n";
            echo "API Secret: " . substr($credentials['api_secret'], 0, 5) . "...\n";
            echo "API Passphrase: " . (isset($credentials['api_passphrase']) ? "gefunden" : "nicht gefunden") . "\n";

            if (!isset($credentials['api_passphrase']) || empty($credentials['api_passphrase'])) {
                throw new Exception('API Passphrase fehlt in den Credentials');
            }

            // BitgetTrading Instanz erstellen
            $this->trading = new BitgetTrading(
                $credentials['api_key'],
                $credentials['api_secret'],
                $credentials['api_passphrase']
            );

            echo "Trading Instanz erfolgreich erstellt\n";

        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            echo "Fehler beim Initialisieren: " . $e->getMessage() . "\n";
        }
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Neue Verbindung! ({$conn->resourceId})\n";

        if ($this->lastError) {
            $conn->send(json_encode([
                'error' => $this->lastError,
                'type' => 'initialization_error'
            ]));
            return;
        }

        $this->sendUpdates($conn);
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg)
    {
        echo "Nachricht empfangen von {$from->resourceId}: $msg\n";
    }

    public function onClose(\Ratchet\ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Verbindung {$conn->resourceId} geschlossen\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        echo "Fehler bei Client {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function sendUpdates(\Ratchet\ConnectionInterface $conn)
    {
        try {
            if (!$this->trading) {
                throw new Exception('Trading-Instanz nicht initialisiert');
            }

            $klines = $this->trading->getKlines('ETHUSDT_UMCBL');
            $accountInfo = $this->trading->getAccountInfo('ETHUSDT_UMCBL');

            $data = [
                'currentPrice' => $klines['currentPrice'] ?? null,
                'crossWalletBalance' => $accountInfo['data']['equity'] ?? null,
                'availableBalance' => $accountInfo['data']['available'] ?? null,
                'crossUnPnl' => $accountInfo['data']['unrealizedPL'] ?? null,
                'timestamp' => time() * 1000
            ];

            $conn->send(json_encode($data));
            echo "Daten gesendet: " . json_encode($data) . "\n";

        } catch (Exception $e) {
            $errorMsg = "Fehler beim Senden der Updates: " . $e->getMessage();
            echo $errorMsg . "\n";
            $conn->send(json_encode([
                'error' => $errorMsg,
                'type' => 'update_error'
            ]));
        }
    }

    public function broadcastUpdates()
    {
        if ($this->lastError) {
            return;
        }

        foreach ($this->clients as $client) {
            $this->sendUpdates($client);
        }
    }
}

// Server Setup
try {
    $loop = Factory::create();
    global $db;

    if (!$db) {
        throw new Exception('Keine Datenbankverbindung verfügbar');
    }

    // Debug: Datenbankverbindung prüfen
    if ($db->connect_error) {
        throw new Exception('Datenbankverbindungsfehler: ' . $db->connect_error);
    }

    $webSocket = new BitgetWebSocketServer($db);

    $server = new IoServer(
        new HttpServer(
            new WsServer($webSocket)
        ),
        new Server('0.0.0.0:8080', $loop),
        $loop
    );

    $loop->addPeriodicTimer(2, function () use ($webSocket) {
        $webSocket->broadcastUpdates();
    });

    echo "WebSocket Server läuft auf ws://0.0.0.0:8080\n";

    $server->run();

} catch (Exception $e) {
    echo "Kritischer Fehler: " . $e->getMessage() . "\n";
    exit(1);
}