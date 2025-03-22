<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
</head>
<body>
<div id="messages"></div>
<input type="text" id="message" />
<button id="send">Send</button>

<script>
    const conn = new WebSocket('ws://localhost:8080'); // Connect to the WebSocket server
    const messages = document.getElementById('messages');
    const messageInput = document.getElementById('message');
    const sendButton = document.getElementById('send');

    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        const newMessage = document.createElement('p');
        newMessage.textContent = e.data;
        messages.appendChild(newMessage);
    };

    sendButton.onclick = function() {
        if (messageInput.value) {
            conn.send(messageInput.value);
            messageInput.value = '';
        }
    };
</script>
</body>
</html>