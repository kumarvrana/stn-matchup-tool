<?php 
//all import functions
function insertProduct($product) {
	global $pdo; 
	$product_sku = $product['product_sku'];
	$product_name = $product['product_name'];
	$stn_sku = $product['stn_sku'];
	$mfg_id = $product['mfg_id'];
	//$product_stock = 
	//$product_price =
	//$insert_array = array();
	$insert_values = array($product_sku, $stn_sku, $product_name, $mfg_id);
	$query = "INSERT INTO stn_matchup_results(`POSItemNumber`,`POSItemName`,`POSUPC`,`MfgName`,`MfgItemNumber`,`RegularPrice`,`stn_sku_match`,`stn_item_name`,`stn_mfg_name`,`matchup_method`,`grade`,`plus_1`,`import_status`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$insert_results = $pdo->prepare($query);
	$insert_results->execute($insert_values);
	if (!$query) {
		$this->warn("Failed to insert product '$sku'");
		return array($sku, 'ERRORS');
	}


	//$imported['product_id'] = $this->storeDB->lastInsertID();

	if (empty($imported['product_id'])) {
		$this->warn("Failed to get ID of inserted product for '$sku'");
		return array($sku, 'ERRORS');
	}

	//$this->updateProductXrefs($imported);

	return array($sku, 'ADDED');
}
?>