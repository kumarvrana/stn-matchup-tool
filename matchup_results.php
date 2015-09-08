<?php

if (isset($_GET['json_match_up_status'])) {
	flush();
	
	ob_start();
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode(array("response" => 200, 'data' => "N"));
	exit;
}

if (isset($_GET['json_match_up_results'])) {

ob_get_clean();
flush();

global $pdo;

$queryids = $pdo->prepare("SELECT DISTINCT POSItemNumber FROM stn_matchup_results");
$queryids->execute();				
$elementsids = $queryids->fetchAll();

$final_results = array();

foreach($elementsids as $item){
		
	$query = $pdo->prepare("SELECT * FROM stn_matchup_results where POSItemNumber = $item[0] LIMIT 0,1");
	$query->execute();				
	$elements = $query->fetch(PDO::FETCH_ASSOC);
	array_push($final_results, $elements);

}

$response['totalRecords'] = count($final_results);
$response['recordsReturned'] = count($final_results);
$response['startIndex'] = 0;
$response['pageSize'] = 1;
$response['records'] = &$final_results;


ob_start();
header("Content-Type: application/json; charset=utf-8");
echo json_encode($response);

exit;

}