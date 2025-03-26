<?php

require_once 'cat-mouse-server.php';

?>
<!DOCTYPE html>

<html>
<head>
  <title>Cat and Mouse Chase</title>
</head>
<body>
<h1><?php var_dump($this->distance); ?></h1>
<div id="game">
  <p>Distance: <span id="distance"></span></p>
  <p>Cat Steps: <span id="catSteps"></span></p>
  <p>Mouse Steps: <span id="mouseSteps"></span></p>
  <div id="messages"></div>
</div>

<script>
  const conn = new WebSocket('ws://localhost:8080');
  const distanceDisplay = document.getElementById('distance');
  const catStepsDisplay = document.getElementById('catSteps');
  const mouseStepsDisplay = document.getElementById('mouseSteps');
  const messages = document.getElementById('messages');

  conn.onopen = function(e) {
    console.log("Connection established!");
  };

  conn.onmessage = function(e) {
    const data = JSON.parse(e.data);
    if (data.distance !== undefined) {
      updateDisplay(data); // Call updateDisplay function
    }
    if (data.message !== undefined) {
      const newMessage = document.createElement('p');
      newMessage.textContent = data.message;
      messages.appendChild(newMessage);
    }
    console.log(data);
  };

  function updateDisplay(data) {
    distanceDisplay.textContent = data.distance;
    catStepsDisplay.textContent = data.catSteps;
    mouseStepsDisplay.textContent = data.mouseSteps;
  }
</script>
</body>
</html>