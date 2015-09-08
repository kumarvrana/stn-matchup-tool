<?php 
// db functions
function matchup_results_table_columns($data){
	global $pdo;
	$length = count($data);
	/* $column_fields = array();
	for($i=0;$i<$length;$i++){
		$column_fields[] = preg_replace("/[^a-z_]/i", "", $data[$i]);
	}
	$product_sku = $column_fields[0];
	$product_upc = $column_fields[2];
	$product_name = $column_fields[1];
	$mfg_name = $column_fields[3];
	$mfg_item_number = $column_fields[4];
	$product_price = $column_fields[5];
	$stock = $column_fields[6]; */
	$product_sku = 'POSItemNumber';
	$product_upc = 'POSUPC';
	$product_name = 'POSItemName';
	$mfg_name = 'MfgName';
	$mfg_item_number = 'MfgItemNumber';
	$product_price = 'RegularPrice';
	$stock = 'QOH';
	$stn_sku_match = 'stn_sku_match';
	$stn_item_name = 'stn_item_name';
	$stn_mfg_name = 'stn_mfg_name';
	$matchup = 'matchup_method';
	$grade = 'grade';
	$dup_status = 'plus_1'; 
	$import = 'import_status';
	$dup_sfield = 'dup_field';
	$m_date = 'matchup_date';
	$query = $pdo->query("DROP TABLE stn_matchup_results");
	$query = $pdo->query("CREATE TABLE IF NOT EXISTS stn_matchup_results(ID int NOT NULL PRIMARY KEY AUTO_INCREMENT, $product_sku varchar(255), $product_name varchar(255), $product_upc varchar(16), $mfg_name varchar(255), $mfg_item_number varchar(255),$product_price float,$stock int,$stn_sku_match varchar(255), $stn_item_name varchar(255), $stn_mfg_name varchar(255), $matchup varchar(255), $grade int, $dup_status varchar(10), $import varchar(10), $dup_sfield varchar(20), $m_date TIMESTAMP NOT NULL DEFAULT NOW()) ENGINE = MyISAM COLLATE utf8_general_ci");
}


function columns_for_csv($data){
	global $pdo;
	$length = count($data);
	$column_fields = array();
	for($i=0;$i<$length;$i++){
		$column_fields[] = preg_replace("/[^a-z_]/i", "", $data[$i]);
	}
	$product_sku = $column_fields[0];
	$product_upc = $column_fields[2];
	$product_name = $column_fields[1];
	$mfg_name = $column_fields[3];
	$mfg_item_number = $column_fields[4];
	$product_price = $column_fields[5];
	$query = $pdo->query("DROP TABLE csv_data");
	
	$query = $pdo->query("CREATE TABLE IF NOT EXISTS csv_data(ID int NOT NULL AUTO_INCREMENT PRIMARY KEY, $product_sku varchar(255), $product_name varchar(255), $product_upc varchar(16), $mfg_name varchar(255), $mfg_item_number varchar(255), $product_price float, csvdate TIMESTAMP NOT NULL DEFAULT NOW()) ENGINE = MyISAM COLLATE utf8_general_ci");
	
}

function get_column_names(){
	global $pdo;
	$query = $pdo->prepare("DESCRIBE stn_matchup_results");
	$query->execute();
	$elements = $query->fetchALL(PDO::FETCH_ASSOC);
	$fields = array();
	foreach($elements as $element){
		$fields[] = $element['Field'];
	}
	return $fields;
}
?>