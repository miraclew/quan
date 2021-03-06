#!/usr/bin/env node
var WebSocketServer = require('websocket').server;
var http = require('http');
var bodyParser = require('body-parser');
var express = require('express');
var redis = require("redis"),
    rc = redis.createClient();
var push = require('./push');

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
    if (parseInt(channel_id) < 0) { // send to recipients
        var recipients = req.body.recipients.split(',');
        sendToUsers(recipients, req.body);
    } else { // send to channel members
        rc.smembers('cms:'+channel_id, function(err, reply){
            if (err == null) {
                sendToUsers(reply, req.body);
            };
        });
    }

    res.json({'code':0});
});

function sendToUsers(users, message) {
    var skip_sender = message.skip_sender;

    for (var i = 0; i < users.length; i++) {
        var k = users[i];
        if (k == message.sender_id && skip_sender == 1) {
            console.log("skip_sender: "+k);
            continue;
        };

        pushToQueue(k, message)
    };
}

function pushToQueue(userId, message) {
    rc.lpush('mq:'+userId,  JSON.stringify(message), function(err) {
        if (err) {
            console.log('lpush failed');
            return;
        }

        processQueue(userId);
    });
}

function processQueue(userId) {
    rc.rpop('mq:'+userId, function(err, reply) {
        if (err) {
            console.log('rpop failed');
            return;
        }

        if (reply === null) {
            return;
        };

        var message = JSON.parse(reply);
        var connection = connections[userId];
        if (connection != null) {
            console.log('send message:'+message.id+' to: '+userId);
            connection.sendUTF(reply, function(err) {
                if (err) { // send failed, queue it up
                    rc.rpush('mq:'+userId,  reply);
                    console.log('sendUTF message('+message.id+') Error:' + err);
                } else {
                    process.nextTick(function(){
                        processQueue(userId);
                    });
                }
            });
        } else {
            // check if the device is ios device
            rc.rpush('mq:'+userId,  reply, function(err2, len){
                if (err2 != null) {
                    console.log(err2);
                };
                console.log(len);
                rc.get("udt:"+userId, function(err, deviceToken) {
                    if (err != null) {
                        console.log(err);
                    };
                    if (deviceToken !== null) {
                        console.log("apn push => user:"+userId+" len="+len + ' deviceToken:' +deviceToken);
                        push.apnPushToUser(deviceToken, reply, len);
                    } else {
                        console.log("user:"+userId+" offline, and has no registered device");
                    }
                });
            });
        }
    });
}

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
    if (!originIsAllowed(request.origin)) {
      // Make sure we only accept requests from an allowed origin
      request.reject();
      console.log((new Date()) + ' Connection from origin ' + request.origin + ' rejected.');
      return;
    }

    var token = request.resourceURL.query.token;
    console.log('request with token:'+token);

    rc.get("token:"+token, function(err, reply) {
        if (err != null) {
            console.log(err);
        };
        if (reply == null) {
            request.reject();
            console.log((new Date()) + ' Connection with token ' + token + ' rejected.');
            return;
        } else {
            var connection = request.accept('mp-v1', request.origin);
            var userId = reply;
            connection.userId = userId;
            connections[userId] = connection;
            console.log("User:" +userId+" connected - IP: " + connection.remoteAddress);
            connection.on('message', function(message) {
                console.log('Received Message: ' + message.utf8Data);
                if (message.type === 'utf8') {
                    if (message.utf8Data == 'ping') {
                        processQueue(userId);
                        connection.sendUTF('pong');
                    } else {
                    }
                }
            });
            connection.on('close', function(reasonCode, description) {
                console.log("User:" + userId + " " + connection.remoteAddress + " disconnected");
                delete connections[userId];
            });

            processQueue(userId);
        };
    });
});
