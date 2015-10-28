var http = require('http');

// account for redis data here as well?
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
  console.log(dateNow.toLocaleString() + '\tData Received\t' + receivedMethod + '\t' + dataReceived + '&R3=' + R3);

  res.writeHead(200);
  res.end('receiver-one.com heard: [' + req.url + ']');
}).listen(3001);
