#!/usr/bin/env node
var WebSocketServer = require('websocket').server;
var http = require('http');
var bodyParser = require('body-parser');
var express = require('express');

var app = express();
app.use(bodyParser());

app.get('/', function(req, res) {
    //res.render('index', { layout: false });
    res.send('hello');
});

app.post('/message', function(req, res) {
    console.log(req.body.abc);
    if (req.body.abc) {
        res.send('sucess');
    } else {
        res.send('error');
    }
});

app.listen(8080);

wsServer = new WebSocketServer({
    httpServer: app,
    autoAcceptConnections: false
});

var connections = [];

function originIsAllowed(origin) {
    console.log('origin:' + origin); // e.g. http://www.baidu.com
    // put logic here to detect whether the specified origin is allowed.
    return true;
}

function authToken(token) {
    return true;
}

wsServer.on('request', function(request) {
    var token = request.resourceURL.query.token;
    console.log('token:'+token);

    if (!authToken(token)) {
        request.reject();
        console.log((new Date()) + ' Connection with token ' + token + ' rejected.');
        return;
    };

    if (!originIsAllowed(request.origin)) {
      // Make sure we only accept requests from an allowed origin
      request.reject();
      console.log((new Date()) + ' Connection from origin ' + request.origin + ' rejected.');
      return;
    }

    var connection = request.accept('mp-protocol-v1', request.origin);
    connections.push(connection);
    console.log(connection.remoteAddress + " connected - Protocol Version " + connection.webSocketVersion);
    connection.on('message', function(message) {
        if (message.type === 'utf8') {
            console.log('Received Message: ' + message.utf8Data);
            connection.sendUTF(message.utf8Data);
        }
    });
    connection.on('close', function(reasonCode, description) {
        console.log(connection.remoteAddress + " disconnected");
        var index = connections.indexOf(connection);
        if (index !== -1) {
            // remove the connection from the pool
            connections.splice(index, 1);
        }
    });
});
