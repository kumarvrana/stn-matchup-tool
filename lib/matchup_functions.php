<?php
// matchup functions
//function for generates stn sku and sku results
function match_pos_mfg($data, $mfg_code){
	
	global $pdo;
	$match_by = $_SESSION['match'];
	$given_sku =  $data[0];
	$pos_item_number = $data[4];
	$item_upc = $data[2];
	$item_name = $data[1];
	$mfg_name = $data[3];
	$pos_item_number = $data[4];
	$pos_item_number = ltrim($pos_item_number, 0);
	$price = $data[5];
	@$stock = $data[6];
	$item_namedb = htmlspecialchars($data[1], ENT_QUOTES);
	$mfg_namedb = htmlspecialchars($data[3], ENT_QUOTES);
	$no_match = "NO MATCH";
	$grade = 0;
	$dup_status = '';
	$import_status = 'N';
	$dup_field = '';
	$stn_sku = stn_sku($mfg_code, $given_sku, $pos_item_number);
	if( $match_by == 'both' ){
		
		match_by_both($data, $stn_sku, $mfg_code);
		
	}elseif( $stn_sku != "No Match"  && $pos_item_number != '' && $match_by == 'sku' ){
		
		match_stn_sku($data, $stn_sku, $mfg_code);
		
	}else{
		$mfg_show = get_mfg_name( $mfg_code );
		$insert_values = array( 
				$given_sku,
				$item_namedb,
				$item_upc,
				$mfg_namedb,
				$pos_item_number,
				$price,
				$stock,
				$mfg_show,
				$no_match,
				$grade,
				$dup_status,
				$import_status,
				$dup_field
		);
		insert_results_indb( $insert_values, $import_status );
	}
}



/* function for getting mfg code form database */

function get_mfg_code( $mfg_name ){
	global $dbs, $pdo;
	$no_match = "NO MATCH";
	$grade = 0;
	// get manufacturer name and mfg code from mfg table by mfg name in imported file
	$query = $dbs->prepare( "SELECT mf_name,
								mf_ref_table FROM mfg
								WHERE mf_name = :mfgname"
						   );
	$query->bindParam(":mfgname", $mfg_name, PDO::PARAM_STR);
	$query->execute();
	$count = $query->rowCount();
	
	if($count > 0){
		$row = $query->fetch();
		$mf_name = $row['mf_name'];  // manufacturer name from mfg table
		$mfg_name_code = $row['mf_ref_table']; // mfg code
		return $mfg_name_code;
	}else{
		$mfg_name = "%{$mfg_name}%";
		$query = $dbs->prepare("SELECT mfg_name, stn_mfg_id FROM `mfg_lookup` 
								WHERE `mfg_name_variations` LIKE :mfgname"
							   );
		$query->bindParam( ":mfgname", $mfg_name, PDO::PARAM_STR );
		$query->execute();
		$count = $query->rowCount();
		if($count > 0) {
			$row = $query->fetch();
			$mf_name = $row['mfg_name'];  // manufacturer name from mfg table
			$mfg_name_code = $row['stn_mfg_id']; // mfg code
			return $mfg_name_code;
		}else{
			return $no_match;
		}
	}
}

/* get manufacture name from product code */
function get_mfg_name( $mfg_code ){
	global $dbs;
	$query = $dbs->prepare( "SELECT mf_name, mf_ref_table FROM mfg 
							WHERE mf_ref_table =:mfgcode"
   						   );
	$query-> bindParam( ":mfgcode", $mfg_code, PDO::PARAM_STR );
	$query->execute();
	$count = $query->rowCount();
	if( $count > 0 ) {
		$row = $query->fetch();
		$mf_name = $row[0];
		$mfg_name_code = $row[1];
		return 	$mf_name;
	}else{
		return '';
	}
}

/*function matchs data from both upc and sku*/
function match_by_both( $data, $stn_sku, $mfg_code ){
	
	global $dbs , $pdo;
	$item = $data[0];
	$item_name = $data[1];
	$item_upc = $data[2];
	$mfg_name = $data[3];
	$pos_item_number = $data[4];
	$pos_item_number = ltrim( $pos_item_number, '0' );
	
	$price = $data[5];
	@$stock = $data[6];
	$item_namedb = htmlspecialchars( $data[1], ENT_QUOTES );
	$mfg_namedb = htmlspecialchars( $data[3], ENT_QUOTES );
	$no_match = 'NO MATCH';
	$grade = 0;
	$import = 'N';
	$dup_status = '';
	$dup_field = '';
	$csvupc = '';
	
	if( $item_upc != '' && $item_upc != '0' && $stn_sku != 'No Match' ) {
		
		$csvupc = check_csvupc_matchby_both( $item_upc );
		
		if( $csvupc != 'no' ) {
			$prod_code = $csvupc;
			$csvupc = strtoupper( substr( $prod_code, 8 ) );
			
			$query = "SELECT
						product_sku, stn_sku, product_name, product_upc,
						product_isbn, product_ean, mfg_id
						FROM stn_products 
						WHERE stn_sku=:stn_sku
						OR $prod_code =:item_upc
					 ";
					 
			$query_both = $dbs->prepare( $query );
			$query_both->bindParam( ":stn_sku", $stn_sku, PDO::PARAM_STR );
			$query_both->bindParam( ":item_upc", $item_upc, PDO::PARAM_STR );
			$query_both->execute();
			$count = $query_both->rowCount();
			
			if( $count >1 ){
				
				$elements = $query_both->fetchALL( PDO::FETCH_ASSOC );
				get_matchup_results_both( $data, $elements, $stn_sku, $csvupc );
				
			} else {
				
				$elements = $query_both->fetch( PDO::FETCH_ASSOC );
				get_matchup_result_both( $data, $elements, $stn_sku, $csvupc );
				
			}
			
		} else {
			
			$csvupc = check_trimmedcsvupc_matchby_both( $item_upc );
			
			if( $csvupc != 'no') {
				$prod_code = $csvupc;
				$item_upc = ltrim( $item_upc, '0' );
				$item_upc = "%{$item_upc}";
				$csvupc = strtoupper( substr( $prod_code, 8 ) );
				
				$query = "SELECT 
							product_sku, stn_sku, product_name,
							product_upc, product_isbn, product_ean, mfg_id
							FROM stn_products 
							WHERE stn_sku=:stn_sku
							OR $prod_code LIKE :item_upc
						 ";
				$query_both = $dbs->prepare( $query );
				$query_both->bindParam( ":stn_sku", $stn_sku, PDO::PARAM_STR );
				$query_both->bindParam( ":item_upc", $item_upc, PDO::PARAM_STR );
				$query_both->execute();
				$count = $query_both->rowCount();
				
				if( $count >1 ){
					$elements = $query_both->fetchALL( PDO::FETCH_ASSOC );
					get_matchup_results_both( $data, $elements, $stn_sku, $csvupc );
				} else {
					$elements = $query_both->fetch( PDO::FETCH_ASSOC );
					get_matchup_result_both( $data, $elements, $stn_sku, $csvupc );
				}
				
			} else {
				
				$matchup = 'sku';
				$query = "SELECT 
							product_sku, stn_sku, product_name,
							product_upc, product_isbn, product_ean, mfg_id
							FROM stn_products 
							WHERE stn_sku=:stn_sku
						";
				$query_both = $dbs->prepare( $query );
				$query_both->bindParam( ":stn_sku", $stn_sku, PDO::PARAM_STR );
				$query_both->execute();
				$count = $query_both->rowCount();
				
				if( $count > 0 ){
					
					if( $count > 1 ){
						
						$elements = $query_both->fetchALL( PDO::FETCH_ASSOC );
						get_matchup_results_both( $data, $elements, $stn_sku, $matchup );
						
					} else {
						
						$elements = $query_both->fetch( PDO::FETCH_ASSOC );
						get_matchup_result_both( $data, $elements, $stn_sku, $matchup );
						
					}
					
				} else {
					
					$mfg_show = get_mfg_name( $mfg_code );
					$insert_values = array( 
							$item,
							$item_namedb,
							$data[2],
							$mfg_namedb,
							$pos_item_number,
							$price,
							$stock,
							$mfg_show,
							$no_match,
							$grade,
							$dup_status,
							$import,
							$dup_field
					);
					
					insert_results_indb( $insert_values, $import );
				}
			}
		}
		
	} else { 
		 if( $stn_sku != 'No Match' ){
			 
				$matchup = 'sku';
				$query = "SELECT product_sku, stn_sku, product_name,
							product_upc, product_isbn, product_ean, mfg_id
							FROM stn_products
							WHERE stn_sku=:stn_sku
						 ";
				$query_both = $dbs->prepare( $query );
				$query_both->bindParam( ":stn_sku", $stn_sku, PDO::PARAM_STR );
				$query_both->execute();
				$count = $query_both->rowCount();
				
				if( $count > 0 ){
					
					if( $count > 1 ){
						
						$elements = $query_both->fetchALL( PDO::FETCH_ASSOC );
						get_matchup_results_both( $data, $elements, $stn_sku, $matchup );
						
					} else {
						
						$elements = $query_both->fetch( PDO::FETCH_ASSOC );
						get_matchup_result_both( $data, $elements, $stn_sku, $matchup );
						
					}
					
				}elseif( $item_upc != '' && $item_upc != '0' ) {
					
					match_pos_upc( $data, $item_upc, $mfg_code );
					
				}else{
					
					$mfg_show = get_mfg_name( $mfg_code );
					$insert_values = array( 
							$item,
							$item_namedb,
							$data[2],
							$mfg_namedb,
							$pos_item_number,
							$price,
							$stock,
							$mfg_show,
							$no_match,
							$grade,
							$dup_status,
							$import,
							$dup_field
					);
					
					insert_results_indb( $insert_values, $import );
			} 
			
		} elseif( $item_upc != '' && $item_upc != '0' ){
			
			match_pos_upc( $data, $item_upc, $mfg_code );
			
		} else {
			
			$mfg_show = get_mfg_name( $mfg_code );
			$insert_values = array( 
					$item,
					$item_namedb,
					$data[2],
					$mfg_namedb,
					$pos_item_number,
					$price,
					$stock,
					$mfg_show,
					$no_match,
					$grade,
					$dup_status,
					$import,
					$dup_field
			);
			insert_results_indb( $insert_values, $import );
		} 
	}
	//} 
}


/*
 * function retrive info from db incase we are matching product with both sku OR upc
 * multiple results
*/
function get_matchup_results_both( $data, $elements, $sku, $matchup ) {
	
	$item_namedb = htmlspecialchars( $data[1], ENT_QUOTES );
	$mfg_namedb = htmlspecialchars( $data[3], ENT_QUOTES );
	$match = 'NO MATCH';
	$i = 1;
	
	foreach( $elements as $row ){
		
		$dup_status = 'V';
		$already_imported = check_stnsku_indb( $row['stn_sku'] );
		$product_sku = $row['product_sku'];
		$stn_sku = $row['stn_sku'];
		$product_name = $row['product_name'];
		$mfg_pname = get_mfg_name( $row['mfg_id'] );
		$product_namedb = htmlspecialchars( $product_name, ENT_QUOTES );
		$mfg_pnamedb = htmlspecialchars( $mfg_pname, ENT_QUOTES );
		
		if( $i == 1 ){
			$dup_field = 'yes';
		} else {
			$dup_field = 'no';
		}
		
		if ( $_SESSION['match'] === 'sku' ) {
			
			$grade = 50;
			$match = 'MATCH by '.$matchup;
			
		} elseif( $_SESSION['match'] === 'upc' ){
			
			$grade = 40;
			$match = 'MATCH by '.$matchup;
			
		} else {
			
			$grade = grade_cal_matchby_both ( $sku, $stn_sku, $data[2], $row['product_upc'], $row['product_isbn'], $row['product_ean'], $matchup );
			
			$match = matchup_status_both( $sku, $stn_sku, $data[2], $row['product_upc'], $row['product_isbn'], $row['product_ean'], $matchup );
			
		}
		
		$grade_pname = grade_product_name( $product_name, $data[1] );
		
		$grade = $grade+$grade_pname;
		
		if( $grade>99 ) $grade = 99;
		
		$import = ( $already_imported == 'yes' ) ? 'AI' : 'Y';
		
		$insert_values = array( 
				$data[0],
				$item_namedb,
				$data[2],
				$mfg_namedb,
				$data[4],
				$data[5],
				@$data[6],
				$stn_sku,
				$product_namedb,
				$mfg_pnamedb,
				$match,
				$grade,
				$dup_status,
				$import,
				$dup_field
		);
		insert_results_indb( $insert_values, $import );
		$i++;
	} 
}

/*
 * Get results when results count is 1
 */
 
function get_matchup_result_both( $data, $elements, $sku, $matchup ){
	
	$item_namedb = htmlspecialchars( $data[1], ENT_QUOTES );
	$mfg_namedb = htmlspecialchars( $data[3], ENT_QUOTES );
	$match = 'NO MATCH';
	$dup_status = '';
	$already_imported = check_stnsku_indb( $elements['stn_sku'] );
	$product_sku = $elements['product_sku'];
	$stn_sku = $elements['stn_sku'];
	$product_name = $elements['product_name'];
	$mfg_pname = get_mfg_name( $elements['mfg_id'] );
	$product_namedb = htmlspecialchars( $product_name, ENT_QUOTES );
	$mfg_pnamedb = htmlspecialchars( $mfg_pname, ENT_QUOTES );
	$dup_field = '';
	
	
	if ( $_SESSION['match'] === 'sku' ) {
		
		$grade = 50;
		$match = 'MATCH by SKU';
		
	} elseif ( $_SESSION['match'] === 'upc' ) {
		
			$grade = 40;
			$match = 'MATCH by '.$matchup;
			
	} else {
		
		$grade = grade_cal_matchby_both ( $sku, $stn_sku, $data[2], $elements['product_upc'], $elements['product_isbn'], $elements['product_ean'], $matchup );
		
		$match = matchup_status_both( $sku, $stn_sku, $data[2], $elements['product_upc'], $elements['product_isbn'], $elements['product_ean'], $matchup );
		
	}
	
	$grade_pname = grade_product_name( $product_name, $data[1] );
	
	$grade = $grade+$grade_pname;
	
	if( $grade>99 ) $grade = 99;
	
	$import = ( $already_imported == 'yes' ) ? 'AI' : 'Y';
	
	$insert_values = array( 
			$data[0],
			$item_namedb,
			$data[2],
			$mfg_namedb,
			$data[4],
			$data[5],
			@$data[6],
			$stn_sku,
			$product_namedb,
			$mfg_pnamedb,
			$match,
			$grade,
			$dup_status,
			$import,
			$dup_field
	);
	
	insert_results_indb( $insert_values, $import );
	
}

/*
 * Grade calculation function when you are doing match with both ( sku or upc ) 
 */
function grade_cal_matchby_both( $sku, $stn_sku, $item_upc, $p_upc, $p_isbn, $p_ean, $matchup ) {
	
	$grade = 0;
	if( $matchup !== 'sku' ) {
		
		$count = check_untrimmed_upc_matchby_both( $item_upc, $matchup );
		
		if( $count <= 0 ){
			
			$item_upc = ltrim($item_upc, '0');
			$p_upc = ltrim( $p_upc, '0' );
			$p_isbn = ltrim( $p_isbn, '0' );
			$p_ean = ltrim( $p_ean, '0' );
			
		}
		
	}
	$stn_sku = strtolower( $stn_sku ); // some time we get stn_sku in capital letters in that case
	
	if( $sku == $stn_sku ){
		
		$grade = 50;
		
	}
	
	if( $matchup == 'UPC' ) {
		
		$grade = 40;
		
	}elseif( $matchup == 'ISBN' ){
		
		$grade = 40;
		
	}elseif( $matchup == 'EAN' ) {
		
		$grade = 40;
		
	}
	
	if( $item_upc === $p_upc && $item_upc != '0' && $item_upc != '' && $matchup != 'sku' ){
		
		$grade = 40;
		
		
	}elseif( $item_upc === $p_upc && $matchup == 'UPC' ){
		
		$grade = 40;
		
	}elseif( $item_upc === $p_isbn && $matchup == 'ISBN' ){
		
		$grade = 40;
		
	}elseif( $item_upc === $p_ean && $matchup == 'EAN' ){
		
		$grade = 40;
		
	}
	
	if( $sku == $stn_sku && $item_upc === $p_upc && $item_upc != '0' && $item_upc != '' && $matchup != 'sku' ){
		
		$grade = 90;
		
	}elseif( $sku == $stn_sku && $item_upc === $p_upc && $matchup == 'UPC' ) {
		
		$grade = 90;
		
	}elseif( $sku == $stn_sku && $item_upc === $p_isbn && $matchup == 'ISBN' ){
		
		$grade = 90;
		
	}elseif( $sku == $stn_sku && $item_upc === $p_ean && $matchup == 'EAN' ){
		
		$grade = 90;
		
	}

	return $grade; 
}

/*
 * functiion to get the matchup status in case both( sku or upc )
 */

function matchup_status_both( $sku, $stn_sku, $item_upc, $p_upc, $p_isbn, $p_ean, $matchup ){
	
	$match = 'NO MATCH';
	
	if( $matchup !== 'sku' ) {
		
		$count = check_untrimmed_upc_matchby_both( $item_upc, $matchup );
		
		if( $count <= 0 ){
			
			$item_upc = ltrim($item_upc, '0');
			$p_upc = ltrim( $p_upc, '0' );
			$p_isbn = ltrim( $p_isbn, '0' );
			$p_ean = ltrim( $p_ean, '0' );
			
		}
		
	}
	
	$stn_sku = strtolower( $stn_sku ); // some time we get stn_sku in capital letters in that case
	if( $sku == $stn_sku ){
		
		$match = 'MATCH by SKU';
		
	}
	if( $matchup == 'UPC' ) {
		
		$match = 'MATCH by UPC';
		
	}elseif( $matchup == 'ISBN' ){
		
		$match = 'MATCH by ISBN';
		
	}elseif( $matchup == 'EAN' ) {
		
		$match = 'MATCH by EAN';
		
	}
	
	if( $item_upc === $p_upc && $item_upc != '0' && $item_upc != '' && $matchup != 'sku' ){
		
		$match = 'MATCH by UPC';
		
	}elseif( $item_upc === $p_upc && $matchup == 'UPC' ){
		
		$match = 'MATCH by UPC';
		
	}elseif( $item_upc === $p_isbn && $matchup == 'ISBN' ){
		
		$match = 'MATCH by ISBN';
		
	}elseif( $item_upc === $p_ean && $matchup == 'EAN' ){
		
		$match = 'MATCH by EAN';
		
	}
	
	if( $sku === $stn_sku && $item_upc === $p_upc && $item_upc != '0' && $item_upc != '' && $matchup != 'sku' ){
		
		$match = 'MATCH by UPC & SKU';
		
	}elseif( $sku === $stn_sku && $item_upc === $p_upc && $matchup == 'UPC' ) {
		
		$match = 'MATCH by UPC & SKU';
		
	}elseif( $sku === $stn_sku && $item_upc === $p_isbn && $matchup == 'ISBN' ){
		
		$match = 'MATCH by ISBN & SKU';
		
	}elseif( $sku === $stn_sku && $item_upc === $p_ean && $matchup == 'EAN' ){
		
		$match = 'MATCH by EAN & SKU';
		
	}
	
	return $match; 
}

/*
 * this function count the results in db regarding products upc, isbn and ean.
 *
 */
function check_untrimmed_upc_matchby_both( $upc, $prod_code ) {
	
	global $dbs, $pdo;
	$sql = "SELECT * FROM stn_products WHERE $prod_code=:item_upc";
	$query = $dbs->prepare( $sql ); 
	$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
	$query->execute();
	$count = $query->rowCount();
	return $count;
	
}

/*
 * this function whether we are getting results upc, isbn and ean.
 *
 */
function check_csvupc_matchby_both( $upc ) {
	
	global $dbs , $pdo;
	$sql = "SELECT * FROM stn_products 
				WHERE product_upc =:item_upc
			";
	$query = $dbs->prepare( $sql ); 
	$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
	$query->execute();
	$count = $query->rowCount();
	
	if( $count > 0 ){
		
		return 'product_upc';
		
	} else {
		$sql = "SELECT * FROM stn_products 
					WHERE product_isbn =:item_upc
				";
		$query = $dbs->prepare( $sql ); 
		$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
		$query->execute();
		$count = $query->rowCount();
		
		if( $count > 0 ) {
			
			return 'product_isbn';
			
		} else {
			
			$sql = "SELECT * FROM stn_products 
						WHERE product_ean =:item_upc
					";
			$query = $dbs->prepare( $sql ); 
			$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
			$query->execute();
			$count = $query->rowCount();
			
			if( $count > 0 ) {
				
				return 'product_ean';
				
			} else {
				
				return 'no';
				
			}
		}
	}
	
}

/*
 * this function tell us that we upc, isbn or ean in matchup from posupc in csv.
 *
 */
function check_trimmedcsvupc_matchby_both( $upc ) {
	
	global $dbs , $pdo;
	$upc = ltrim( $upc, '0' );
	
	if( $upc == '' ){
		return 'no';
	}
	
	$upc = "%{$upc}";
	$sql = "SELECT * FROM stn_products 
				WHERE product_upc LIKE :item_upc
			";
	$query = $dbs->prepare( $sql ); 
	$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
	$query->execute();
	$count = $query->rowCount();
	
	if( $count > 0 ){
		
		return 'product_upc';
		
	} else {
		
		$sql = "SELECT * FROM stn_products 
					WHERE product_isbn LIKE :item_upc
				";
		$query = $dbs->prepare( $sql ); 
		$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
		$query->execute();
		$count = $query->rowCount();
		
		if( $count > 0 ){
			
			return 'product_isbn';
			
		} else {
			
			$sql = "SELECT * FROM stn_products 
						WHERE product_ean LIKE :item_upc
					";
			$query = $dbs->prepare( $sql ); 
			$query->bindParam( ":item_upc", $upc, PDO::PARAM_STR );
			$query->execute();
			$count = $query->rowCount();
			
			if( $count > 0 ) {
				
				return 'product_ean';
				
			} else {
				
				return 'no';
				
			}
		}
	}
	
}
  
/* 
 * sku match up with data base 
 * @passing data from csv, stn_sku (generated from mfg and mfg itemname) and mfg code
*/

function match_stn_sku( $data, $stn_sku, $mfg_code ){
	
	global $dbs , $pdo;
	$item_namedb = htmlspecialchars($data[1], ENT_QUOTES);
	$mfg_namedb = htmlspecialchars($data[3], ENT_QUOTES);
	$no_match = "NO MATCH";
	$grade = 0;
	$import = 'N';
	$dup_status = '';
	$dup_field = '';
	$matchup = 'sku';
	
	$query = "SELECT 
				product_sku, stn_sku, product_name,
				product_upc, product_isbn, product_ean, mfg_id
				FROM stn_products
				WHERE stn_sku=:stn_sku
			";
	$query_sku = $dbs->prepare( $query );
	$query_sku->bindParam( ":stn_sku", $stn_sku, PDO::PARAM_STR );
	$query_sku->execute();
	$count = $query_sku->rowCount();
	
	if( $count > 0 ){
		
		if( $count > 1 ){
			
			$elements = $query_sku->fetchALL( PDO::FETCH_ASSOC );
			get_matchup_results_both( $data, $elements, $stn_sku, $matchup );
			
		} else {
			
			$elements = $query_sku->fetch( PDO::FETCH_ASSOC );
			get_matchup_result_both( $data, $elements, $stn_sku, $matchup );
			
		}
	}else{
		
		$mfg_show = get_mfg_name( $mfg_code );
		$insert_values = array( 
				$data[0],
				$item_namedb,
				$data[2],
				$mfg_namedb,
				$data[4],
				$data[5],
				$data[6],
				$mfg_show,
				$no_match,
				$grade,
				$dup_status,
				$import,
				$dup_field
		);
		insert_results_indb( $insert_values, $import );
	}
}
/* function match with UPC, ISBN and EAN*/
function match_pos_upc( $data, $item_upc, $mfg_code ){
	
	global $dbs, $pdo;
	$csvupc = check_csvupc_matchby_both( $item_upc );
	$stn_sku = stn_sku($mfg_code, $data[0], $data[4]);
	$item_upc = $data[2];
	$item_namedb = htmlspecialchars($data[1], ENT_QUOTES);
	$mfg_namedb = htmlspecialchars($data[3], ENT_QUOTES);
	$no_match = "NO MATCH";
	$dup_field = '';
	$dup_status = '';
	$grade = 0;
	$import = 'N';
	
	if( $csvupc != 'no' ) {
		
		$prod_code = $csvupc;
		$csvupc = strtoupper( substr( $prod_code, 8 ) );
		$query = "SELECT
					product_sku, stn_sku, product_name,
					product_upc, product_isbn, product_ean, mfg_id
					FROM stn_products 
					WHERE $prod_code=:item_upc
				 ";
		$query_both = $dbs->prepare( $query );
		$query_both->bindParam( ":item_upc", $item_upc, PDO::PARAM_STR );
		$query_both->execute();
		$count = $query_both->rowCount();
		
		if( $count > 1 ) {
			
			$elements = $query_both->fetchALL( PDO::FETCH_ASSOC );
			get_matchup_results_both( $data, $elements, $stn_sku, $csvupc );
			
		} else {
			
			$elements = $query_both->fetch( PDO::FETCH_ASSOC );
			get_matchup_result_both( $data, $elements, $stn_sku, $csvupc );
			
		}
		
	} elseif ( $csvupc == 'no' && $item_upc[0] == '0' ) {
		
		$item_upc = ltrim( $item_upc, '0' );
		$csvupc = check_trimmedcsvupc_matchby_both( $item_upc );
		
		if( $csvupc != 'no' ){
			
			$prod_code = $csvupc;
			$csvupc = strtoupper( substr( $prod_code, 8 ) );
			$item_upc = "%{$item_upc}";
			$query = "SELECT 
						product_sku, stn_sku, product_name, product_upc,
						product_isbn, product_ean, mfg_id
						FROM stn_products
						WHERE $prod_code LIKE :item_upc
					 ";
			$query_both = $dbs->prepare( $query );
			$query_both->bindParam( ":item_upc", $item_upc, PDO::PARAM_STR );
			$query_both->execute();
			$count = $query_both->rowCount();
			
			if( $count > 1 ) {
				
				$elements = $query_both->fetchALL( PDO::FETCH_ASSOC );
				get_matchup_results_both( $data, $elements, $stn_sku, $csvupc );
				
			} else {
				
				$elements = $query_both->fetch( PDO::FETCH_ASSOC );
				get_matchup_result_both( $data, $elements, $stn_sku, $csvupc );
				
			}
		} else {
			
			$mfg_show = get_mfg_name( $mfg_code );
			$insert_values = array( 
					$data[0],
					$item_namedb,
					$data[2],
					$mfg_namedb,
					$data[4],
					$data[5],
					$data[6],
					$mfg_show,
					$no_match,
					$grade,
					$dup_status,
					$import,
					$dup_field
			);
			insert_results_indb( $insert_values, $import );	
		}
	} else {
		$mfg_show = get_mfg_name( $mfg_code );
		$insert_values = array( 
				$data[0],
				$item_namedb,
				$data[2],
				$mfg_namedb,
				$data[4],
				$data[5],
				$data[6],
				$mfg_show,
				$no_match,
				$grade,
				$dup_status,
				$import,
				$dup_field
		);
		insert_results_indb( $insert_values, $import );
	}
}
// sku already exsists in local Database
function check_stnsku_indb($stn_sku){
	
	global $pdo;
	$query_AI = $pdo->prepare( "SELECT * FROM jos_vm_product 
									WHERE stn_sku=:stn_sku"
							 );
	$query_AI->bindParam(":stn_sku", $stn_sku, PDO::PARAM_STR);
	$query_AI->execute();
	$count = $query_AI->rowCount();
	if($count > 0){
		
		return 'yes';
		
	}else{
		
		return 'no';
		
	}
	
}


// sku generating function
function stn_sku( $mfg_code, $given_sku, $pos_item_number ){
	
	$stn_sku = "No Match";
	if( $pos_item_number != ''  && $mfg_code != 'NO MATCH' ){
		
		$pos_item_number = preg_replace( "/[^a-zA-Z0-9]/", "", $pos_item_number );
		$pos_item_number = ltrim( $pos_item_number, '0' );
		$stn_sku = $mfg_code.$pos_item_number; // now we have a sku
		$stn_sku = strtolower( $stn_sku ); // sku in lowercase
		return $stn_sku;
		
	}else{
		
		return $stn_sku;
		
	}
}

	
/* function for product name grade
	@product name in db  and csv
	return grade
*/
function grade_product_name( $product_name, $item_name ){
	
	$product_name15 = substr($product_name, 0, 15);
	
	$item_name15 = substr($item_name, 0, 15);
	
	if(levenshtein($product_name, $item_name)  == 0 ) $grade_pname = 25;
	elseif(levenshtein($product_name15, $item_name15) == 0) $grade_pname = 15;
	else $grade_pname = 0;
	
	return $grade_pname;
	
}

/*
	function insert all matchup and csv result in localdb
	return nothing
*/
function insert_results_indb($insert_values, $import){
	
	global $pdo;
	$fields = get_column_names();
	
	foreach( $fields as $field ){
		
		$item_f = $fields[1];
		$itemname_f = $fields[2];
		$upc_f = $fields[3];
		$mfgname_f = $fields[4];
		$mfgitemnumber_f = $fields[5];
		$price_f = $fields[6];
		$qty_f = $fields[7];
		$stn_skumatch_f = $fields[8];
		$stn_itemname_f = $fields[9];
		$stn_mfgname_f = $fields[10];
		$matchup_method_f = $fields[11];
		$grade_f = $fields[12];
		$plus_1_f = $fields[13];
		$import_status_f = $fields[14];
		$dup_sfield_f = $fields[15];
		
	}
	if($import == 'Y' || $import == 'AI'){
		$query = "INSERT INTO 
					stn_matchup_results( 
						$item_f,
						$itemname_f,
						$upc_f,
						$mfgname_f,
						$mfgitemnumber_f,
						$price_f,
						$qty_f,
						$stn_skumatch_f,
						$stn_itemname_f,
						$stn_mfgname_f,
						$matchup_method_f,
						$grade_f,
						$plus_1_f,
						$import_status_f,
						$dup_sfield_f 
				) VALUES( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,? )";
		
		$insert_results = $pdo->prepare( $query );
		$insert_results->execute( $insert_values );
		
	}else{
		$query = "INSERT INTO
					stn_matchup_results(
						$item_f,
						$itemname_f,
						$upc_f,
						$mfgname_f,
						$mfgitemnumber_f,
						$price_f,
						$qty_f,
						$stn_mfgname_f,
						$matchup_method_f,
						$grade_f,
						$plus_1_f,
						$import_status_f,
						$dup_sfield_f
					) VALUES( ?,?,?,?,?,?,?,?,?,?,?,?,? )
				 ";
				 
		$insert_results = $pdo->prepare( $query );
		$insert_results->execute( $insert_values );
		
	}
	
}
?>