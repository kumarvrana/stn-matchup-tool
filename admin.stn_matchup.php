<?php
//die('here');
// error_reporting(-1);
/* Licensed under GPL (see LICENSE.php) */
defined('_VALID_MOS') or die('Restricted access');
ini_set('max_execution_time', 300);
global $mosConfig_live_site;
global $mosConfig_absolute_path;

require_once $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/db_config.php";
require_once $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/localstore_config.php";

// require __DIR__ ."/matchup_results.php";
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	require $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/lib/ajax_functions.php";
}

require_once $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/view/matchup_report.php";

require_once $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/lib/functions.php";

require_once $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/lib/matchup_functions.php";

global $my;
$user_role = $my->usertype;

$task = @$_REQUEST['task'];
if ( $task == 'check_mfg' ) {
	require "$mosConfig_absolute_path/administrator/components/com_stn_matchup/view/matchup_import_mfg.php";
	check_mfgs();
	return;
}
if ( $task == 'import_mfgs' ) {
	require "$mosConfig_absolute_path/administrator/components/com_stn_matchup/view/matchup_import_mfg.php";
	$mfgMap = array();
	$saveList = isset($_REQUEST['localmfg_save']) ? $_REQUEST['localmfg_save'] : array();
	
	foreach($saveList as $remoteId => $save) {
		if (strtoupper($save) === 'ON') {
			$mfgMap[$remoteId] = $_REQUEST['localmfg'][$remoteId];
		}
	}
	import_mfgs( 'stn', $mfgMap );
	return;
}

// reading/matchup started from here
$uploaded_file = @$_SESSION['file_name'];
$match_by = @$_SESSION['match'];
$firstRow = true;

$check = "<input type='checkbox' id='check' name='upload'>";
$check_duplicate_upc = array ();
$check_duplicate_item_number = array ();
$row = 1;
$file_in = "$upload_path/$uploaded_file";

//if(!file_exists ($file_in)){
if (($handle = @fopen("$upload_path/$uploaded_file", "r")) !== FALSE) {
	if (($data = fgetcsv($handle)) !== FALSE) {
		matchup_results_table_columns($data);
	}
	if( $user_role == 'Super Administrator' ) {
		
		while (($data = fgetcsv($handle)) !== FALSE && $row <= 10000) {
			
		if (in_array(@$data[4], $check_duplicate_item_number) || in_array(@$data[2], $check_duplicate_upc)) {
			
			check_duplicate_record_csvdata( $data );
			
			$check_duplicate_item_number[] = strlen($data[4]) > 0 ? $data[4] : uniqid();
			
			$check_duplicate_upc[] = strlen($data[2]) > 0 ? $data[2] : uniqid();
		} else {
			
			$check_duplicate_item_number[] = strlen($data[4]) > 0 ? $data[4] : uniqid();
			
			$check_duplicate_upc[] = strlen($data[2]) > 0 ? $data[2] : uniqid();
			main_iteration( $data );
		}
		$row = $row + 1;
		}
	}else{
		
		while (($data = fgetcsv($handle)) !== FALSE && $row <= 500) {
			if (in_array(@$data[4], $check_duplicate_item_number) || in_array(@$data[2], $check_duplicate_upc)) {
				
				check_duplicate_record_csvdata( $data );
				
				$check_duplicate_item_number[] = strlen($data[4]) > 0 ? $data[4] : uniqid();
				
				$check_duplicate_upc[] = strlen($data[2]) > 0 ? $data[2] : uniqid();
			} else {
				
				$check_duplicate_item_number[] = strlen($data[4]) > 0 ? $data[4] : uniqid();
				
				$check_duplicate_upc[] = strlen($data[2]) > 0 ? $data[2] : uniqid();
				main_iteration( $data );
			}
			// }
			$row = $row + 1;
		}
		
	}
	
	
	fclose($handle);
}
//}

switch( $task ) {
	case 'check_mfg':
		require "$mosConfig_absolute_path/administrator/components/com_stn_matchup/view/matchup_import_mfg.php";
		check_mfgs();
		break;
	case 'import_mfg':
		require "$mosConfig_absolute_path/administrator/components/com_stn_matchup/view/matchup_import_mfg.php";
		$mfgMap = array();
		$saveList = isset($_REQUEST['localmfg_save']) ? $_REQUEST['localmfg_save'] : array();
		foreach($saveList as $remoteId => $save) {
			if (strtoupper($save) === 'ON') {
				$mfgMap[$remoteId] = $_REQUEST['localmfg'][$remoteId];
			}
		}
		import_mfgs( 'stn', $mfgMap );
		break;
	case 'imp_mult_prods':
		echo massImport();
		break;
	default:
		require $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/view/matchup_results_table.php";
		break;
}

if (isset($_GET['download_matchup_results_csv'])) {
	
	require $mosConfig_absolute_path . "/administrator/components/com_stn_matchup/view/matchup_export_csv.php";
	
}
@unlink($file_in);


/*
 * from here our csv reading started function
 * @data (from csv)
*/

function main_iteration( $data ){
	$match_by = $_SESSION['match'];
	$dup_status = '';
	$item = $data[0];
	$item_name = $data[1];
	$item_upc = $data[2];
	$mfg_name = $data[3];
	$item_namedb = htmlspecialchars($data[1], ENT_QUOTES);
	$mfg_namedb = htmlspecialchars($data[3], ENT_QUOTES);
	$pos_item_number = $data[4];
	$price = $data[5];
	@$stock = $data[6];
	$no_match = "NO MATCH";
	$grade = 0;
	$import_status = 'N';
	$dup_field = '';
	$check = "<input type='checkbox' class='check' name='upload'>";
	// get mfg code here
	
	 $mfg_code = get_mfg_code($mfg_name);
	 if($match_by == 'both'){
		match_pos_mfg($data, $mfg_code);
	}elseif ($mfg_code != 'NO MATCH' && $pos_item_number != '' && $match_by == 'sku') {
		match_pos_mfg($data, $mfg_code);
	} elseif ( ( $data[2] != '0' && $match_by == 'upc') && ( $data[2] != '' && $match_by == 'upc') ) {
		// check by UPC, ISBN and EAN
		match_pos_upc($data, $item_upc, $mfg_code);
	} else {
		/* if both UPC and MFG ITEM NUMBER are empty in imported csv */
		if( $data[0] != '' && $data[0] != '0' ) { // condition check empty row in csv
			$mfg_show = get_mfg_name($mfg_code);
			$insert_values = array (
					$item,
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
			insert_results_indb($insert_values, $import_status);
		}
	}
}

/*
 * this function check duplicate data in csv (upc and mfgitem number)
 * @data (from csv)
*/
function check_duplicate_record_csvdata( $data ){
	$grade = 0; // initialise grade
	$match_by = $_SESSION['match'];
	$item_name = htmlspecialchars($data[1], ENT_QUOTES);
	$mfg_name = htmlspecialchars($data[3], ENT_QUOTES);
	$mfg_show = '';
	$dup_status = '';
	$import_status = 'N';
	$dup_field = '';
	$check = "<input type='checkbox' class='check' name='upload'>";
	$duplicate = 'Duplicate Record In CSV';
	$insert_values = array (
			$data[0],
			$item_name,
			$data[2],
			$mfg_name,
			$data[4],
			$data[5],
			@$data[6],
			$mfg_show,
			$duplicate,
			$grade,
			$dup_status,
			$import_status,
			$dup_field
	);
	
	insert_results_indb($insert_values, $import_status);
}

function massImport() {
	global $mosConfig_live_site;
	$importURL = "{$mosConfig_live_site}/administrator/components/com_stn_matchup/ajax/import.php";
	$skuList = array_map('htmlentities', explode(',', $_REQUEST['sku_list']));
	?>
	<pre id="importOutput" style="overflow:auto;max-height:480px;width:100%;text-align:left;"></pre>
	<br/>
	<br/>
	<a class="matchup-import-button" style="padding:10px;float:right;marign:15px" href="<?= $mosConfig_live_site ?>/administrator/index2.php?option=com_stn_matchup">Return To Home</a>
	<script type="text/javascript">
	(function () {
		var importURL = <?= json_encode($importURL) ?>;
		var skus = <?= json_encode($skuList) ?>;
		
		$(window).on(
			'unload',
			function () {
				return "Products import is incomplete, leaving will cancel remaining imports.";
			}
		);
		
		function import50() {
			var step = skus.splice(0, 50);
				if (step.length > 0) {
				$.get(importURL +  '?sku_list=' + step.join(','))
					.done(function (data) {
						$('#importOutput')
							.append(data)
							.scrollTop($('#importOutput')[0].scrollHeight);
						setTimeout(import50, 0);
					});
			} else {
				alert('Import finished');
				$(window).off('unload');
			}
		}
		import50();
		
	}());
	</script>
	<?
}

?>