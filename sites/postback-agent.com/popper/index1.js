'use strict';

var redis = require('redis');
var client = redis.createClient(6379, 'localhost',{no_ready_check: true});
client.on('error', function (err) {
  console.log('error event - ' + client.host + ':' + client.port + ' - ' + err);
});
client.blpop('postbackQueue',0,redis.print);


//getData();

//function getData() {
//  client.blpop('postbackQueue', 0, function(err, data) {
//    postbackData = data[0];
//     postbackData = JSON.parse(data);
//     console.log('got postbackData:'+postbackData);
//    if (postbackData.method == "POST") {
//      request({
//        uri: postbackData.url,
//        method: postbackData.method,
//  	form: postbackData.data,
//        timeout: 10000,
//        followRedirect: true,
//        maxRedirects: 10
//      }, function(error, response, body) {
//        console.log(body);
//      });    
//    }  
//  });
//  process.nextTick(getData);
//}

