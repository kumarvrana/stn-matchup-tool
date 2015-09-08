<?php
global $dbs;
//global $mosConfig_db, $mosConfig_host, $mosConfig_user, $mosConfig_password;
try {
	$conf = Site::getConfig('cdndb', 'stn');
	$dbs = new PDO(
		"mysql:host={$conf['db_host']};dbname={$conf['db_mysql_db']}",
		$conf['db_mysql_user'],
		$conf['db_mysql_pwd']
	);
} catch (Exception $e) {
	throw new Exception("Unable to connect to local store database", 0, $e);
}
