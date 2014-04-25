var ws = new WebSocket('ws://localhost:8080/?token=y46rK6gxQRivazdhksY7','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}
ws.onerror=function(err) {console.log(err)}
ws.onopen=function() {console.log('connected')}
ws.onclose=function() {console.log('closed')}

var ws = new WebSocket('ws://quanrtm.duapp.com/?token=y46rK6gxQRivazdhksY7','mp-v1');
ws.onmessage = function(msg) {console.log(msg.data)}
ws.onerror=function(err) {console.log(err)}
ws.onopen=function() {console.log('connected')}
ws.onclose=function() {console.log('closed')}