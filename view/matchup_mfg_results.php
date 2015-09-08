<br>
<br>
<form method="post" action="index2.php?option=com_stn_matchup&task=imp_mult_prods">
	<input type="hidden" name="option" value="com_stn_matchup"/>
	<input type="hidden" name="task" value="imp_mult_prods"/>
	
	<table class="result" id="mfg-info">
		<tr><th class="heading">Manufacturers Import/Mapping</th>
		<tr><td class="matchup-mfg-results"><?= $imported; ?> Manufacturers Imported.</td></tr>
		<tr><td class="matchup-mfg-results"><?= count($results); ?> Manufacturers Mapped.</td></tr>
		<tr><td class="matchup-mfg-results"><?= $unmapped; ?> Manufacturers Unmapped.</td></tr>
	</table>
	
	<input type="hidden" name="sku_list" value="<?= htmlspecialchars($pSkuList); ?>"/>
	<input type="submit" class="matchup-import-button" value="Import Products" style="padding:10px;margin:15px;float:right;" />
</form>