var ws = new WebSocket('ws://localhost:8080/?token=tSbbaAQD1hnS7ZEdRA0D','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}