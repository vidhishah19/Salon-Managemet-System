<!-- Begin Main Menu -->
<div class="ewMenu">
<?php $RootMenu = new crMenu(EWR_MENUBAR_ID); ?>
<?php

// Generate all menu items
$RootMenu->IsRoot = TRUE;
$RootMenu->AddMenuItem(14, "mi_salon_details_report", $ReportLanguage->Phrase("SimpleReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("14", "MenuText") . $ReportLanguage->Phrase("SimpleReportMenuItemSuffix"), "salon_details_reportrpt.php", -1, "", TRUE, FALSE);
$RootMenu->AddMenuItem(15, "mi_location_report", $ReportLanguage->Phrase("SimpleReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("15", "MenuText") . $ReportLanguage->Phrase("SimpleReportMenuItemSuffix"), "location_reportrpt.php", -1, "", TRUE, FALSE);
$RootMenu->AddMenuItem(17, "mi_appointment_report", $ReportLanguage->Phrase("SimpleReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("17", "MenuText") . $ReportLanguage->Phrase("SimpleReportMenuItemSuffix"), "appointment_reportrpt.php", -1, "", TRUE, FALSE);
$RootMenu->AddMenuItem(18, "mi_salon_membership_report", $ReportLanguage->Phrase("SimpleReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("18", "MenuText") . $ReportLanguage->Phrase("SimpleReportMenuItemSuffix"), "salon_membership_reportrpt.php", -1, "", TRUE, FALSE);
$RootMenu->AddMenuItem(19, "mi_user_ratings_report", $ReportLanguage->Phrase("SimpleReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("19", "MenuText") . $ReportLanguage->Phrase("SimpleReportMenuItemSuffix"), "user_ratings_reportrpt.php", -1, "", TRUE, FALSE);
$RootMenu->AddMenuItem(20, "mi_user_bookmark_report", $ReportLanguage->Phrase("SimpleReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("20", "MenuText") . $ReportLanguage->Phrase("SimpleReportMenuItemSuffix"), "user_bookmark_reportrpt.php", -1, "", TRUE, FALSE);
$RootMenu->Render();
?>
</div>
<!-- End Main Menu -->
