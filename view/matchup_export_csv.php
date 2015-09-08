<?php
	$csv_output = "POS Item Number, POS Item Name, POS UPC, MFG Name, MFG Item Number, Stock Qty, STN SKU MATCH, STN item name, STN mfg name, Matchup Method, Grade, 1+, IMPORT?\n";
	$query = $pdo->prepare("SELECT * FROM stn_matchup_results LIMIT 0,500");
	$query->execute();				
	$elements = $query->fetchAll();
	$c = ","; //csv spacer
	foreach($elements as $row){
		$product_name = str_replace(",", " ", $row[2]);
		$mfg_name =	str_replace(",", " ", $row[4]);
		$product_dname = str_replace(",", " ", $row[9]);
		$mfg_dname = str_replace(",", " ", $row[10]);
		$csv_output .= $row[1].$c.$product_name.$c.$row[3].$c.$mfg_name.$c.$row[5].$c.$row[7].$c.$row[8].$c.$product_dname.$c.$mfg_dname.$c.$row[11].$c.$row[12].$c.$row[13].$c.$row[14]."\n";
	}
	
	export_CSV($csv_output);
		
	
	/* functions */
	function export_CSV($string){
		$filename = "CSV_Export_" .date("j-m-Y_H.i"). ".csv";

		if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "Opera";
		}
		elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "IE";
		} else {
			$UserBrowser = '';
		}
		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';

		// dump anything in the buffer
		while( @ob_end_clean() );

		header('Content-Type: ' . $mime_type);
		header('Content-Encoding: none');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		if ($UserBrowser == 'IE') {
			header('Content-Disposition: inline; filename="' . $filename . '"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Pragma: no-cache');
		}
		/*** Now dump the data!! ***/
		echo $string;
		
		// do nothin' more
		exit();
	}
?>