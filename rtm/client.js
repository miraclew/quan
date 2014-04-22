var ws = new WebSocket('ws://localhost:8080/?token=AtceXh2JOJ4mnWCPE8Wj','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}


var ws = new WebSocket('ws://quanrtm.duapp.com/?token=AtceXh2JOJ4mnWCPE8Wj','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}
ws.onerror=function(err) {console.log(err)}
ws.onopen=function() {console.log('connected')}
ws.onclose=function() {console.log('closed')}