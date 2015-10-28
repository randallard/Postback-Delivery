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


#       PROCESSING POST DATA 
#	store "method"
#	store "endpoint"
#	foreach data element
#		send [method]:[endpoint] to redis with {key}/{value}/{bar} replaced

	$postData = json_decode(file_get_contents('php://input'));
	$thisMethod = $postData->{'endpoint'}->{'method'};
	$thisStartUrl = $postData->{'endpoint'}->{'url'};

	list($host,$getParameters) = explode('?',$thisStartUrl);
	$keyValuePairs = explode('&',$getParameters);
	$dataItemsTemplate = new ArrayObject();
	
	foreach( $keyValuePairs as &$keyValuePair ) {
		$i = count($dataItemsTemplate);
		list($key,$value) = explode('=',$keyValuePair);
		$value = str_replace('{','',$value);
		$value = str_replace('}','',$value);
		$dataItemsTemplate[$i] = new stdClass();
		$dataItemsTemplate[$i]->key = $key;
		$dataItemsTemplate[$i]->value = $value;		
	}
        foreach ( $dataItemsTemplate as &$templateItem ) {
		if($templateItem->key != $templateItem->value) { echo "<br />Warning: Data Key '$templateItem->key' does not match Data Value Index '$templateItem->value' in URI: $templateItem->key=".'{'.$templateItem->value.'}'; }
        }
//	$postData = json_decode(file_get_contents('php://input'), TRUE);
//	$thisMethod = $_POST["endpoint"]["method"];
//	$thisStartUrl = $_POST["endpoint"]["url"];

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
//	might add ability to take ingest post data as well


	if($thisMethod == "GET"){
			foreach($postData->data as &$dataItem) {
				$thisUrl = $host.'?';
				$printedParameterCount = 0;
				foreach( $dataItemsTemplate as &$dataTemplate ) {
					if(isset($dataItem->{$dataTemplate->value})){
						if($printedParameterCount++ > 0) { $thisUrl .= '&'; }
						$thisUrl .= $dataTemplate->key.'='.$dataItem->{$dataTemplate->value};
					}
					else { echo '<br />Notice: No Data For ' . $dataTemplate->key . '={' . $dataTemplate->value . '}'; }
				}
				echo "<br />Posting Data: $thisUrl";
				$thisJSON = json_encode( array('method' => $thisMethod, 'url' => $thisUrl ));
				$redis->rpush("postbackQueue",$thisJSON); 
			}
	}


//	else if( $thisMethod == "POST") {
//			$thisUrl = $thisStartUrl;
//			foreach($postData->{'data'} as &$dataItem) {
//					
//			}	
//	}
?>
