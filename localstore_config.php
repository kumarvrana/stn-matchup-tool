<?php
global $pdo;
global $mosConfig_db, $mosConfig_host, $mosConfig_user, $mosConfig_password;
try {
	$pdo = new PDO("mysql:host=$mosConfig_host;dbname=$mosConfig_db", $mosConfig_user, $mosConfig_password);
} catch (Exception $e) {
	throw new Exception("Unable to connect to local store database", 0, $e);
}
?>