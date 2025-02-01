<?
require('config/config.php');

//const response = await fetch('https://api.dexscreener.com/latest/dex/search?q=text', {

$url = 'https://api.dexscreener.com/token-profiles/latest/v1';
$data = simpleCurl($url);

foreach($data['body'] as $r){
	if(isset($chains[$r['chainId']])){
		if($chainCount[$r['chainId']] < 30){
			$chainCount[$r['chainId']]++;
			$chains[$r['chainId']] .= ",{$r['tokenAddress']}";
		}
	}else{
		$chainCount[$r['chainId']] = 1;
		$chains[$r['chainId']] = "{$r['tokenAddress']}";
	}
}

foreach($chains as $key => $value){
	$url = "https://api.dexscreener.com/tokens/v1/{$key}/{$value}";
	$details = simpleCurl($url);
	$details = $details['body'];

	if($key == 'solana'){
		$addresses = explode(',',$value);
		// predump($addresses);die;
		$sniffer = simplePost('https://solsniffer.com/api/v2/tokens', ['addresses'=>$addresses]);
		// predump($sniffer);die;
		foreach($sniffer['body'] as $r){
			insertUpdateGeneric('tokens_solscanner','baseToken_address',$r,$r['baseToken_address'],true);
		}
	}

	// echo json_encode($details);die;
	// predump($details);
	// die;
	foreach($details as $r){
		$edit['created_at'] = 'now';
		$edit['chainId'] = $r['chainId'];
		$edit['dexId'] = $r['dexId'];
		$edit['pairAddress'] = $r['pairAddress'];
		$edit['baseToken_address'] = $r['baseToken']['address'];
		$edit['baseToken_name'] = $r['baseToken']['name'];
		$edit['baseToken_symbol'] = $r['baseToken']['symbol'];
		$edit['priceNative'] = $r['priceNative'];
		$edit['priceUsd'] = $r['priceUsd'];
		$edit['txns_m5_buys'] = $r['txns']['m5']['buys'];
		$edit['txns_m5_sells'] = $r['txns']['m5']['sells'];
		$edit['txns_h1_buys'] = $r['txns']['h1']['buys'];
		$edit['txns_h1_sells'] = $r['txns']['h1']['sells'];
		$edit['txns_h6_buys'] = $r['txns']['h6']['buys'];
		$edit['txns_h6_sells'] = $r['txns']['h6']['sells'];
		$edit['txns_h24_buys'] = $r['txns']['h24']['buys'];
		$edit['txns_h24_sells'] = $r['txns']['h24']['sells'];
		$edit['volume_m5'] = $r['volume']['m5'];
		$edit['volume_h1'] = $r['volume']['h1'];
		$edit['volume_h6'] = $r['volume']['h6'];
		$edit['volume_h24'] = $r['volume']['h24'];
		$edit['priceChange_m5'] = @$r['priceChange']['m5'];
		$edit['priceChange_h1'] = @$r['priceChange']['h1'];
		$edit['priceChange_h6'] = @$r['priceChange']['h6'];
		$edit['priceChange_h24'] = @$r['priceChange']['h24'];
		$edit['liquidity_usd'] = @$r['liquidity']['usd'];
		$edit['liquidity_base'] = @$r['liquidity']['base'];
		$edit['liquidity_quote'] = @$r['liquidity']['quote'];
		$edit['fdv'] = @$r['fdv'];
		$edit['marketCap'] = @$r['marketCap'];
		$edit['pairCreatedAt'] = ($r['pairCreatedAt']/1000);
		$edit['pairCreatedAtTime'] = date('Y-m-d H:i:s', $edit['pairCreatedAt']);

		// $seconds_ago = (time() - $edit['pairCreatedAt']);
		insertUpdateGeneric('tokens_dexscreener','id',$edit);
	}
}

die();

function solscan(){

}

function solsniffer(){
	//9i2z54q40yc3hrjn88bykecshmf49r

}


function predump($array){
	echo '<pre>';print_r($array);echo'</pre>';
}
function simpleCurl($url){

	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json; charset=utf-8',
		'Cookie: __cf_bm=N0wh4KhrEuYUdbMzZzpkjYIimGgAxuklyEtoFJ.aO6A-1737942206-1.0.1.1-2RKpxADNt3LrIXo2SMdmv50nvRrMglOJsnHA9mjgsrpI5LrCPyp6j8JHy5QvMRxcbiYT6R767mXLzr6HQ4cfz8JlP5uKEZ_FPj5MQ3fg.wI'
		),
	));

	$result['body'] = curl_exec($ch);
	$result['headers'] = curl_getinfo($ch);

	$result['body'] = json_decode($result['body'],true);

	curl_close($ch);

	return $result;
}

function simplePost($url, $params){

	// echo json_encode($params);die;

	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => json_encode($params),
		CURLOPT_HTTPHEADER => array(
			'accept: application/json',
			'X-API-KEY: 9i2z54q40yc3hrjn88bykecshmf49r',
			'Content-Type: application/json'
		),
	));

	$result['body'] = curl_exec($ch);
	$result['headers'] = curl_getinfo($ch);

	$result['body'] = json_decode($result['body'],true);
	$result['body'] = $result['body']['data'];

	// predump($result['body']);die;
	for($i=0;$i<count($result['body']);$i++){
		$details = json_decode($result['body'][$i]['tokenData']['indicatorData']['high']['details'],true);
		$result['body'][$i]['score'] = $result['body'][$i]['tokenData']['score'];
		$result['body'][$i]['auditRisk_mintDisabled'] = $result['body'][$i]['tokenData']['auditRisk']['mintDisabled'];
		$result['body'][$i]['auditRisk_freezeDisabled'] = $result['body'][$i]['tokenData']['auditRisk']['freezeDisabled'];
		$result['body'][$i]['auditRisk_lpBurned'] = $result['body'][$i]['tokenData']['auditRisk']['lpBurned'];
		$result['body'][$i]['auditRisk_top10Holders'] = $result['body'][$i]['tokenData']['auditRisk']['top10Holders'];
		$result['body'][$i]['baseToken_address'] = $result['body'][$i]['address'];

		$result['body'][$i]['high_count'] = $result['body'][$i]['tokenData']['indicatorData']['high']['count'];
		foreach($details as $key => $value){
			$result['body'][$i]['high_'.str_replace([' ','-'],'_',strtolower($key))] = $value;
		}

		$details = json_decode($result['body'][$i]['tokenData']['indicatorData']['moderate']['details'],true);
		$result['body'][$i]['moderate_count'] = $result['body'][$i]['tokenData']['indicatorData']['moderate']['count'];
		foreach($details as $key => $value){
			$result['body'][$i]['moderate_'.str_replace([' ','-'],'_',strtolower($key))] = $value;
		}

		$details = json_decode($result['body'][$i]['tokenData']['indicatorData']['low']['details'],true);
		$result['body'][$i]['low_count'] = $result['body'][$i]['tokenData']['indicatorData']['low']['count'];
		foreach($details as $key => $value){
			$result['body'][$i]['low_'.str_replace([' ','-'],'_',strtolower($key))] = $value;
		}

		$details = json_decode($result['body'][$i]['tokenData']['indicatorData']['specific']['details'],true);
		$result['body'][$i]['specific_count'] = $result['body'][$i]['tokenData']['indicatorData']['specific']['count'];
		foreach($details as $key => $value){
			$result['body'][$i]['specific_'.str_replace([' ','-'],'_',strtolower($key))] = $value;
		}
	}

	curl_close($ch);

	return $result;

}

function ckClean($string,$removeLines=false){
	if($string == '0')return $string;
	if(is_array($string)){
		echo 'Array given!';predump($string);
	}
	$string = @trim($string);
    $string = cleanOutUTF8($string);
	$string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
	$string = iconv('UTF-8', 'UTF-8//TRANSLIT', $string);
	$string = rtrim(str_replace("'","\'",$string),'\\');
    $string = preg_replace('/\\\\{2,}/', '\\',$string);
    $string = str_replace('\\"', '\\\\"',$string);
    if($removeLines){
    	$string = cleanLines($string);
    }
    //$string = str_replace('\/','/',$string);
    return $string;
}
function cleanLines($string){
	$string = preg_replace("/\r\n|\r/", "<br />", $string);
	$string = trim($string);
	$string = str_replace("\r\n", "\n", $string);
	$string = str_replace("\r", "\n", $string);
	$string = str_replace("\n", "\\n", $string);
	$string = preg_replace('/\s+/S', " ", $string);
	return $string;
}
function cleanOutUTF8($string){
	$search = [                 // www.fileformat.info/info/unicode/<NUM>/ <NUM> = 2018
		"-",     // another em dash attempt
		"\xC2\xAB",     // « (U+00AB) in UTF-8
		"\xC2\xBB",     // » (U+00BB) in UTF-8
		"\xE2\x80\x98", // ‘ (U+2018) in UTF-8
		"\xE2\x80\x99", // ’ (U+2019) in UTF-8
		"\xE2\x80\x9A", // ‚ (U+201A) in UTF-8
		"\xE2\x80\x9B", // ‛ (U+201B) in UTF-8
		"\xE2\x80\x9C", // “ (U+201C) in UTF-8
		"\xE2\x80\x9D", // ” (U+201D) in UTF-8
		"\xE2\x80\x9E", // „ (U+201E) in UTF-8
		"\xE2\x80\x9F", // ‟ (U+201F) in UTF-8
		"\xE2\x80\xB9", // ‹ (U+2039) in UTF-8
		"\xE2\x80\xBA", // › (U+203A) in UTF-8
		"\xE2\x80\x93", // – (U+2013) in UTF-8
		"\xE2\x80\x94", // — (U+2014) in UTF-8
		"\xE2\x80\xA6",  // … (U+2026) in UTF-8
		chr(212),//another crack in case the above miss
		chr(213),
		chr(210),
		chr(211),
		chr(209),
		chr(208),
		chr(201),
		chr(145),
		chr(146),
		chr(147),
		chr(148),
		chr(151),
		chr(150),
		chr(133),
		'â'
	];

	$replacements = [
		"–",
		"<<",
		">>",
		"'",
		"'",
		"'",
		"'",
		'"',
		'"',
		'"',
		'"',
		"<",
		">",
		"-",
		"-",
		"...",
		'&#8216;',
		'&#8217;',
		'&#8220;',
		'&#8221;',
		'&#8211;',
		'&#8212;',
		'&#8230;',
		'&#8216;',
		'&#8217;',
		'&#8220;',
		'&#8221;',
		'&#8211;',
		'&#8212;',
		'&#8230;',
		'-'
	];

    return str_replace($search, $replacements, $string);
}
function withSpace($string){
	return (!empty($string) ? "$string " : '');
}

function errInfo($methodName, $errObj) {
	$errId = time() . '-' . rand(1,100);
	error_log("***{$errId}*** Error Method: [{$methodName}] | Error Message: " . $errObj->getMessage());
	error_log("***{$errId}*** Error Method: [{$methodName}] | Error Trace: " . $errObj->getTraceAsString());
	if(!isProd()){
		echo("<br/>SQL ERROR MESSAGE:::: ***{$errId}*** Error Method: [{$methodName}] | Error Message: " . $errObj->getMessage());
		echo("<br/>SQL ERROR TRACE:::: ***{$errId}*** Error Method: [{$methodName}] | Error Trace: " . $errObj->getTraceAsString());
	}
}

function isProd(){
	return false;
}

function insertUpdate($sql, $params = []){
	global $db;
	$sth = $db->prepare($sql);

	try {
		$sth->execute($params);
		return 1;
	} catch (PDOException $e) {
		errInfo(__FUNCTION__, $e);
		error_log(__FUNCTION__ . " Error: " . $sql);
	}
}

function getRows($sql,$count=false,$limit='',$start=''){
	global $db;
	global $dbType;
	global $pmConnection;

	if(empty($start) && $start !== 0)global $start;
	if(empty($limit))global $limit;

	if($limit == 'alba'){
		$limitSQL = '';
	}else{
		$limitSQL = ($dbType == 'pgsql' ? "limit $limit offset $start" : "limit $start, $limit");
	}

	if($count){
		$countSql = replaceBetween($sql,'^^^','^^^');
		$countSql = replaceBetween($countSql,'^^^','^^^');
		$countSql = replaceBetween($countSql,'^^^','^^^');
		//echo $sql;echo '<br/>';echo '<br/>';echo '<br/>';echo $countSql;die;
		$sqlCount = "select count(*) from ($countSql)z";
		if (!(is_null($pmConnection))) {
			$sth = $pmConnection->prepare($sqlCount);
		} else {
			$sth = $db->prepare($sqlCount);
		}

        try {
            $sth->execute();
			$count = $sth->fetchColumn();
        } catch (PDOException $e) {
            errInfo(__FUNCTION__, $e);
			$count = 0;
        }
		
		$sql = str_replace('^^^','',$sql);
		if (!(is_null($pmConnection))) {
			$sth = $pmConnection->prepare("$sql $limitSQL");
		} else {
			$sth = $db->prepare("$sql $limitSQL");
		}
		//echo "$sql $limitSQL \n\n";
        try {
            $sth->execute();
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            errInfo(__FUNCTION__, $e);
			$results = array();
        }

		$totalReturned = ($limit > $count ? $count : $limit);
		$limit = ($limit == 'alba' ? 10000 : $limit);
		// $end = (($start+$limit) <= ($start+$count) ? $start+$limit : $start+$count);
		$end = (($start+$limit) <= ($count) ? $start+$limit : $count);

		$retVal = array('count'=>(int)$count, 'start'=>(int)$start+1, 'end'=>(int)$end, 'limit'=>(int)$limit, 'totalReturned'=>$totalReturned, 'results'=>$results);

	}else{
		$sql = str_replace('^^^','',$sql);
		$sth = $db->prepare("$sql $limitSQL");
		//echo "$sql $limitSQL \n\n";
        try {
            $sth->execute();
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            errInfo(__FUNCTION__, $e);
			$results = array();
        }

		$retVal = $results;
	}

	return $retVal;
}
function getAll($sql,$params=[]){
	global $db;
	global $pmConnection;

	if (!(is_null($pmConnection))) {
		$sth = $pmConnection->prepare($sql);
	} else {
		$sth = $db->prepare($sql);
	}

	try {
		$sth->execute($params);
		$results = $sth->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		errInfo(__FUNCTION__, $e);
		$results = array();
	}

	$retVal = $results;

	return $retVal;
}

function insertUpdateGeneric($table,$key,$params,$id='',$force=false,$field_flatten=true){
	global $db;
	global $describe;
	global $pmConnection;

	if(!isset($describe[$table])){
		if (!(is_null($pmConnection))) {
			$sth = $pmConnection->prepare("DESCRIBE $table");
		} else {
			$sth = $db->prepare("DESCRIBE $table");
		}

        try {
            $sth->execute();
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            errInfo(__FUNCTION__, $e);
			$results = array();
        }

		$describe[$table] = $results;
	}else{
		$results = $describe[$table];
	}

	//if($force)echo $id."\n";

	if($force){
		$sql = "select 1 from $table where $key = '$id'";
		$exists = getSingle($sql);
		if(!empty($exists)){
			$force = false;//alredy exists just update as normal
		}
	}

	if ($field_flatten) {
		for($i=0;$i<count($results);$i++){
			$results[$i]['Field'] = $results[$i]['Field'];
		}
	}

	if(!empty($id) && !$force){
		$sql = "update $table set ";
		foreach($results as $r){
			if(isset($params[$r['Field']]) && $r['Field'] != $key){
				$params[$r['Field']] = ckClean($params[$r['Field']]);
				if($params[$r['Field']] === 'now'){
					$sql .= "{$r['Field']} = now(),";
				}else if($params[$r['Field']] === 'null'){
					$sql .= "{$r['Field']} = NULL,";
				}else{
					$sql .= "`{$r['Field']}` = '{$params[$r['Field']]}',";
				}
			}
		}
		$sql = rtrim($sql,',');
		$sql .= " where $key = '$id'";
	}else{
		$sql = "insert into $table(";
		foreach($results as $r){
			if(isset($params[$r['Field']]) && ($r['Field'] != $key || $force))
				$sql .= "`{$r['Field']}`,";
		}
		$sql = rtrim($sql,',');
		$sql .= ")values(";
		foreach($results as $r){
			if(isset($params[$r['Field']]) && ($r['Field'] != $key || $force)){
				if($params[$r['Field']] === 'now'){
					$sql .= 'now(),';
				}else if($params[$r['Field']] === 'null'){
					$sql .= 'NULL,';
				}else{
					$params[$r['Field']] = ckClean($params[$r['Field']]);
					$sql .= "'{$params[$r['Field']]}',";
				}
			}
		}
		$sql = rtrim($sql,',') . ')';
		//error_log($sql);
	}
	if (!(is_null($pmConnection))) {
    	$sth = $pmConnection->prepare($sql);
	} else {
		$sth = $db->prepare($sql);
	}

	try {
		$sth->execute();
	} catch (PDOException $e) {
		errInfo(__FUNCTION__, $e);
	}

	//setMessage((!empty($id) ? 'updated' : 'created'),'success');

	// TODO: This isn't safe without transactions. Consider using $db->lastInsertId().

	if(empty($id)){
		$sql = "select max($key) as the_id from $table";
		$id = getSingle($sql);
		$id = $id['the_id'];
	}

	//if(!isset($_SESSION['message']))setMessage('Data updated','success');

	return $id;
}

function getSingle($sql,$item=''){
	$row = getRows($sql,false,1,0);
	$row = (isset($row[0]) ? $row[0] : array());

	return (!empty($item) ? @$row[$item] : $row);
}




/*
solscan
url = "https://pro-api.solscan.io/v2.0/token/holders?address=AuLr9eDm38HMFobZrqSTm9cfdsdeswZJcddd4H36cXdw&page=1&page_size=10"    
headers = {"token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjcmVhdGVkQXQiOjE3MzgyMTk0Njg4MTEsImVtYWlsIjoiZGFuaWVsLmFtYWRpMTAwMUBnbWFpbC5jb20iLCJhY3Rpb24iOiJ0b2tlbi1hcGkiLCJhcGlWZXJzaW9uIjoidjIiLCJpYXQiOjE3MzgyMTk0Njh9.xQbnxSuiqzJPJCggYYxJJpVImE04tvGqc9nFRKMliM4"}


eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjcmVhdGVkQXQiOjE3MzgyMTk0Njg4MTEsImVtYWlsIjoiZGFuaWVsLmFtYWRpMTAwMUBnbWFpbC5jb20iLCJhY3Rpb24iOiJ0b2tlbi1hcGkiLCJhcGlWZXJzaW9uIjoidjIiLCJpYXQiOjE3MzgyMTk0Njh9.xQbnxSuiqzJPJCggYYxJJpVImE04tvGqc9nFRKMliM4
*/

// predump($data);
/*
$i = 0;
foreach($data['body'] as $r){
	//insert into db here..

	$url = "https://api.dexscreener.com/tokens/v1/{$r['chainId']}/{$r['tokenAddress']}";
	$details = simpleCurl($url);
	$details = $details['body'][0];

	predump($details);

	$edit['chainId'] = $details['chainId'];
	$edit['dexId'] = $details['dexId'];
	$edit['pairAddress'] = $details['pairAddress'];
	$edit['baseToken_address'] = $details['baseToken']['address'];
	$edit['baseToken_name'] = $details['baseToken']['name'];
	$edit['baseToken_symbol'] = $details['baseToken']['symbol'];
	$edit['priceNative'] = $details['priceNative'];
	$edit['priceUsd'] = $details['priceUsd'];
	$edit['txns_m5_buys'] = $details['txns']['m5']['buys'];
	$edit['txns_m5_sells'] = $details['txns']['m5']['sells'];
	$edit['txns_h1_buys'] = $details['txns']['h1']['buys'];
	$edit['txns_h1_sells'] = $details['txns']['h1']['sells'];
	$edit['txns_h6_buys'] = $details['txns']['h6']['buys'];
	$edit['txns_h6_sells'] = $details['txns']['h6']['sells'];
	$edit['txns_h24_buys'] = $details['txns']['h24']['buys'];
	$edit['txns_h24_sells'] = $details['txns']['h24']['sells'];
	$edit['volume_m5'] = $details['volume']['m5'];
	$edit['volume_h1'] = $details['volume']['h1'];
	$edit['volume_h6'] = $details['volume']['h6'];
	$edit['volume_h24'] = $details['volume']['h24'];
	$edit['priceChange_m5'] = @$details['priceChange']['m5'];
	$edit['priceChange_h1'] = @$details['priceChange']['h1'];
	$edit['priceChange_h6'] = @$details['priceChange']['h6'];
	$edit['priceChange_h24'] = @$details['priceChange']['h24'];
	$edit['liquidity_usd'] = $details['liquidity']['usd'];
	$edit['liquidity_base'] = $details['liquidity']['base'];
	$edit['liquidity_quote'] = $details['liquidity']['quote'];
	$edit['fdv'] = $details['fdv'];
	$edit['marketCap'] = $details['marketCap'];
	$edit['pairCreatedAt'] = ($details['pairCreatedAt']/1000);
	$edit['pairCreatedAtTime'] = date('Y-m-d H:i:s', $edit['pairCreatedAt']);

	// $seconds_ago = (time() - $edit['pairCreatedAt']);
	insertUpdateGeneric('tokens_dexscreener','id',$edit);

	$i++;
	if($i>10)die;
}
*/