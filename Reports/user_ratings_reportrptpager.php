<form action="<?php echo ewr_CurrentPage() ?>" name="ewPagerForm" class="ewForm form-horizontal">
<?php if (!isset($Pager)) $Pager = new crNumericPager($Page->StartGrp, $Page->DisplayGrps, $Page->TotalGrps, $Page->GrpRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
<div class="ewPager">
<div class="ewNumericPage"><ul class="pagination">
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<li><a href="<?php echo ewr_CurrentPage() ?>?start=<?php echo $Pager->FirstButton->Start ?>"><?php echo $ReportLanguage->Phrase("PagerFirst") ?></a></li>
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<li><a href="<?php echo ewr_CurrentPage() ?>?start=<?php echo $Pager->PrevButton->Start ?>"><?php echo $ReportLanguage->Phrase("PagerPrevious") ?></a></li>
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<li<?php if (!$PagerItem->Enabled) { echo " class=\" active\""; } ?>><a href="<?php echo ewr_CurrentPage() ?>?start=<?php echo $PagerItem->Start ?>"><?php echo $PagerItem->Text ?></a></li>
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<li><a href="<?php echo ewr_CurrentPage() ?>?start=<?php echo $Pager->NextButton->Start ?>"><?php echo $ReportLanguage->Phrase("PagerNext") ?></a></li>
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<li><a href="<?php echo ewr_CurrentPage() ?>?start=<?php echo $Pager->LastButton->Start ?>"><?php echo $ReportLanguage->Phrase("PagerLast") ?></a></li>
	<?php } ?>
</ul></div>
</div>
<div class="ewPager ewRec">
	<span><?php echo $ReportLanguage->Phrase("Record") ?> <?php echo $Pager->FromIndex ?> <?php echo $ReportLanguage->Phrase("To") ?> <?php echo $Pager->ToIndex ?> <?php echo $ReportLanguage->Phrase("Of") ?> <?php echo $Pager->RecordCount ?></span>
</div>
<?php } ?>
<?php if ($Page->TotalGrps > 0) { ?>
<div class="ewPager">
<input type="hidden" name="t" value="user_ratings_report">
<select name="<?php echo EWR_TABLE_GROUP_PER_PAGE; ?>" class="form-control input-sm" onchange="this.form.submit();">
<option value="1"<?php if ($Page->DisplayGrps == 1) echo " selected" ?>>1</option>
<option value="2"<?php if ($Page->DisplayGrps == 2) echo " selected" ?>>2</option>
<option value="3"<?php if ($Page->DisplayGrps == 3) echo " selected" ?>>3</option>
<option value="4"<?php if ($Page->DisplayGrps == 4) echo " selected" ?>>4</option>
<option value="5"<?php if ($Page->DisplayGrps == 5) echo " selected" ?>>5</option>
<option value="10"<?php if ($Page->DisplayGrps == 10) echo " selected" ?>>10</option>
<option value="20"<?php if ($Page->DisplayGrps == 20) echo " selected" ?>>20</option>
<option value="50"<?php if ($Page->DisplayGrps == 50) echo " selected" ?>>50</option>
<option value="ALL"<?php if ($Page->getGroupPerPage() == -1) echo " selected" ?>><?php echo $ReportLanguage->Phrase("AllRecords") ?></option>
</select>
</div>
<?php } ?>
</form>
