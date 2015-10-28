var redis    = require('redis').createClient(),
    request  = require('request');

// for safe shutdown do this - account for redis here as well
process.on('message', function(msg) {  
  if (msg === 'shutdown') {
    //close_all_connections();
    //delete_cache();
    server.close();
    process.exit(0);
  }
});

redis.on('error', function (err) {
        var dateNow = new Date();
        console.log(dateNow.toLocaleString() + '\tError\t' + err);
});

sendData();

function sendData(){
  redis.blpop('postbackQueue', 0, function(err, data)  {
    var postbackData = JSON.parse(data[1]);
    var options = {
      uri: postbackData.url,
      method: postbackData.method,
      timeout: 10000,
      followRedirect: true,
      maxRedirects: 10
    };
    if ( postbackData.method == 'POST' ) {
	options.form = postbackData.data;
    }
    request(options, function(error, response, body) {
        var dateNow = new Date();
        var logString = dateNow.toLocaleString() + '\tReceived\tBody:[' + body + ']\tStatus:[' + response.statusCode + ']\tError:[' + error + ']';
        console.log(logString);
    });
    process.nextTick(sendData);
  });
}

