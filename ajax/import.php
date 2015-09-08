<?php
define('_VALID_MOS', 1);

require_once '../../../../configuration.php';
require_once '../../../../includes/ajax.php';
require_once '../../../../includes/STN.class.php';
require_once '../../com_virtuemart/ajax/acl.php';
require_once "../../com_importer/ajax/lib.php";
ajaxACL(__FILE__, array('administrator', 'super administrator'));

$pdo = STN::getPDO();
	
$results = $pdo->query("
	SELECT *
		FROM `stn_matchup_results`
		WHERE `import_status` = 'Y' AND `dup_field` != 'no'
	;
");

$rowCount = $results->rowCount();

include_once "{$mosConfig_absolute_path}/administrator/components/com_virtuemart/virtuemart.cfg.php";

$error = array();
$errorSkus = array();
$alreadyImported = array();

$markImported = $pdo->prepare("
	UPDATE `stn_matchup_results`
		SET `import_status`='AI'
		WHERE `ID`=:id
	;
");

STN::depend('ProductDataProvider');
$processedSkus = array();
try {
	
	belongsToGroup($_SESSION, 'super administrator');
	
	$provider = getProvider('STN');
	
	$categories = $provider->getCategories();
	
	$unImportable = array();
	$importedCount = 0;
	foreach ($results->fetchAll(PDO::FETCH_ASSOC) as $importable) {
		
		 $sku = $importable['stn_sku_match'];
		 
		if (empty($sku)) {
			$error[] = 'No import candidate for item number: ' . $importable['POSItemNumber'] . ' missing SKU.';
			continue;
		}
		if (productExistsLocally($sku, STN::getPDO())) {
			$alreadyImported[] = $sku;
			$importCount += 1;
			continue;
		}
		try {
			$product = $provider->getProductBySTNSKU($sku);
			if (!empty($product)) {
				$product_id = importProduct(
					$product,
					$provider,
					$categories
				);
				$changes = array(
					'sku' => $importable['POSItemNumber'],
					'quantity' => $importable['QOH'],
				);
				$failed = array();
				Product::update($product_id, $changes, $failed);
				if (!empty($failed)) {
					throw new Exception('Saving product data failed.');
				}
				Product::setPrice($product_id, $importable['RegularPrice']);
			} else {
				$errorSkus[$sku] = "Product not found on master database: " . $sku;
				continue;
			}
			
		} catch (Exception $e) {
			$errorSkus[$sku] = $e->getMessage();
			continue;
		}
		$markImported->execute(array(':id' => $importable['ID']));
		$processedSkus[] = $sku;
		$importCount += 1;
	}
} catch (Exception $e) {
	$error[] = $e->getMessage();
}

echo json_encode(array(
	'intended' => $rowCount,
	'processedSkus' => $processedSkus,
	'alreadyImported' => $alreadyImported,
	'errors' => $error,
	'errorSkus' => $errorSkus,
	'importedCount' => $importCount,
));
