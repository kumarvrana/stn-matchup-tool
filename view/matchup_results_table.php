<?php

global $mosConfig_live_site;

?>
<br/>
<!--input type="hidden" id="delEnt">
<input type="hidden" id="dupEnt"-->
<table class="result right" id="view_data_form" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="rs-hover-first" colspan="7" style="background:none; padding: 9.4px 0.4em; border:none;background-color:#2F5497;text-align:center;font-weight:bold "> UPLOADED DATA </th>
			<th class="rs-hover-second" colspan="7" style="background:none;border:none;background-color:#548135;text-align:center; font-weight:bold"> MATCH-UP RESULTS </th>
		</tr>
		<tr style="padding: 9.4px 0.4em;">
			<th id='selectbox'>Select</th>
			<th id='POSItemNumber' class='sort-column csv-upload' data-order="desc">POS Item Number</th>
			<th id='POSItemName' class='sort-column csv-upload' data-order="desc">POS Item Name</th>
			<th id='POSUPC' class='sort-column csv-upload' data-order="desc">POS UPC</th>
			<th id='MfgName' class='sort-column csv-upload' data-order="desc">MFG Name</th>
			<th id='MfgItemNumber' class='sort-column csv-upload' data-order="desc">MFG Item Number</th>
			<!--th id='RegularPrice' class='sort-column csv-upload'>Price</th-->
			<th id='QOH' class='sort-column csv-upload' data-order="desc">QOH</th>
			<th id='stn_sku_match' class='sort-column matchup-results' data-order="desc">STN SKU MATCH</th>
			<th id='stn_item_name' class='sort-column matchup-results' data-order="desc">STN item name</th>
			<th id='stn_mfg_name' class='sort-column matchup-results' data-order="desc">STN mfg name</th>
			<th id='matchup_method' class='sort-column matchup-results' data-order="desc">Matchup Method</th>
			<th id='grade' class='sort-column matchup-results' data-order="desc">Grade</th>
			<th id='plus_1' class='sort-column matchup-results' data-order="desc">1+</th>
			<th style="background-color:grey" id='import_status' class='sort-column' data-order="desc">IMPORT?</th>
		</tr>
		<tr class="rs-remove-bg">
			<form id="matchup-filter">
			<input type="hidden" name="task" value="filterResults" />
			<input type="hidden" name="order" id="order" value="asc"/>
			<input type="hidden" name="order_by" id="order-by" value="POSItemNumber"/>
			<th>
				<input type='checkbox' id='check' name='upload'>
			</th>
			<th>
				<input type='text' id='POSItemNumber' class='typeit' name='POSItemNumber'/>
			</th>
			<th>
				<input type='text' name='POSItemName' id='POSItemName' class='typeit'/>
			</th>
			<th>
				<input type='text' id='POSUPC' class='typeit' name='POSUPC'/>
			</th>
			<th>
				<input type='text' id='MfgName' class='typeit'name='MfgName'/>
			</th>
			<th>
				<input type='text' id='MfgItemNumber' class='typeit' name='MfgItemNumber'/>
			</th>
			<!--th>
				<input type='text' id='RegularPrice' class='typeit' name='RegularPrice'/>
			</th-->
			<th>
				<input type='text' id='QOH' class='typeit' name='QOH'/>
			</th>
			<th>
				<input type='text' id='stn_sku_match' class='typeit' name='stn_sku_match'/>
			</th>
			<th>
				<input name='stn_item_name' id='stn_item_name' class='typeit' type='text'/>
			</th>
			<th>
				<input type='text' id='stn_mfg_name' class='typeit' name='stn_mfg_name'/>
			</th>
			<th>
				<!--input type='text' id='matchup_method' class='typeit' name='matchup_method'/-->
				<select id='matchup_method' class='typeit' name='matchup_method'>
					<option value='' SELECTED>--Select--</option>
					<option value='MATCH by SKU'>MATCH by SKU</option>
					<option value='MATCH by UPC'>MATCH by UPC</option>
					<option value='MATCH by EAN'>MATCH by EAN</option>
					<option value='MATCH by ISBN'>MATCH by ISBN</option>
					<option value='MATCH by UPC & SKU'>MATCH by UPC & SKU</option>
					<option value='NO MATCH'>NO MATCH</option>
					
				</select>
			</th>
			<th>
				<input type='text' id='grade' class='typeit' name='grade'/>
			</th>
			<th>
			</th>
			<th class="gry">
				<select class='typeit gry-b' id='import_status' name='import_status'>
					<option value='' SELECTED>--Select--</option>
					<option value='Y'>Yes</option>
					<option value='N'>No</option>
					<option value='AI'>AI</option>
				</select>
				
			</th>
			</form>
		</tr>
	</thead>
	
	<tbody id='table-results'>
<?php
	global $pdo;
	$duplicate_jsin = array();
	$check = "<input type='checkbox' class='check' name='upload'>";
	sleep(5);
	$query = $pdo->prepare("SELECT * FROM stn_matchup_results WHERE dup_field != 'no' GROUP BY POSItemNumber LIMIT 0, 500");
	$query->execute();
	$elements = $query->fetchAll();
	foreach($elements as $row){
				
	?>
		<tr>
			<td style='width:20px;' data-id="<?php echo $row[0]; ?>"><?php echo $check; ?></td>
			<td class='positemnumber'><?php echo $row[1];?></td>
			<td class='posname'><?php echo $row[2];?></td>
			<td class='posupc'><?php echo $row[3];?></td>
			<td class='mfgname'><?php echo $row[4];?></td>
			<td class='mfgitemnumber'><?php echo $row[5];?></td>
			<!--td class='posprice'><?php //echo $row[6]; ?></td-->
			<td class='StockQty'><?php echo $row[7]; ?></td>
			<td class='stnsku'><?php echo $row[8];?></td>
			<td class='stnitemname'><?php echo $row[9];?></td>
			<td class='stnmfgname'><?php echo $row[10];?></td>
			<td class='matchup'><?php echo $row[11];?></td>
			<td class='grade'><?php echo $row[12];?></td>
			<?php if($row['plus_1'] == NULL || empty($row['plus_1'])){ ?>
			<td class="duplicate-satus"><?php echo $row[13];?></td>
			<?php } else { ?>
			
			<td class="duplicate-satus"><span class='show-duplicates' style="cursor:pointer;" data-id = "<?php echo $row[0]; ?>" data-item="<?php echo $row[1]; ?>"><?php echo $row[13]; ?></span></td>
			<?php } ?>
			<td class = "import">
				<span class="change-status"  data-id = "<?php echo $row[0]; ?>">
				<?php echo $row[14];?>
				</span>
			</td>
		
		</tr>
	<?php
	
	}
	
	
?>
</tbody>
</table>

<br/>
<form action="<?php echo $mosConfig_live_site ?>/administrator/index2.php" method="get">
	<input name="option" value="com_stn_matchup" type="hidden" />
	<input name="download_matchup_results_csv" value="csv" type="hidden" />
	<input type="submit" class="button" value="Export to CSV File" />
</form>
	
	
	<div class='popup' id="popup" style='display:none'>
		<div id="pop-container" class='pop-container'>
		
			<div id="pop-results">
				Loading...
			</div>
			
			<div id="pop-footer" class="text-c">
				<a href="javascript:void(0)" onclick="replaceDuplicate(this)"> Save </a>
				<a href="javascript:void(0)" class="close-btn" onclick="popup('hide')"> Close </a>
				
			</div>
			
		</div>
	</div>
	
	<div id="bg-cover" class='bg-cover' style='display:none'></div>
	
<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/components/com_stn_matchup/js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/components/com_stn_matchup/js/matchup-main.js"></script>
<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/administrator/modules/product-editor/ModalMask.class.js"></script>
<script>
	var site_url_with_comp_matchup = '<?php echo $mosConfig_live_site ?>/administrator/index2.php?option=com_stn_matchup';
</script>

<style type="text/css">
#loading{
	backgroun-color:#fff;
	background-image:url('../images/ajax-loader.gif');
	position:fixed;
	width:220px;
	height:19px;
	margin-top:-10px;
	margin-left:-110px;
	z-index:2;
	top:50%;
	left:50%;
}
#import-results{
	position:fixed;
	background:#fff;
	border-radius:20px;
	box-shdow:inset 1px 1px 1px 1px rgba(0, 0, 0, 0.5);
	width:600px;
	height:400px;
	z-index:3;
	top:50%;
	left:50%;
	margin-top:-200px;
	margin-left:-300px;
	box-sizing:border-box;
	padding:50px;
	font-size:16px;
}
#import-results h3{
	text-decoration:underline;
	text-align:center;
}
#import-results th{
	text-align:right;
	width:50%;
}
#import-results td{
	text-align:left;
	width:50%;
}
#import-results .group{
	position:absolute;
	bottom:20px;
	left:0;
	right:0;
}
#import-results .btn {
	background-color: green;
	border-radius: 10px;
	line-height:2;
	padding:0.5em;
	margin:0 0.5em;
	color:#fff;
	font-weight:bold;
	box-shadow:inset 0 1px 0 1px rgba(255,255,255,0.5);
}
#import-results #import-errors{
	border:1px solid #ccc;
	position:absolute;
	bottom:70px;
	left: 25px;
	right:25px;
	top:150px;
	overflow:auto;
}
#import-results a.btn:hover {
	text-decoration:none;
}
.stn-hide{
	display:none;
}
</style>

<div id="loading" class="stn-hide"></div>
<div id="import-results" class="stn-hide">
	<h3>Import Result Summary</h3>
	<table>
		<tr>
			<th>
				Products Imported:
			</th>
			<td>
				<span class="importcount"></span>
			</td>
		</tr>
		<tr>
			<th>
				Products that already existed:
			</th>
			<td>
				<span class="existcount"></span>
			</td>
		</tr>
		<tr>
			<th>
				Products that couldn't be imported:
			</th>
			<td>
				<span class="unimportedcount"></span>
			</td>
		</tr>
	</table>
	<div id="import-errors">
		No errors reported.
	</div>
	<div class="group">
		<a href="<?= $mosConfig_live_site; ?>/administrator/index2.php?pshop_mode=admin&page=product.product_datatable&option=com_virtuemart"
			class="btn"
		>
			View/Edit Products
		</a>
		<a href="<?= $mosConfig_live_site; ?>/administrator/index2.php?option=com_stn_matchup"
			class="btn"
		>
			Back to Matchup
		</a>
	</div>
</div>


