<?php
// cat-mouse-server.php (Run this from the command line)
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop; // Use React\EventLoop\Loop instead of Factory
use React\EventLoop\LoopInterface;

class CatMouse implements MessageComponentInterface {
    protected SplObjectStorage $clients;
    protected int $distance;
    protected int $catSteps;
    protected int $mouseSteps;
    protected LoopInterface $loop; // Add loop property

    public function __construct(LoopInterface $loop) { // Add loop parameter
        $this->clients = new \SplObjectStorage;
        $this->distance = rand(2, 10);
        $this->catSteps = 0;
        $this->mouseSteps = 0;
        $this->loop = $loop; // Initialize loop
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        $this->sendGameState($conn);
        $this->startGameLoop(); // Start the game loop
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
//        usleep(250000);
    }

    public function startGameLoop() {
        echo 'cat mouse here ';
        $gameOver = false;
        while($gameOver == false){
            echo "Game loop running at: " . date('H:i:s') . "\n";
//            usleep(500000); // 0.5 second delay
            if ($this->distance <= 0) {
                $this->broadcast("Cat caught the mouse!");
                $this->resetGame();
                $gameOver = true;
            } elseif ($this->distance >= 20) {
                $this->broadcast("Mouse escaped!");
                $this->resetGame();
                $gameOver = true;
            } else {
                $this->catMove();
                $this->mouseMove();
                $this->broadcastGameState(); // Sends data to the browser

                echo "Cat steps: " . $this->catSteps . "\n";
                echo "Mouse steps: " . $this->mouseSteps . "\n";
                echo "Distance: " . $this->distance . "\n";
            }
        }
    }

    private function catMove() {
        $steps = rand(0, 5);
        $this->distance -= $steps;
        $this->catSteps += $steps;
        $this->broadcast("Cat moved $steps steps.");
    }

    private function mouseMove() {
        $steps = rand(0, 5);
        $this->distance += $steps;
        $this->mouseSteps += $steps;
        $this->broadcast("Mouse moved $steps steps.");
    }

    private function broadcastGameState() {
        foreach ($this->clients as $client) {
            $this->sendGameState($client);
//            usleep(500000);
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

$loop = Loop::get(); // Use Loop::get() instead of Factory::create()
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new CatMouse($loop)
        )
    ),
    8080,
    '0.0.0.0',
    $loop
);

$server->run();

?>