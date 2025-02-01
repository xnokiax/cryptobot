<?
$mysql['servername'] = 'localhost';
$mysql['port'] = 3306;
$mysql['username'] = 'root';
$mysql['password'] = 'root';
$mysql['db'] = 'crypto_trade';

try{
	$db = new PDO("mysql:host={$mysql['servername']};port={$mysql['port']};dbname={$mysql['db']}",$mysql['username'],$mysql['password']);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
}catch(PDOException $e){
	echo "Connection failed: " . $e->getMessage();
	die();
}