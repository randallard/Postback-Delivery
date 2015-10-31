var http = require('http');
var io = require('socket.io-client');

process.on('message', function(msg) {  
  if (msg === 'shutdown') {
//    close_all_connections();
//    delete_cache();
    http.close();
    process.exit(0);
  }
});

http.createServer(function (req, res) {
  var R3 = (new Date).getTime();
  var dataReceived;
  var receivedMethod = '[' + req.method + ']';
  if (req.method == 'POST') {
    req.on('data', function(chunk) {
      dataReceived = chunk.toString;
    });
  } else if (req.method == "GET"){
      dataReceived = req.url;
  }
  else {
	var dateNow = new Date();
	console.log(dateNow.toLocaleString() + '\tUnexpected Method\t' + req.method);
        dataReceived = req.url;
  }
  var dateNow = new Date();
  console.log(dateNow.toLocaleString() + '\tData Received\t' + receivedMethod + '\t' + dataReceived + ' at [' + R3 + ']ms');

  res.writeHead(200);
  res.end('receiver-one.com heard: [' + req.url + ']');

  socket = io.connect('http://postback-monitor.com', {
      port: 3000
  });
  socket.on('connect', function () { console.log("connected to postback monitor"); });
  socket.emit('postbackData', req.url+'&R3='+R3);
}).listen(3001);
