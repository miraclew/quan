var ws = new WebSocket('ws://localhost:8080/?token=AtceXh2JOJ4mnWCPE8Wj','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}