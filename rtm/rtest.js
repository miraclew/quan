var redis = require("redis"),
    client = redis.createClient();

client.on("error", function (err) {
    console.log("Redis Error: " + err);
});

client.set("string key", "string val", redis.print);

client.get("aaa", function(err, reply) {
    console.log(typeof(reply))
    console.log(reply);
});