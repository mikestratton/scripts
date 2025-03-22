<?php
// cat-mouse-server.php (Run this from the command line)
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class CatMouse implements MessageComponentInterface {
    protected $clients;
    protected $distance;
    protected $catSteps;
    protected $mouseSteps;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->distance = rand(2, 10); // Initial random distance
        $this->catSteps = 0;
        $this->mouseSteps = 0;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        $this->sendGameState($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // No client messages needed in this example.
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sendGameState(ConnectionInterface $conn) {
        $conn->send(json_encode([
            'distance' => $this->distance,
            'catSteps' => $this->catSteps,
            'mouseSteps' => $this->mouseSteps,
        ]));
        $this->runGame();
    }

    private function runGame() {
        if ($this->distance <= 0) {
            $this->broadcast("Cat caught the mouse!");
            $this->resetGame();
        } elseif ($this->distance >= 20) {
            $this->broadcast("Mouse escaped!");
            $this->resetGame();
        } else {
            $this->catMove();
            $this->mouseMove();
            $this->broadcastGameState();
            usleep(500000); // Wait for 0.5 seconds before next move.
            $this->runGame();
        }
    }

    private function catMove() {
        $steps = rand(1, 3);
        $this->distance -= $steps;
        $this->catSteps += $steps;
        $this->broadcast("Cat moved $steps steps.");
    }

    private function mouseMove() {
        $steps = rand(1, 3);
        $this->distance += $steps;
        $this->mouseSteps += $steps;
        $this->broadcast("Mouse moved $steps steps.");
    }

    private function broadcastGameState() {
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'distance' => $this->distance,
                'catSteps' => $this->catSteps,
                'mouseSteps' => $this->mouseSteps,
            ]));
        }
    }

    private function broadcast($message) {
        foreach ($this->clients as $client) {
            $client->send(json_encode(['message' => $message]));
        }
    }

    private function resetGame() {
        $this->distance = rand(2, 10);
        $this->catSteps = 0;
        $this->mouseSteps = 0;
        $this->broadcastGameState();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new CatMouse()
        )
    ),
    8080
);

$server->run();
?>