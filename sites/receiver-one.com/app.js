var http = require('http');
var fs = require('fs');

http.createServer(function (req, res) {
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
	fs.appendFile('log.txt'
		, dateNow.toLocaleString() + '\tUnexpected Method\t' + req.method + '\n'
		, function(err){
			if (err) throw err;
		}
	);
        dataReceived = req.url;
  }
  var dateNow = new Date();
  console.log(dateNow.toLocaleString() + '\tData Received\t' + receivedMethod + '\t' + dataReceived);
  fs.appendFile('log.txt'
    , dateNow.toLocaleString() + '\tData Received\t' + receivedMethod + '\t' + dataReceived + '\n'
    , function(err){
    	if (err) throw err;
    }
  );

  res.writeHead(200);
  res.end('receiver-one.com heard: [' + req.url + ']');
}).listen(3001, function(){
  	var dateNow = new Date();
	console.log(dateNow.toLocaleString() + '\tStart Listening\tport 3001');
  	fs.appendFile('log.txt'
	    , dateNow.toLocaleString() + '\tStart Listening\tport 3001\n'
	    , function(err){
	        if (err) throw err;
	    }
  	);

});
