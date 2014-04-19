var ws = new WebSocket('ws://localhost:8080/?token=Tvoi7Zq9KzBLEsDw5lx7','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}