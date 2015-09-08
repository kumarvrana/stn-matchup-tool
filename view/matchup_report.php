<?php
// no direct access
require_once __DIR__ . '/lib-excel/Classes/PHPExcel/IOFactory.php';

global $mosConfig_live_site;
global $mosConfig_absolute_path;

echo "<link href='$mosConfig_live_site/administrator/components/com_stn_matchup/css/matchup-main.css' type='text/css' rel='stylesheet'/>";

$message = '';
$upload_path =  "$mosConfig_absolute_path/administrator/components/com_stn_matchup/upload";
$allowedExts = array( "csv", "xlsx", "xls", "txt" );

if ( isset($_POST["submit"]) ) {
	
	if ( isset($_FILES["file"])) {

            //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            $message = "<div class='message w'>Please upload file. Return Code: " . $_FILES["file"]["error"] . "<br/></div>";

        }else{
			 //Print file details
			$_SESSION['match'] = $_POST['match'];
			            
			if ( !file_exists( $upload_path ) ) {
				
				mkdir( $upload_path );
				@chmod( $upload_path, 0775 );
				
			}
			
            $filename = end( explode( ".", $_FILES["file"]["name"] ) );
			
			if( in_array( $filename, $allowedExts ) ) {
				
				$_SESSION['file_name'] =  $_FILES["file"]["name"];
				$check_file = move_uploaded_file($_FILES["file"]["tmp_name"], "$upload_path/" . $_FILES["file"]["name"]);
				
				if($check_file == 1){
					
					$message =  "<div class='message r'>File is uploaded ". $_FILES["file"]["name"] . "<br /></div>";
					echo "<script>$(document).ready(function(){ $('.upload-buttn').css('background','#2F5497');
					$('.import-button').css('background','#2F5497');}) ;</script>";
					$extension = substr(strrchr($_FILES["file"]["name"],'.'),1);
					
					if( $extension == 'xlsx' || $extension == 'xls' ){
						
						$csv_name = explode('.',$_FILES["file"]["name"]);
						$csv_name = $csv_name[0];
						$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
						if (PHPExcel_Settings::setCacheStorageMethod($cacheMethod)) {
							
							//$message .= "<div class='message r'>". date('H:i:s') . " Enable Cell Caching using " . $cacheMethod . " method <br/></div>";
						} else {
							
							$message .= "<div class='message r'>". date('H:i:s') . " Unable to set Cell Caching using " . $cacheMethod . " method, reverting to memory <br/></div>";
							
						}
				
						$excel = PHPExcel_IOFactory::load($upload_path."/".$_FILES["file"]["name"]);
				
						$writer = PHPExcel_IOFactory::createWriter($excel, 'CSV');
						$writer->save("$upload_path/$csv_name.csv");
						$_SESSION['file_name'] = "$csv_name.csv";
						$message .= "<div class='message r'>Execl to CSV file is: ".$_SESSION['file_name']."</div>";
					}
				}else{
					
					$message = "<div class='message w'>Enable to upload file. <br /></div>";
					
				}
			} else {
				
				$message = "<div class='message w'>Error: uploading the wrong file. <br /></div>";
				
			}
		}
     } else {
		 
		$message = "<div class='message w'>No file selected <br /></div>";
		
     }
	
}
?>

<table cellspacing="0" cellpadding="0" width="100%" class="adminheading">
    <tr>
        <th class="edit">STN Matchup Tool</th>
    </tr>
</table>
<table class="result rich" width="600" style=" margin-top:40px;">
	<form action="<?php echo $mosConfig_live_site . '/administrator/index2.php?option=com_stn_matchup';?>" method="post" enctype="multipart/form-data" id="form-id">
		<?php
		
		$checked = isset($_SESSION['match']) ? $_SESSION['match'] : 'both';
		
		?>
		<tr><td>Do want to match by:</td><td>
		<input type='radio' name='match' value='sku' <?php echo $checked  == 'sku' ? 'checked' : ''; ?> >SKU &nbsp;&nbsp;
		<input type='radio' name='match' value='upc' <?php echo $checked  == 'upc' ? 'checked' : ''; ?> >UPC &nbsp;&nbsp;
		<input type='radio' name='match' value='both' <?php echo $checked  == 'both' ? 'checked' : ''; ?> >BOTH </td></tr>
		<tr>
			<?php echo $message; ?>
		</tr>
		<tr>
			<td width="20%">Select file</td>
			<td width="80%"><input type="file" name="file" id="file" /><a href="http://help.stoysnet.com/stn-import-tool/stn-matchup-tool.html" style='float:right' target='_blank'>Setup Instructions</a></td>
		</tr>

		<tr>
			<td>Submit</td>
			<td><input type="submit" class="upload-buttn" disabled name="submit" value="Upload File" /></td>
		</tr>
	</form>
		<tr>
			<td>Import</td>
			<td><a class="import-button" href="<?=$mosConfig_live_site?>/administrator/index2.php?option=com_stn_matchup&task=check_mfg">Import Select?</a></td>
		</tr>
</table>
<br/>
<br/>


<script>
$(function() {
	$("#file").change(function (){
       var fileName = $(this).val();
       if( fileName !== '' ){
		 $(".upload-buttn").prop("disabled", false);
		 $(".upload-buttn").addClass('active');
	   }
     });
	$('#form-id').submit(function() {
		$(".upload-buttn").addClass('active');
		$(".import-button").addClass('active');
	});
});
</script>
<style>
.rich .upload-buttn {
    background: #BCC1D6;
    border: 0px;
    padding: 4px;
    color: #fff;
    border-radius: 3px;
}
.active{
    background: #2F5497 !important;
}
</style>
