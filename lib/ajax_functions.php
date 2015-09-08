<?php
global $pdo; 
// function to change the import status
function task_changeImportStatus(){

	global $pdo;
	$temp = false;	
	$check = intval($_GET['check']);
	$id = $_GET['id'];
	$ids = (count($_GET['multi_ids']) > 1) ? $_GET['multi_ids'] : 1;
	$status = $_GET['status'];
	//if($check === 1){
		//$update = array($status);
		//$query = $pdo->prepare("UPDATE stn_matchup_results SET import_status = ?");
	//}else{
		if(count($ids) == 1){
			$update = array($status, $id);
			$query = $pdo->prepare("UPDATE stn_matchup_results SET import_status = ? WHERE id = ?");
		}else{
			foreach($ids as $id){
				$update = array($status, $id);
				$query1 = $pdo->prepare("UPDATE stn_matchup_results SET import_status = ? WHERE id = ?");
				$query1->execute($update);
			}
			return 2;
		}
	//}
	if($query->execute($update)){
		return 1;
	}
	
	
	return 0;
}


// delete duplicate in database

function task_deleteDuplicates(){

	global $pdo;
	
	$safe = $_GET['safe'];
	$item = $_GET['item'];
	$update = $pdo->prepare("UPDATE stn_matchup_results SET dup_field = ? WHERE ID = ?");
	if(!$update->execute(array('yes', $safe))) return 0;
	$query = $pdo->prepare("UPDATE stn_matchup_results SET dup_field = ? WHERE POSItemNumber = ? and ID != ?" );
	header("Content-Type: application/json");
	
	if($query->execute(array('no', $item, $safe))) return 1;				

	return 0; 
	//return 1;
	/* $update = $pdo->prepare("UPDATE stn_matchup_results SET plus_1 = ? WHERE ID = ?");
	if(!$update->execute(array('', $safe))) return 0;
	
	$query = $pdo->prepare("DELETE FROM stn_matchup_results where POSItemNumber = ? and ID != ?");
	
	header("Content-Type: application/json");
	
	if($query->execute(array($item, $safe))) return 1;				

	return 0; */
	
	
}


// load duplicates from database

function task_loadDuplicates() {

	global $pdo;

	$posno = $_GET['posno'];
	$id = $_GET['safe'];

	$query = $pdo->prepare("SELECT * FROM stn_matchup_results where POSItemNumber = ?");
	$query->execute(array($posno));				
	$elements = $query->fetchAll();
	if (empty($elements )) return "";
	?>
	<table class="result  duplicate_results">
		<tr><h4>Multiple matches found for the following item.</h4></tr>
		<tr>
			<table class="results csv-fields " style="border:1px solid;">
				<tr style="background-color:#BBC2E6;text-align:center;font-weight:bold">
					<th>Item#</th>
					<th>Item Name</th>
					<th>UPC</th>
					<th>MFG</th>
					<th>MFG Item#</th>
					<!--th>Price</th-->
					<th>QOH</th>
				</tr>
				<tr>
					<td class='positemnumber'><?php echo $elements[0][1]; ?></td>
					<td class='posname'><?php echo $elements[0][2]; ?></td>
					<td class='posupc'><?php echo $elements[0][3];  ?></td>
					<td class='mfgname'><?php echo $elements[0][4]; ?></td>
					<td class='mfgitemnumber'><?php echo $elements[0][5]; ?></td>
					<!--td class='posprice'><?php //echo $elements[0][6]; ?></td-->
					<td class='StockQty'><?php echo $elements[0][7]; ?></td>
				</tr>
			</table>
		</tr>
		<br/>
		<br/>
		<tr><strong>Select the item to use.</strong></tr>
		<tr>
			<table class="results dupli-results" style="border:1px solid;width: 100%;">
				<thead>
					<tr style="background-color:#C8E0C2;text-align:center; font-weight:bold">
						<th>Select</th>
						<th>STN SKU</th>
						<th>Item Name</th>
						<th>MFG</th>
						<th>Match Method</th>
						<th>Grade</th>
					</tr>
				</thead>
				<tbody class="dup-results">
				<?php 
					//$check = "<input type='checkbox' class='check' name='upload'>";
					foreach($elements as $row) {
				?>
				<tr data-row="<?php echo $row['ID'] ?>" class="setdis<?php echo $row['ID'] ?> <?php echo $id == $row['ID'] ? 'highlight-row' : ''; ?>" onclick="selectDuplicate(this)">
					<td style='width:20px;'><input type='checkbox' class='check' name='upload' <?php echo $id == $row['ID'] ? 'checked' : ''; ?>></td>
					<td style="display:none;" class='positemnumber'><?php echo $row[1]; ?></td>
					<td style="display:none;" class='posname'><?php echo $row[2]; ?></td>
					<td style="display:none;" class='posupc'><?php echo $row[3];  ?></td>
					<td style="display:none;" class='mfgname'><?php echo $row[4]; ?></td>
					<td style="display:none;" class='mfgitemnumber'><?php echo $row[5]; ?></td>
					<!--td class='posprice'><?php //echo $row[6]; ?></td-->
					<td style="display:none;" class='StockQty'><?php echo $row[7]; ?></td>
					<td class='stnsku'><?php echo $row[8]; ?></td>
					<td class='stnitemname'><?php echo $row[9]; ?></td>
					<td class='stnmfgname'><?php echo $row[10]; ?></td>
					<td class='matchup'><?php echo $row[11];  ?></td>
					<td class='grade'><?php echo $row[12]; ?></td>
					<td style="display:none;" class="duplicate-satus"><span class='show-duplicates' style="cursor:pointer;" data-id = "<?php echo $row['ID']; ?>" data-item="<?php echo $row['POSItemNumber']; ?>"><?php echo $row['plus_1']; ?></span></td>
					<td style="display:none;"><?php echo $row[14];  ?></td>
				</tr>
					<?php 
						}
					?>
				</tbody>
			</table>
		</tr>
		
	</table>
<?php
	
}

// perform sorting

function task_sortResults() {
	
	global $pdo;
	
	$field = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'ID';
	$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
	$query = "SELECT * from stn_matchup_results WHERE dup_field != 'no' GROUP BY POSItemNumber ORDER BY $field $order limit 0, 500";
	
	$stmt = $pdo->prepare($query);
 
	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($results)) {
		
		foreach($results as $row) {
			__tableRow($row);
		}
		
	} else {
		echo "<tr><td colspan='13'>No results to display!</td></tr>";
	}
	
	$content = ob_get_contents();
	ob_get_clean();
	return $content;

}


// filter results  by user search

function task_filterResults() {
	
	global $pdo;
	
	$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'ID';
	
	$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
	
	$params = array_filter($_GET);
	
	if( in_array( @$params['upload'], $params ) ) {
		unset( $params['upload'] );
	} 
	
	unset($params['task'], $params['option'], $params['order_by'], $params['order']);
	
	$str = '';
	
	foreach($params as $k => $v)
	{ 
		
		if($k == 'grade' || $k == 'QOH')
		{
			$grade_a = preg_replace('/[^0-9]/', '', $v);
			$garde = preg_replace('/[0-9]+/', '', $v); // comparion operator
			if(!empty($garde))
			{
				$str .= 'AND '.$k.' '.$garde.' '.$grade_a.' '; 
			}
			else 
			{
				$str .= 'AND '.$k.' LIKE "%'.$v.'%" ';
			}
			
		}
		else 
		{
			$str .= 'AND '.$k.' LIKE "%'.$v.'%" ';
		}
		
	}

	$str = substr($str,3);
	if($str == false) {
		$str = '';
	}
	
	if(!empty($str))
	{
		$query = "SELECT * from stn_matchup_results WHERE ".$str." AND dup_field != 'no' GROUP BY POSItemNumber ORDER BY ".$order_by." ".$order." limit 0, 500";
	}
	else
	{
		$query = "SELECT * from stn_matchup_results WHERE dup_field != 'no' GROUP BY POSItemNumber ORDER BY ".$order_by." ".$order." limit 0, 500";
	
	}

	$stmt = $pdo->prepare($query);

	$stmt->execute();

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if (!empty($results)) {
		
		foreach($results as $row) {
			__tableRow($row);
		}
		
	} else {
		echo "<tr><td colspan='14'>No results to display!</td></tr>";
	}
	
	$content = ob_get_contents();
	ob_get_clean();
	return $content;
}
// private function to draw table rows

function __tableRow($row, $check = "<input type='checkbox' class='check' name='upload'>") {
?>
	 <tr>
		<td style='width:20px;' data-id="<?php echo $row['ID']; ?>"><?php echo $check; ?></td>
		<td class='positemnumber'><?php echo $row['POSItemNumber'];?></td>
		<td class='posname'><?php echo $row['POSItemName'];?></td>
		<td class='posupc'><?php echo $row['POSUPC'];?></td>
		<td class='mfgname'><?php echo $row['MfgName'];?></td>
		<td class='mfgitemnumber'><?php echo $row['MfgItemNumber'];?></td>
		<!--td class='posprice'><?php //echo $row['RegularPrice']; ?></td-->
		<td class='StockQty'><?php echo $row['QOH']; ?></td>
		<td class='stnsku'><?php echo $row['stn_sku_match'];?></td>
		<td class='stnitemname'><?php echo $row['stn_item_name'];?></td>
		<td class='stnmfgname'><?php echo $row['stn_mfg_name'];?></td>
		<td class='matchup'><?php echo $row['matchup_method'];?></td>
		<td class='grade'><?php echo $row['grade'];?></td>		
		<?php if($row['plus_1'] == NULL || empty($row['plus_1'])){ ?>
		<td class="duplicate-satus"><?php echo $row['plus_1'];?></td>
		<?php } else { ?>		
		<td class="duplicate-satus"><span class='show-duplicates' style="cursor:pointer;" data-id = "<?php echo $row['ID']; ?>" data-item="<?php echo $row['POSItemNumber']; ?>"><?php echo $row['plus_1']; ?></span></td>
		<?php } ?>
		<td class="import"><span class="change-status" data-id="<?php echo $row['ID']; ?>"><?php echo $row['import_status'];?></span></td>
	</tr>
<?php
}

// fire some task based on user event

if (isset($_GET['task']) && !empty($_GET['task'])) {

	if (is_callable("task_" . $_GET['task'])) {
		flush();
		ob_start();
		echo call_user_func("task_" . $_GET['task']);
		exit;
	}

}