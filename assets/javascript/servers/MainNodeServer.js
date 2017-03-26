var socket = require('socket.io');
var express = require('express');

// Redis
var redis = require('redis');
var client  = redis.createClient();
var session = require('express-session');
var RedisStore = require('connect-redis')(session);
var bodyParser = require('body-parser');

var http = require('http');

var PHPUnserialize = require('php-unserialize');

var app = express();
var server = http.createServer(app);

var io = socket.listen(server);

var clients = {};

io.sockets.on('connection', function(socket){
    console.log("new client");

    socket.on('disconnect', function(data){
        console.log('left');
    });
});

server.listen(8082);