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
    var thisR2 = (new Date()).getTime();
    var postbackData = JSON.parse(data[1]);
    if ( postbackData.stopwatch == 'on' ) {
	    postbackData.R2 = thisR2;
    }
    var options = {
      method: postbackData.method,
      timeout: 10000,
      followRedirect: true,
      maxRedirects: 10
    };
    if ( postbackData.method.toUpperCase() == 'POST' ) {
        options.uri = postbackData.url;
	if ( postbackData.stopwatch == 'on' ) {
		postbackData.S3 = (new Date()).getTime();
	}
	options.form = postbackData.data;
    }
    else if ( postbackData.method.toUpperCase() == 'GET' ) {
        options.uri = postbackData.url;
	if ( postbackData.stopwatch == 'on' ) {
		options.uri += "&S1="+postbackData.S1+"&R1="+postbackData.R1+"&S2="+postbackData.S2+"&R2="+postbackData.R2+"&S3=" + (new Date()).getTime();
	}
    }
    
    request(options, function(error, response, body) {
        var dateNow = new Date();
        var logString = dateNow.toLocaleString() + '\tReceived\tBody:[' + body + ']\tStatus:[' + response.statusCode + ']' + ( error == null ? "" : '\tError:[' + error + ']' );
        console.log(logString);
    });
    process.nextTick(sendData);
  });
}

