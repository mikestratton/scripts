<?php
// cat-mouse-server.php (Run this from the command line)
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop; // Use React\EventLoop\Loop instead of Factory
//use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class CatMouse implements MessageComponentInterface
{
    protected SplObjectStorage $clients;
    protected int $distance;
    protected int $catSteps;
    protected int $mouseSteps;
    protected React\EventLoop\LoopInterface $loop; // Add loop property

    public function __construct(React\EventLoop\LoopInterface $loop)
    { // Add loop parameter
        $this->clients = new \SplObjectStorage;
        $this->distance = rand(2, 10);
        $this->catSteps = 0;
        $this->mouseSteps = 0;
        $this->loop = $loop; // Initialize loop
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        $this->sendGameState($conn);


        if ($this->clients->count() === 1) { // Start the loop only for the first client
            $this->startGameLoop();
        }
        //$this->startGameLoop(); // Start the game loop
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // No client messages needed in this example.
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sendGameState(ConnectionInterface $conn)
    {
        $conn->send(json_encode([
            'distance' => $this->distance,
            'catSteps' => $this->catSteps,
            'mouseSteps' => $this->mouseSteps,
        ]));
    }


    public function startGameLoop()
    {
        echo 'Cat and mouse game started...' . PHP_EOL;

        $this->loop->addPeriodicTimer(0.5, function (React\EventLoop\TimerInterface $timer) {
            echo "Game loop running at: " . date('H:i:s') . PHP_EOL;

            if ($this->distance <= 0) {
                $this->broadcast("Cat caught the mouse!");
                $this->resetGame();
                $this->loop->cancelTimer($timer); // Stop the loop
            } elseif ($this->distance >= 20) {
                $this->broadcast("Mouse escaped!");
                $this->resetGame();
                $this->loop->cancelTimer($timer); // Stop the loop
            } else {
                $this->catMove();
                $this->mouseMove();
                $this->broadcastGameState();
                echo "Cat steps: {$this->catSteps}, Mouse steps: {$this->mouseSteps}, Distance: {$this->distance}" . PHP_EOL;
            }
        });
    }
    /*
        public function startGameLoop() {
            echo 'cat mouse here ';
            $gameOver = false;
            while($gameOver == false){
                echo "Game loop running at: " . date('H:i:s') . "\n"; // Add this line
                usleep(500000);
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
                    $this->broadcastGameState();
                    echo "Cat steps: " . $this->catSteps . "\n";
                    echo "Mouse steps: " . $this->mouseSteps . "\n";
                    echo "Distance: " . $this->distance . "\n";
                }

            }
    */
    //        $this->loop->addPeriodicTimer(3, function () { // Run every 3 seconds
//            echo "Game loop running at: " . date('H:i:s') . "\n"; // Add this line
//            if ($this->distance <= 0) {
//                $this->broadcast("Cat caught the mouse!");
//                $this->resetGame();
//            } elseif ($this->distance >= 20) {
//                $this->broadcast("Mouse escaped!");
//                $this->resetGame();
//            } else {
//                $this->catMove();
//                $this->mouseMove();
//                $this->broadcastGameState();
//            }
//        });
//}

    private function catMove()
    {
        $steps = rand(1, 3);
        $this->distance -= $steps;
        $this->catSteps += $steps;
        $this->broadcast("Cat moved $steps steps.");
    }

    private function mouseMove()
    {
        $steps = rand(1, 3);
        $this->distance += $steps;
        $this->mouseSteps += $steps;
        $this->broadcast("Mouse moved $steps steps.");
    }

    private function broadcastGameState()
    {
        $data = json_encode([
            'distance' => $this->distance,
            'catSteps' => $this->catSteps,
            'mouseSteps' => $this->mouseSteps,
        ]);

        echo "Sending JSON: $data" . PHP_EOL; // Debug output

        foreach ($this->clients as $client) {
            $this->sendGameState($client);
        }
    }

    private function broadcast($message)
    {
        foreach ($this->clients as $client) {
            $client->send(json_encode(['message' => $message]));
        }
    }

    private function resetGame()
    {
        $this->distance = rand(2, 10);
        $this->catSteps = 0;
        $this->mouseSteps = 0;
        $this->broadcastGameState();
    }
}
/*
$loop = Loop::get(); // Use Loop::get() instead of Factory::create()

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new CatMouse($loop)
        )
    ),
    8080,
    //'0.0.0.0',
    $loop
);
$server->run();
*/

$loop = Loop::get(); // Get the event loop

$webSocket = new WsServer(new CatMouse($loop));
$httpServer = new HttpServer($webSocket);
$ioServer = new IoServer($httpServer, new React\Socket\SocketServer('0.0.0.0:8080', [], $loop), $loop);

echo "WebSocket server running on ws://0.0.0.0:8080\n";

$loop->run(); // Start the event loop
$server->run();
