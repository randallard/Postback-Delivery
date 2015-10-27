var redis    = require('redis').createClient(),
    request  = require('request'),
    fs       = require('fs');

redis.on('error', function (err) {
        var dateNow = new Date();
        console.log(dateNow.toLocaleString() + '\tError\t' + err);
        fs.appendFile('log.txt'
            , dateNow.toLocaleString() + '\tError\t'+ err +'\n'
            , function(err){
                if (err) throw err;
            }
        );

});

sendData();

function sendData(){
  redis.blpop('postbackQueue', 0, function(err, data)  {
    var postbackData = JSON.parse(data[1]);
    var options = {
      uri: postbackData.url,
      method: postbackData.method,
      form: postbackData.data,
      timeout: 10000,
      followRedirect: true,
      maxRedirects: 10
    };
    request(options, function(error, response, body) {
        var dateNow = new Date();
        console.log(dateNow.toLocaleString() + '\tReceived\t' + body + '\t' + response + '\t' + error);
        fs.appendFile('log.txt'
            , dateNow.toLocaleString() + '\tReceived\t' + body + '\t' + response + '\t' + error + '\n'
            , function(err){
                if (err) throw err;
            }
        );
    });
    process.nextTick(sendData);
  });
}

