var apn = require('apn');

var options = { passphrase:'miraclew' };
var apnConnection = new apn.Connection(options);

apnConnection.on('connected', function() {
    console.log("Connected");
});

apnConnection.on('transmitted', function(notification, device) {
    console.log("Notification transmitted to:" + device.token.toString('hex'));
});

apnConnection.on('transmissionError', function(errCode, notification, device) {
    console.error("Notification caused error: " + errCode + " for device ", device, notification);
});

apnConnection.on('timeout', function () {
    console.log("Connection Timeout");
});

apnConnection.on('disconnected', function() {
    console.log("Disconnected from APNS");
});

apnConnection.on('socketError', console.error);

var apnPushToUser = function(token, message, len) {
    var myDevice = new apn.Device(token);
    var note = new apn.Notification();
    note.expiry = Math.floor(Date.now() / 1000) + 3600; // Expires 1 hour from now.
    note.badge = len;
    note.sound = "ping.aiff";
    note.alert = "你有一条新的消息";//
    note.payload = {aa: 11};

    apnConnection.pushNotification(note, myDevice);
}

exports.apnPushToUser = apnPushToUser;