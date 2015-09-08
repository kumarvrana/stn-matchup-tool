<?php //if (count($remoteMfgs) > 0): ?>
<form method="post" action="index2.php?option=com_stn_matchup&task=import_mfgs">
	<input type="hidden" name="option" value="com_stn_matchup"/>
	<input type="hidden" name="task" value="import_mfgs"/>

<script type="text/javascript">
		$(function () {
			$('.mfg-map').bind('change', function (e) {
				var target = $(e.target),
					num = target.attr('id').replace('localmfg_', ''),
					saveCheck = $('#localmfg_save_' + num);
				saveCheck.prop('checked', true);
			});
			
			
			$('#import-all').bind('click', function (e) {
				$('select.unmapped option').each(function (key, value) {
					var option = $(value);
					
					if (option.attr('value') === '{import}') {
						option.prop('selected', 'selected');
						var num = option.parent().attr('id').replace('localmfg_', '');
						$('#localmfg_save_' + num).prop('checked', true);
						option.attr('selected', 'selected');
					} else {
						option.removeProp('selected').removeAttr('selected');
					}
				});
			});
		});
		</script>

<table class='realtable'>
<thead>
<tr class="mfg-heading">
<th>
	Remote Manufacturer
</th>
<th>
	Status
</th>
<th>
	Maps To <input type="button" id="import-all" value="Import Unmapped Manufacturers" style="margin:0 10px;padding:0 5px;" />
</th>
<th>

</th>
</tr>
</thead>
<?php 
$even = true;
foreach ($remoteMfgs as $remote):
	$class = $even ? ' class="even"' : ' class="odd"';
	$even = !$even;
	
	?>
	<tr<?= $class; ?>>
		<td>
			<?= $remote['mf_name']; ?>
		</td>
		<td>
		
	<? if (array_key_exists($remote['ID'], $mappedMfgs)): ?>
			Previously Imported and/or Mapped
		</td>
		<td>
			<select name="localmfg[<?= $remote['ID'] ?>]" id="localmfg_<?= $remote['ID'] ?>" class='mfg-map'>
				<option value='NULL'>Unmapped</option>
				<option value='{import}'>Import: <?= $remote['mf_name'] ?></option>
				<? 
					
					output_remote_mfg_options($localMfgs, false, $mappedMfgs[$remote['ID']]['manufacturer_id']); ?>
			</select>
		</td>
		<td>
		
			<input type="checkbox" name="localmfg_save[<?= $remote['ID'] ?>]" id="localmfg_save_<?= $remote['ID'] ?>" checked='checked' />
			<label for="localmfg_save_<?= $remote['ID'] ?>">Save?</label>
		</td>
	<? elseif (array_key_exists($remote['mf_ref_table'], $localMfgsByCode)): ?>
			Internal Match Found
		</td>
		<td>
			<select name="localmfg[<?= $remote['ID'] ?>]" id="localmfg_<?= $remote['ID'] ?>" class='mfg-map'>
				<option value='NULL'>Unmapped</option>
				<option value='{import}'>Import: <?= $remote['mf_name'] ?></option>
				<? 
					output_remote_mfg_options(
						$localMfgs,
						false,
						$localMfgsByCode[$remote['mf_ref_table']][0]['manufacturer_id']
					);
				?>
			</select>
		</td>
		<td>
		
			<input type='checkbox' name="localmfg_save[<?= $remote['ID'] ?>]" id="localmfg_save_<?= $remote['ID'] ?>" checked='checked' />
			<label for="localmfg_save_<?= $remote['ID'] ?>">Save?</label>
		</td>
	<? elseif (array_key_exists($remote['mf_name'], $localMfgsByName)): ?>
			Name Match Found
		</td>
		<td>
			<select name="localmfg[<?= $remote['ID'] ?>]" id="localmfg_<?= $remote['ID'] ?>" class='mfg-map'>
				<option value='NULL'>Unmapped</option>
				<option value='{import}'>Import: <?= $remote['mf_name'] ?></option>
				<?
				
					output_remote_mfg_options(
						$localMfgs,
						false,
						$localMfgsByName[$remote['mf_name']][0]['manufacturer_id']
					);
				?>
			</select>
		</td>
		<td>
			<input type='checkbox' name="localmfg_save[<?= $remote['ID'] ?>]" id="localmfg_save_<?= $remote['ID'] ?>" checked='checked' />
			<label for="localmfg_save_<?= $remote['ID'] ?>">Save?</label>
		</td>
	<? else: ?>
			No Match Found
		</td>
		<td>
		
			<select name="localmfg[<?= $remote['ID'] ?>]" id="localmfg_<?= $remote['ID'] ?>" class="mfg-map unmapped">
				<option value='NULL'>Unmapped</option>
				<option value='{import}'>Import: <?= $remote['mf_name'] ?></option>
				<?
					reset($localMfgs);
					$first = current($localMfgs);
					output_remote_mfg_options($localMfgs, $remote['mf_name']);
				?>
			</select>
		</td>
		<td>
			<input type='checkbox' name="localmfg_save[<?= $remote['ID'] ?>]" id="localmfg_save_<?= $remote['ID']; ?>" />
			<label for="localmfg_save_<?= $remote['ID'] ?>">Save?</label>
		</td>
	<? endif; ?>
	</tr>
<?
endforeach;
?>
</table>
<input type="hidden" name="sku_list" value="<?= htmlspecialchars($pSkuList); ?>"/>
<input type="submit" class="matchup-import-button" value="Save Mappings" style="padding:10px;margin:15px;float:right;" />
</form>
<?php //endif; ?>