<?php
global $mosConfig_live_site;
function output_remote_mfg_options($mfgs, $levenshtein = false, $id = false) {
	
	$outputs = array();
	
	$copies = $mfgs;
	
	if (!empty($levenshtein)) {
		while(!empty($copies)) {
			$key = null;
			$distance = PHP_INT_MAX;
			foreach($copies as $jj => $copy) {
				$lev = levenshtein($copy['mf_name'], $levenshtein);
				if ($lev < $distance) {
					$key = $jj;
					$distance = $lev;
				}
			}
			$outputs[] = $copies[$key];
			unset($copies[$key]);
			$copies = array_values($copies);
		}
	} else {
		$outputs = $mfgs;
	}
	//echo "<select name='maps' id="">";
	$c = 0;
	foreach ($outputs as $mfg) {
		$selected = '';
		if (($mfg['manufacturer_id'] == $id) || ($id === false && $c === 0)) {
			$selected = " selected='selected'";
			$c += 1;
		}
		echo "<option value='{$mfg['manufacturer_id']}'$selected>{$mfg['mf_name']}</option>";
	}
	//echo "</select>";
}

/*
	function get all manufactures from matchup results
	@return array of mfgnames
*/

function get_mfgname_from_matchuptable() {
	global $pdo;
	$query = "SELECT 
				DISTINCT stn_mfg_name 
				FROM `stn_matchup_results` 
				WHERE stn_mfg_name != ''";
	$query = $pdo->prepare( $query );
	$query->execute();
	$result = $query->fetchALL( PDO::FETCH_ASSOC );
	$mfg_names = array();
	foreach( $result as $mfg_name ){
		$mfg_names[] = $mfg_name['stn_mfg_name'];
	}
	return $mfg_names;
}


function check_mfgs() {
	global $pdo, $dbs, $mosConfig_absolute_path;
	
	STN::depend('Site');
	
	$result = $pdo->prepare("
		SELECT *
			FROM `jos_vm_manufacturer`
			LEFT JOIN `jos_vm_stn_mf_map` ON `manufacturer_id`=`client_mf_id`
	");
	
	$result->execute();
	$element = $result->fetchALL( PDO::FETCH_ASSOC );
	$localMfgs = array();
	$localMfgsByCode = array();
	$localMfgsByName = array();
	$mappedMfgs = array();
		
	foreach( $element as $row ) {
		
		$localMfgs[$row['manufacturer_id']] = $row;
		if ( !empty( $row['mf_code'] ) ) {
			
			$localMfgsByCode[$row['mf_code']] = empty( $localMfgsByCode[$row['mf_code']] ) ? array() : $localMfgsByCode[$row['mf_code']];
			$localMfgsByCode[$row['mf_code']][] = $row;
		}
		
		if ( !empty( $row['mf_name'] ) ) {
			$localMfgsByName[$row['mf_name']] = empty( $localMfgsByName[$row['mf_name']] ) ? array() : $localMfgsByName[$row['mf_name']];
			$localMfgsByName[$row['mf_name']][] = $row;
		}
		
		if ( !is_null( $row['stn_mf_id'] ) ) {
			$mappedMfgs[$row['stn_mf_id']] = $row;
		}
		
	}
		
	$pSkuList = get_skus_from_matchup_results();
	
	$mfg_names = get_mfgname_from_matchuptable();
	
	$remoteMfgs = array();
		
	foreach( $mfg_names as $mfg_name ) {
		
		$stmt = $dbs->prepare("
			SELECT `mfg`.*
				FROM `mfg`
				WHERE `mf_name`=:mfg_name
				GROUP BY `mfg`.`mf_ref_table`
		");
		
		$stmt->bindParam( ':mfg_name', $mfg_name, PDO::PARAM_STR );
		$stmt->execute();
		$element = $stmt->fetchALL( PDO::FETCH_ASSOC );
		
		foreach( $element as $row ) {
			
			$remoteMfgs[$row['ID']] = $row;
			
		}
	}
	
	require "$mosConfig_absolute_path/administrator/components/com_stn_matchup/view/matchup_check_mfg.php";
}

function import_mfgs( $type = 'stn', $mfgMap ) {
	global $pdo, $dbs;
	global $mosConfig_absolute_path;
	
	$pSkuList = get_skus_from_matchup_results();
	
	STN::depend('Mfg');
	$results = array();
	$fails = array();
	$unmapped = 0;
	$imported = 0;
	
	foreach($mfgMap as $remoteId => $localId) {
		if (is_numeric($localId)) {
			$sql = "SELECT * FROM `jos_vm_stn_mf_map` WHERE stn_mf_id =:remoteId";
			$check = $pdo->prepare( $sql );
			$check->bindParam( ':remoteId', $remoteId, PDO::PARAM_INT );
			$check->execute();
			$count = $check->rowCount();
			if( $count > 0 ){
				$sql = "DELETE FROM `jos_vm_stn_mf_map` WHERE `jos_vm_stn_mf_map`.`stn_mf_id` = $remoteId";
				$check = $pdo->prepare( $sql );
				$check->execute();
			}
			$result = Mfg::map($remoteId, $localId, $type);
			if ( $result !== false ) {
				$results[] = array(
					'remote' => $remoteId,
					'local' => $localId,
					'result' => $result,
				);
			}
				
		} elseif( $localId === 'NULL' ) {
			
			Mfg::unmap( $remoteId, $type );
			$unmapped += 1;
			
		} elseif( $localId === '{import}' ) {
			//STN::depend('Site');
			
			$sql = "SELECT * FROM `jos_vm_stn_mf_map` WHERE stn_mf_id =:remoteId";
			$check = $pdo->prepare( $sql );
			$check->bindParam( ':remoteId', $remoteId, PDO::PARAM_INT );
			$check->execute();
			$count = $check->rowCount();
			if( $count > 0 ){
				
				$sql = "DELETE FROM `jos_vm_stn_mf_map` WHERE `jos_vm_stn_mf_map`.`stn_mf_id` = $remoteId";
				$check = $pdo->prepare( $sql );
				$check->execute();
				
			}
			$stmt = $dbs->prepare("
				SELECT *
					FROM `mfg`
					WHERE `ID`=:remoteId
			");
			
			
			$stmt->bindParam( ':remoteId', $remoteId, PDO::PARAM_INT );
			$stmt->execute();
			$result = $stmt->fetchAll( PDO::FETCH_ASSOC );
			//$row = array();
			foreach( $result as $row ) {
			//import and map mfgs
				$values = array( 
						$row['mf_name'],
						$row['mf_email'],
						$row['mf_desc'],
						$row['mf_category_id'],
						$row['mf_url'],
						$row['mf_ref_table'],
						$row['mf_type']
						);
				$sql = "
				INSERT INTO `jos_vm_manufacturer`(
						`mf_name`,
						`mf_email`,
						`mf_desc`,
						`mf_category_id`,
						`mf_url`,
						`mf_code`,
						`mf_type`
					)
					VALUES (?,?,?,?,?,?,?)";
			$result = $pdo->prepare( $sql );
			
			if ( $result->execute( $values ) ) {
				
				$result = Mfg::map($remoteId, $pdo->lastInsertId(), $type);
				
				if ( $result !== false ) {
					$results[] = array(
						'remote' => $remoteId,
						'local' => $localId,
						'result' => $result,
					);
				}
				
				$imported += 1;
				
			}else{
				
				trigger_error( "Failed to import Manufacturer (id:{$row['ID']}) with error: " . $results->errorInfo() );
				trigger_error( "\tGenerated SQL: $sql" );
				
			}
				
		}
	}
	}
	require "$mosConfig_absolute_path/administrator/components/com_stn_matchup/view/matchup_mfg_results.php";
	
}


function get_skus_from_matchup_results() {
	
	global $pdo;
	$query = "SELECT 
				`stn_sku_match` 
				FROM `stn_matchup_results` 
				WHERE `import_status` = 'Y'
				AND `dup_field` != 'no'
				";
	$query = $pdo->prepare( $query );
	$query->execute();
	$result = $query->fetchALL( PDO::FETCH_ASSOC );
	$count = $query->rowCount();
	$p_skulist = array();
	if( $count > 0 ){
		
		foreach( $result as $skus ){
			
			$p_skulist[] = $skus['stn_sku_match'];
			
		}
		
	}
	$p_skulist = implode( ',', $p_skulist );
	return $p_skulist;
	
}

?>