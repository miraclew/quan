#!/usr/bin/env node
var WebSocketServer = require('websocket').server;
var http = require('http');
var bodyParser = require('body-parser');
var express = require('express');
var redis = require("redis"),
    rc = redis.createClient();

rc.on("error", function (err) {
    console.log("Redis Error: " + err);
});

var connections = {};
var app = express();
app.use(bodyParser());

app.get('/', function(req, res) {
    res.send('hello');
});

app.post('/messages', function(req, res) {
    console.log('post /messages: ');
    console.log(req.body);
    var channel_id = req.body.channel_id;

    rc.smembers('cms:'+channel_id, function(err, reply){
        if (err == null) {
            for (var i = 0; i < reply.length; i++) {
                var k = reply[i];
                console.log('send to: '+k);
                var connection = connections[k];
                if (connection != null) {
                    connection.sendUTF(JSON.stringify(req.body));
                };
            };
        };
    });

    // for(var k in connections) {
    //     var connection = connections[k];
    //     connection.sendUTF(JSON.stringify(req.body));
    // }

    res.json({'code':0});
});

var httpServer = app.listen(8080);

wsServer = new WebSocketServer({
    httpServer: httpServer,
    autoAcceptConnections: false
});

function originIsAllowed(origin) {
    console.log('origin:' + origin); // e.g. http://www.baidu.com
    // put logic here to detect whether the specified origin is allowed.
    return true;
}

wsServer.on('request', function(request) {
    console.log('request....');
    if (!originIsAllowed(request.origin)) {
      // Make sure we only accept requests from an allowed origin
      request.reject();
      console.log((new Date()) + ' Connection from origin ' + request.origin + ' rejected.');
      return;
    }

    var token = request.resourceURL.query.token;
    console.log('token:'+token);

    rc.get("token:"+token, function(err, reply) {
        console.log(err);
        if (reply == null) {
            request.reject();
            console.log((new Date()) + ' Connection with token ' + token + ' rejected.');
            return;
        } else {
            var connection = request.accept('mp-v1', request.origin);
            var userId = reply;
            connection.userId = userId;
            connections[userId] = connection;
            console.log(connection.remoteAddress + " connected - Protocol Version " + connection.webSocketVersion);
            connection.on('message', function(message) {
                if (message.type === 'utf8') {
                    console.log('Received Message: ' + message.utf8Data);
                    connection.sendUTF(message.utf8Data);
                }
            });
            connection.on('close', function(reasonCode, description) {
                console.log(connection.remoteAddress + " disconnected");
                delete connections[userId];
            });
        };
    });
});
