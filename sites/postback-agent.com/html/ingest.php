<?php
	$thisR1 = round(microtime(true) * 1000);

	$postData = json_decode(file_get_contents('php://input'));
	$thisMethod = $postData->{'endpoint'}->{'method'};

	// See if we've implemented a way to process this method
	$implementedMethods = 'GET|';
	if( substr_count( $implementedMethods, strtoupper($thisMethod)) == 0){
		// 501 not implemented
		header("HTTP/1.1 501 [$thisMethod] has not been implemented, please use [$implementedMethods]");
	}

	$thisStartUrl = $postData->{'endpoint'}->{'url'};

	if(isset($postData->{'S1'})) { 
		$thisS1 = $postData->{'S1'};
		$stopwatch = 'on';
	}
	else { 
		$stopwatch = 'off';
	}


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
				$thisPush = array('method' => $thisMethod, 'url' => $thisUrl, 'R1' => $thisR1, 'stopwatch' => $stopwatch, 'S2' => round(microtime(true) * 1000) );
				if ( isset($thisS1) ) {	
					$arrayS1 = array('S1' => $thisS1);
					$thisPush = array_merge($arrayS1,$thisPush); 
				}

				$arrayString = "<br />thisPush:[";	
				foreach ($thisPush as $key => $value) {
					$arrayString .= "$key:[$value];";
				}
				echo $arrayString;
				
				$thisJSON = json_encode( $thisPush );
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
