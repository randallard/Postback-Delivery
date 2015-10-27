<?php

// CONNECT TO REDIS
require "/var/www/postback-agent.com/cgi-bin/predis/autoload.php";
Predis\Autoloader::register();

// since we connect to default setting localhost
// and 6379 port there is no need for extra
// configuration. If not then you can specify the
// scheme, host and port to connect as an array
// to the constructor.
try {
    $redis = new Predis\Client();
/*
    $redis = new Predis\Client(array(
        "scheme" => "tcp",
        "host" => "127.0.0.1",
        "port" => 6379));
*/
}
catch (Exception $e) {
    echo "Couldn't connect to Redis";
    echo $e->getMessage();
}

//$redis->lpush("postbackQueue",$_POST);

/*  EXPECTED POST DATA

var postData = {
          "endpoint":{
            "method":"GET",
            "url":"http://sample_domain_endpoint.com/data?key={key}&value={value}&foo={bar}"
          },
          "data":[
            {
              "key":"Azureus",
              "value":"Dendrobates"
            },
            {
              "key":"Phyllobates",
              "value":"Terribilis"
            }
          ]
        };

*/

#       PROCESSING POST DATA 
#	store "method"
#	store "endpoint"
#	foreach data element
#		send [method]:[endpoint] to redis with {key}/{value}/{bar} replaced

	$postData = json_decode(file_get_contents('php://input'));
	$thisMethod = $postData->{'endpoint'}->{'method'};
	$thisStartUrl = $postData->{'endpoint'}->{'url'};

//	$postData = json_decode(file_get_contents('php://input'), TRUE);
//	$thisMethod = $_POST["endpoint"]["method"];
//	$thisStartUrl = $_POST["endpoint"]["url"];

//	might add ability to take ingest post data as well
	if($thisMethod == "GET"){
			$thisUrl = $thisStartUrl;
			foreach($postData->{'data'} as $dataItem) {
				$thisUrl = str_replace('{'.$dataItem->{'key'}.'}',$dataItem->{'value'},$thisUrl);
			}
			$thisUrl = str_replace('{','',$thisUrl);
			$thisUrl = str_replace('}','',$thisUrl);
			$thisJSON = json_encode( array('method' => $thisMethod, 'url' => $thisUrl ));
			$redis->rpush("postbackQueue",$thisJSON); 
	}
//	else if( $thisMethod == "POST") {
//			$thisUrl = $thisStartUrl;
//			foreach($postData->{'data'} as &$dataItem) {
//					
//			}	
//	}
?>
