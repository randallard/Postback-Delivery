var app = require('express')();
var http = require('http').Server(app);

var io = require('socket.io')(http);

app.get('/', function(req, res){
  res.sendFile(__dirname + '/index.html');
});

io.on('connection', function(socket){
  console.log('connection established');
//  socket.on('postbackData', function (data) {
  socket.on('postbackData', function (data) {
    io.emit('postbackData', data);
    console.log('received ' + data);
  });
});

http.listen(3000, function(){
  console.log('listening on *:3000');
});
