<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start();
?>
<?php include_once "phprptinc/ewrcfg9.php" ?>
<?php include_once ((EW_USE_ADODB) ? "adodb5/adodb.inc.php" : "phprptinc/ewmysql.php") ?>
<?php include_once "phprptinc/ewrfn9.php" ?>
<?php include_once "phprptinc/ewrusrfn9.php" ?>
<?php include_once "appointment_reportrptinfo.php" ?>
<?php

//
// Page class
//

$appointment_report_rpt = NULL; // Initialize page object first

class crappointment_report_rpt extends crappointment_report {

	// Page ID
	var $PageID = 'rpt';

	// Project ID
	var $ProjectID = "{3C488A29-896C-4371-BB76-02105C3C2105}";

	// Page object name
	var $PageObjName = 'appointment_report_rpt';

	// Page name
	function PageName() {
		return ewr_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ewr_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Export URLs
	var $ExportPrintUrl;
	var $ExportExcelUrl;
	var $ExportWordUrl;
	var $ExportPdfUrl;
	var $ReportTableClass;
	var $ReportTableStyle = "";

	// Custom export
	var $ExportPrintCustom = FALSE;
	var $ExportExcelCustom = FALSE;
	var $ExportWordCustom = FALSE;
	var $ExportPdfCustom = FALSE;
	var $ExportEmailCustom = FALSE;

	// Message
	function getMessage() {
		return @$_SESSION[EWR_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EWR_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EWR_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EWR_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_WARNING_MESSAGE], $v);
	}

		// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-info ewInfo\">" . $sMessage . "</div>";
			$_SESSION[EWR_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EWR_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EWR_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-danger ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EWR_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<div class=\"ewMessageDialog ewDisplayTable\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") // Header exists, display
			echo $sHeader;
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") // Fotoer exists, display
			echo $sFooter;
	}

	// Validate page request
	function IsPageRequest() {
		if ($this->UseTokenInUrl) {
			if (ewr_IsHttpPost())
				return ($this->TableVar == @$_POST("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == @$_GET["t"]);
		} else {
			return TRUE;
		}
	}
	var $Token = "";
	var $CheckToken = EWR_CHECK_TOKEN;
	var $CheckTokenFn = "ewr_CheckToken";
	var $CreateTokenFn = "ewr_CreateToken";

	// Valid Post
	function ValidPost() {
		if (!$this->CheckToken || !ewr_IsHttpPost())
			return TRUE;
		if (!isset($_POST[EWR_TOKEN_NAME]))
			return FALSE;
		$fn = $this->CheckTokenFn;
		if (is_callable($fn))
			return $fn($_POST[EWR_TOKEN_NAME]);
		return FALSE;
	}

	// Create Token
	function CreateToken() {
		global $gsToken;
		if ($this->CheckToken) {
			$fn = $this->CreateTokenFn;
			if ($this->Token == "" && is_callable($fn)) // Create token
				$this->Token = $fn();
			$gsToken = $this->Token; // Save to global variable
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $ReportLanguage;

		// Language object
		$ReportLanguage = new crLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (appointment_report)
		if (!isset($GLOBALS["appointment_report"])) {
			$GLOBALS["appointment_report"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["appointment_report"];
		}

		// Initialize URLs
		$this->ExportPrintUrl = $this->PageUrl() . "export=print";
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel";
		$this->ExportWordUrl = $this->PageUrl() . "export=word";
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf";

		// Page ID
		if (!defined("EWR_PAGE_ID"))
			define("EWR_PAGE_ID", 'rpt', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EWR_TABLE_NAME"))
			define("EWR_TABLE_NAME", 'appointment report', TRUE);

		// Start timer
		$GLOBALS["gsTimer"] = new crTimer();

		// Open connection
		if (!isset($conn)) $conn = ewr_Connect($this->DBID);

		// Export options
		$this->ExportOptions = new crListOptions();
		$this->ExportOptions->Tag = "div";
		$this->ExportOptions->TagClassName = "ewExportOption";

		// Search options
		$this->SearchOptions = new crListOptions();
		$this->SearchOptions->Tag = "div";
		$this->SearchOptions->TagClassName = "ewSearchOption";

		// Filter options
		$this->FilterOptions = new crListOptions();
		$this->FilterOptions->Tag = "div";
		$this->FilterOptions->TagClassName = "ewFilterOption fappointment_reportrpt";
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $gsEmailContentType, $ReportLanguage, $Security;
		global $gsCustomExport;

		// Get export parameters
		if (@$_GET["export"] <> "")
			$this->Export = strtolower($_GET["export"]);
		elseif (@$_POST["export"] <> "")
			$this->Export = strtolower($_POST["export"]);
		$gsExport = $this->Export; // Get export parameter, used in header
		$gsExportFile = $this->TableVar; // Get export file, used in header
		$gsEmailContentType = @$_POST["contenttype"]; // Get email content type

		// Setup placeholder
		// Setup export options

		$this->SetupExportOptions();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();

		// Check token
		if (!$this->ValidPost()) {
			echo $ReportLanguage->Phrase("InvalidPostRequest");
			$this->Page_Terminate();
			exit();
		}

		// Create Token
		$this->CreateToken();
	}

	// Set up export options
	function SetupExportOptions() {
		global $ReportLanguage;
		$exportid = session_id();

		// Printer friendly
		$item = &$this->ExportOptions->Add("print");
		$item->Body = "<a title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("PrinterFriendly", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("PrinterFriendly", TRUE)) . "\" href=\"" . $this->ExportPrintUrl . "\">" . $ReportLanguage->Phrase("PrinterFriendly") . "</a>";
		$item->Visible = FALSE;

		// Export to Excel
		$item = &$this->ExportOptions->Add("excel");
		$item->Body = "<a title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToExcel", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToExcel", TRUE)) . "\" href=\"" . $this->ExportExcelUrl . "\">" . $ReportLanguage->Phrase("ExportToExcel") . "</a>";
		$item->Visible = TRUE;

		// Export to Word
		$item = &$this->ExportOptions->Add("word");
		$item->Body = "<a title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToWord", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToWord", TRUE)) . "\" href=\"" . $this->ExportWordUrl . "\">" . $ReportLanguage->Phrase("ExportToWord") . "</a>";

		//$item->Visible = FALSE;
		$item->Visible = FALSE;

		// Export to Pdf
		$item = &$this->ExportOptions->Add("pdf");
		$item->Body = "<a title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToPDF", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToPDF", TRUE)) . "\" href=\"" . $this->ExportPdfUrl . "\">" . $ReportLanguage->Phrase("ExportToPDF") . "</a>";
		$item->Visible = FALSE;

		// Uncomment codes below to show export to Pdf link
//		$item->Visible = TRUE;
		// Export to Email

		$item = &$this->ExportOptions->Add("email");
		$url = $this->PageUrl() . "export=email";
		$item->Body = "<a title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToEmail", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToEmail", TRUE)) . "\" id=\"emf_appointment_report\" href=\"javascript:void(0);\" onclick=\"ewr_EmailDialogShow({lnk:'emf_appointment_report',hdr:ewLanguage.Phrase('ExportToEmail'),url:'$url',exportid:'$exportid',el:this});\">" . $ReportLanguage->Phrase("ExportToEmail") . "</a>";
		$item->Visible = FALSE;

		// Drop down button for export
		$this->ExportOptions->UseDropDownButton = FALSE;
		$this->ExportOptions->UseButtonGroup = TRUE;
		$this->ExportOptions->UseImageAndText = $this->ExportOptions->UseDropDownButton;
		$this->ExportOptions->DropDownButtonPhrase = $ReportLanguage->Phrase("ButtonExport");

		// Add group option item
		$item = &$this->ExportOptions->Add($this->ExportOptions->GroupOptionName);
		$item->Body = "";
		$item->Visible = FALSE;

		// Filter panel button
		$item = &$this->SearchOptions->Add("searchtoggle");
		$SearchToggleClass = " active";
		$item->Body = "<button type=\"button\" class=\"btn btn-default ewSearchToggle" . $SearchToggleClass . "\" title=\"" . $ReportLanguage->Phrase("SearchBtn", TRUE) . "\" data-caption=\"" . $ReportLanguage->Phrase("SearchBtn", TRUE) . "\" data-toggle=\"button\" data-form=\"fappointment_reportrpt\">" . $ReportLanguage->Phrase("SearchBtn") . "</button>";
		$item->Visible = TRUE;

		// Reset filter
		$item = &$this->SearchOptions->Add("resetfilter");
		$item->Body = "<button type=\"button\" class=\"btn btn-default\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ResetAllFilter", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ResetAllFilter", TRUE)) . "\" onclick=\"location='" . ewr_CurrentPage() . "?cmd=reset'\">" . $ReportLanguage->Phrase("ResetAllFilter") . "</button>";
		$item->Visible = TRUE;

		// Button group for reset filter
		$this->SearchOptions->UseButtonGroup = TRUE;

		// Add group option item
		$item = &$this->SearchOptions->Add($this->SearchOptions->GroupOptionName);
		$item->Body = "";
		$item->Visible = FALSE;

		// Filter button
		$item = &$this->FilterOptions->Add("savecurrentfilter");
		$item->Body = "<a class=\"ewSaveFilter\" data-form=\"fappointment_reportrpt\" href=\"#\">" . $ReportLanguage->Phrase("SaveCurrentFilter") . "</a>";
		$item->Visible = TRUE;
		$item = &$this->FilterOptions->Add("deletefilter");
		$item->Body = "<a class=\"ewDeleteFilter\" data-form=\"fappointment_reportrpt\" href=\"#\">" . $ReportLanguage->Phrase("DeleteFilter") . "</a>";
		$item->Visible = TRUE;
		$this->FilterOptions->UseDropDownButton = TRUE;
		$this->FilterOptions->UseButtonGroup = !$this->FilterOptions->UseDropDownButton; // v8
		$this->FilterOptions->DropDownButtonPhrase = $ReportLanguage->Phrase("Filters");

		// Add group option item
		$item = &$this->FilterOptions->Add($this->FilterOptions->GroupOptionName);
		$item->Body = "";
		$item->Visible = FALSE;

		// Set up options (extended)
		$this->SetupExportOptionsExt();

		// Hide options for export
		if ($this->Export <> "") {
			$this->ExportOptions->HideAllOptions();
			$this->SearchOptions->HideAllOptions();
			$this->FilterOptions->HideAllOptions();
		}

		// Set up table class
		if ($this->Export == "word" || $this->Export == "excel" || $this->Export == "pdf")
			$this->ReportTableClass = "ewTable";
		else
			$this->ReportTableClass = "table ewTable";
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $ReportLanguage, $EWR_EXPORT, $gsExportFile;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();

		// Export
		if ($this->Export <> "" && array_key_exists($this->Export, $EWR_EXPORT)) {
			$sContent = ob_get_contents();

			// Remove all <div data-tagid="..." id="orig..." class="hide">...</div> (for customviewtag export, except "googlemaps")
			if (preg_match_all('/<div\s+data-tagid=[\'"]([\s\S]*?)[\'"]\s+id=[\'"]orig([\s\S]*?)[\'"]\s+class\s*=\s*[\'"]hide[\'"]>([\s\S]*?)<\/div\s*>/i', $sContent, $divmatches, PREG_SET_ORDER)) {
				foreach ($divmatches as $divmatch) {
					if ($divmatch[1] <> "googlemaps")
						$sContent = str_replace($divmatch[0], '', $sContent);
				}
			}
			$fn = $EWR_EXPORT[$this->Export];
			if ($this->Export == "email") { // Email
				ob_end_clean();
				echo $this->$fn($sContent);
				ewr_CloseConn(); // Close connection
				exit();
			} else {
				$this->$fn($sContent);
			}
		}

		 // Close connection
		ewr_CloseConn();

		// Go to URL if specified
		if ($url <> "") {
			if (!EWR_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}

	// Initialize common variables
	var $ExportOptions; // Export options
	var $SearchOptions; // Search options
	var $FilterOptions; // Filter options

	// Paging variables
	var $RecIndex = 0; // Record index
	var $RecCount = 0; // Record count
	var $StartGrp = 0; // Start group
	var $StopGrp = 0; // Stop group
	var $TotalGrps = 0; // Total groups
	var $GrpCount = 0; // Group count
	var $GrpCounter = array(); // Group counter
	var $DisplayGrps = 20; // Groups per page
	var $GrpRange = 10;
	var $Sort = "";
	var $Filter = "";
	var $PageFirstGroupFilter = "";
	var $UserIDFilter = "";
	var $DrillDown = FALSE;
	var $DrillDownInPanel = FALSE;
	var $DrillDownList = "";

	// Clear field for ext filter
	var $ClearExtFilter = "";
	var $PopupName = "";
	var $PopupValue = "";
	var $FilterApplied;
	var $SearchCommand = FALSE;
	var $ShowHeader;
	var $GrpFldCount = 0;
	var $SubGrpFldCount = 0;
	var $DtlFldCount = 0;
	var $Cnt, $Col, $Val, $Smry, $Mn, $Mx, $GrandCnt, $GrandSmry, $GrandMn, $GrandMx;
	var $TotCount;
	var $GrandSummarySetup = FALSE;
	var $GrpIdx;

	//
	// Page main
	//
	function Page_Main() {
		global $rs;
		global $rsgrp;
		global $Security;
		global $gsFormError;
		global $gbDrillDownInPanel;
		global $ReportBreadcrumb;
		global $ReportLanguage;

		// Aggregate variables
		// 1st dimension = no of groups (level 0 used for grand total)
		// 2nd dimension = no of fields

		$nDtls = 7;
		$nGrps = 1;
		$this->Val = &ewr_InitArray($nDtls, 0);
		$this->Cnt = &ewr_Init2DArray($nGrps, $nDtls, 0);
		$this->Smry = &ewr_Init2DArray($nGrps, $nDtls, 0);
		$this->Mn = &ewr_Init2DArray($nGrps, $nDtls, NULL);
		$this->Mx = &ewr_Init2DArray($nGrps, $nDtls, NULL);
		$this->GrandCnt = &ewr_InitArray($nDtls, 0);
		$this->GrandSmry = &ewr_InitArray($nDtls, 0);
		$this->GrandMn = &ewr_InitArray($nDtls, NULL);
		$this->GrandMx = &ewr_InitArray($nDtls, NULL);

		// Set up array if accumulation required: array(Accum, SkipNullOrZero)
		$this->Col = array(array(FALSE, FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(FALSE,FALSE));

		// Set up groups per page dynamically
		$this->SetUpDisplayGrps();

		// Set up Breadcrumb
		if ($this->Export == "")
			$this->SetupBreadcrumb();
		$this->Salon_Name->SelectionList = "";
		$this->Salon_Name->DefaultSelectionList = "";
		$this->Salon_Name->ValueList = "";
		$this->Service_Name->SelectionList = "";
		$this->Service_Name->DefaultSelectionList = "";
		$this->Service_Name->ValueList = "";

		// Check if search command
		$this->SearchCommand = (@$_GET["cmd"] == "search");

		// Load default filter values
		$this->LoadDefaultFilters();

		// Load custom filters
		$this->Page_FilterLoad();

		// Set up popup filter
		$this->SetupPopup();

		// Load group db values if necessary
		$this->LoadGroupDbValues();

		// Handle Ajax popup
		$this->ProcessAjaxPopup();

		// Extended filter
		$sExtendedFilter = "";

		// Restore filter list
		$this->RestoreFilterList();

		// Build extended filter
		$sExtendedFilter = $this->GetExtendedFilter();
		ewr_AddFilter($this->Filter, $sExtendedFilter);

		// Build popup filter
		$sPopupFilter = $this->GetPopupFilter();

		//ewr_SetDebugMsg("popup filter: " . $sPopupFilter);
		ewr_AddFilter($this->Filter, $sPopupFilter);

		// Check if filter applied
		$this->FilterApplied = $this->CheckFilter();

		// Call Page Selecting event
		$this->Page_Selecting($this->Filter);
		$this->SearchOptions->GetItem("resetfilter")->Visible = $this->FilterApplied;

		// Get sort
		$this->Sort = $this->GetSort();

		// Get total count
		$sSql = ewr_BuildReportSql($this->getSqlSelect(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), $this->getSqlOrderBy(), $this->Filter, $this->Sort);
		$this->TotalGrps = $this->GetCnt($sSql);
		if ($this->DisplayGrps <= 0 || $this->DrillDown) // Display all groups
			$this->DisplayGrps = $this->TotalGrps;
		$this->StartGrp = 1;

		// Show header
		$this->ShowHeader = TRUE;

		// Set up start position if not export all
		if ($this->ExportAll && $this->Export <> "")
		    $this->DisplayGrps = $this->TotalGrps;
		else
			$this->SetUpStartGroup(); 

		// Set no record found message
		if ($this->TotalGrps == 0) {
				if ($this->Filter == "0=101") {
					$this->setWarningMessage($ReportLanguage->Phrase("EnterSearchCriteria"));
				} else {
					$this->setWarningMessage($ReportLanguage->Phrase("NoRecord"));
				}
		}

		// Hide export options if export
		if ($this->Export <> "")
			$this->ExportOptions->HideAllOptions();

		// Hide search/filter options if export/drilldown
		if ($this->Export <> "" || $this->DrillDown) {
			$this->SearchOptions->HideAllOptions();
			$this->FilterOptions->HideAllOptions();
		}

		// Get current page records
		$rs = $this->GetRs($sSql, $this->StartGrp, $this->DisplayGrps);
		$this->SetupFieldCount();
	}

	// Accummulate summary
	function AccumulateSummary() {
		$cntx = count($this->Smry);
		for ($ix = 0; $ix < $cntx; $ix++) {
			$cnty = count($this->Smry[$ix]);
			for ($iy = 1; $iy < $cnty; $iy++) {
				if ($this->Col[$iy][0]) { // Accumulate required
					$valwrk = $this->Val[$iy];
					if (is_null($valwrk)) {
						if (!$this->Col[$iy][1])
							$this->Cnt[$ix][$iy]++;
					} else {
						$accum = (!$this->Col[$iy][1] || !is_numeric($valwrk) || $valwrk <> 0);
						if ($accum) {
							$this->Cnt[$ix][$iy]++;
							if (is_numeric($valwrk)) {
								$this->Smry[$ix][$iy] += $valwrk;
								if (is_null($this->Mn[$ix][$iy])) {
									$this->Mn[$ix][$iy] = $valwrk;
									$this->Mx[$ix][$iy] = $valwrk;
								} else {
									if ($this->Mn[$ix][$iy] > $valwrk) $this->Mn[$ix][$iy] = $valwrk;
									if ($this->Mx[$ix][$iy] < $valwrk) $this->Mx[$ix][$iy] = $valwrk;
								}
							}
						}
					}
				}
			}
		}
		$cntx = count($this->Smry);
		for ($ix = 0; $ix < $cntx; $ix++) {
			$this->Cnt[$ix][0]++;
		}
	}

	// Reset level summary
	function ResetLevelSummary($lvl) {

		// Clear summary values
		$cntx = count($this->Smry);
		for ($ix = $lvl; $ix < $cntx; $ix++) {
			$cnty = count($this->Smry[$ix]);
			for ($iy = 1; $iy < $cnty; $iy++) {
				$this->Cnt[$ix][$iy] = 0;
				if ($this->Col[$iy][0]) {
					$this->Smry[$ix][$iy] = 0;
					$this->Mn[$ix][$iy] = NULL;
					$this->Mx[$ix][$iy] = NULL;
				}
			}
		}
		$cntx = count($this->Smry);
		for ($ix = $lvl; $ix < $cntx; $ix++) {
			$this->Cnt[$ix][0] = 0;
		}

		// Reset record count
		$this->RecCount = 0;
	}

	// Accummulate grand summary
	function AccumulateGrandSummary() {
		$this->TotCount++;
		$cntgs = count($this->GrandSmry);
		for ($iy = 1; $iy < $cntgs; $iy++) {
			if ($this->Col[$iy][0]) {
				$valwrk = $this->Val[$iy];
				if (is_null($valwrk) || !is_numeric($valwrk)) {
					if (!$this->Col[$iy][1])
						$this->GrandCnt[$iy]++;
				} else {
					if (!$this->Col[$iy][1] || $valwrk <> 0) {
						$this->GrandCnt[$iy]++;
						$this->GrandSmry[$iy] += $valwrk;
						if (is_null($this->GrandMn[$iy])) {
							$this->GrandMn[$iy] = $valwrk;
							$this->GrandMx[$iy] = $valwrk;
						} else {
							if ($this->GrandMn[$iy] > $valwrk) $this->GrandMn[$iy] = $valwrk;
							if ($this->GrandMx[$iy] < $valwrk) $this->GrandMx[$iy] = $valwrk;
						}
					}
				}
			}
		}
	}

	// Get count
	function GetCnt($sql) {
		$conn = &$this->Connection();
		$rscnt = $conn->Execute($sql);
		$cnt = ($rscnt) ? $rscnt->RecordCount() : 0;
		if ($rscnt) $rscnt->Close();
		return $cnt;
	}

	// Get recordset
	function GetRs($wrksql, $start, $grps) {
		$conn = &$this->Connection();
		$conn->raiseErrorFn = $GLOBALS["EWR_ERROR_FN"];
		$rswrk = $conn->SelectLimit($wrksql, $grps, $start - 1);
		$conn->raiseErrorFn = '';
		return $rswrk;
	}

	// Get row values
	function GetRow($opt) {
		global $rs;
		if (!$rs)
			return;
		if ($opt == 1) { // Get first row

	//		$rs->MoveFirst(); // NOTE: no need to move position
				$this->FirstRowData = array();
				$this->FirstRowData['Appointment_ID'] = ewr_Conv($rs->fields('Appointment ID'),3);
				$this->FirstRowData['Start_Time'] = ewr_Conv($rs->fields('Start Time'),200);
				$this->FirstRowData['Salon_Name'] = ewr_Conv($rs->fields('Salon Name'),200);
				$this->FirstRowData['User_Name'] = ewr_Conv($rs->fields('User Name'),200);
				$this->FirstRowData['Service_Name'] = ewr_Conv($rs->fields('Service Name'),200);
				$this->FirstRowData['Price'] = ewr_Conv($rs->fields('Price'),131);
		} else { // Get next row
			$rs->MoveNext();
		}
		if (!$rs->EOF) {
			$this->Appointment_ID->setDbValue($rs->fields('Appointment ID'));
			$this->Start_Time->setDbValue($rs->fields('Start Time'));
			$this->Salon_Name->setDbValue($rs->fields('Salon Name'));
			$this->User_Name->setDbValue($rs->fields('User Name'));
			$this->Service_Name->setDbValue($rs->fields('Service Name'));
			$this->Price->setDbValue($rs->fields('Price'));
			$this->Val[1] = $this->Appointment_ID->CurrentValue;
			$this->Val[2] = $this->Start_Time->CurrentValue;
			$this->Val[3] = $this->Salon_Name->CurrentValue;
			$this->Val[4] = $this->User_Name->CurrentValue;
			$this->Val[5] = $this->Service_Name->CurrentValue;
			$this->Val[6] = $this->Price->CurrentValue;
		} else {
			$this->Appointment_ID->setDbValue("");
			$this->Start_Time->setDbValue("");
			$this->Salon_Name->setDbValue("");
			$this->User_Name->setDbValue("");
			$this->Service_Name->setDbValue("");
			$this->Price->setDbValue("");
		}
	}

	//  Set up starting group
	function SetUpStartGroup() {

		// Exit if no groups
		if ($this->DisplayGrps == 0)
			return;

		// Check for a 'start' parameter
		if (@$_GET[EWR_TABLE_START_GROUP] != "") {
			$this->StartGrp = $_GET[EWR_TABLE_START_GROUP];
			$this->setStartGroup($this->StartGrp);
		} elseif (@$_GET["pageno"] != "") {
			$nPageNo = $_GET["pageno"];
			if (is_numeric($nPageNo)) {
				$this->StartGrp = ($nPageNo-1)*$this->DisplayGrps+1;
				if ($this->StartGrp <= 0) {
					$this->StartGrp = 1;
				} elseif ($this->StartGrp >= intval(($this->TotalGrps-1)/$this->DisplayGrps)*$this->DisplayGrps+1) {
					$this->StartGrp = intval(($this->TotalGrps-1)/$this->DisplayGrps)*$this->DisplayGrps+1;
				}
				$this->setStartGroup($this->StartGrp);
			} else {
				$this->StartGrp = $this->getStartGroup();
			}
		} else {
			$this->StartGrp = $this->getStartGroup();
		}

		// Check if correct start group counter
		if (!is_numeric($this->StartGrp) || $this->StartGrp == "") { // Avoid invalid start group counter
			$this->StartGrp = 1; // Reset start group counter
			$this->setStartGroup($this->StartGrp);
		} elseif (intval($this->StartGrp) > intval($this->TotalGrps)) { // Avoid starting group > total groups
			$this->StartGrp = intval(($this->TotalGrps-1)/$this->DisplayGrps) * $this->DisplayGrps + 1; // Point to last page first group
			$this->setStartGroup($this->StartGrp);
		} elseif (($this->StartGrp-1) % $this->DisplayGrps <> 0) {
			$this->StartGrp = intval(($this->StartGrp-1)/$this->DisplayGrps) * $this->DisplayGrps + 1; // Point to page boundary
			$this->setStartGroup($this->StartGrp);
		}
	}

	// Load group db values if necessary
	function LoadGroupDbValues() {
		$conn = &$this->Connection();
	}

	// Process Ajax popup
	function ProcessAjaxPopup() {
		global $ReportLanguage;
		$conn = &$this->Connection();
		$fld = NULL;
		if (@$_GET["popup"] <> "") {
			$popupname = $_GET["popup"];

			// Check popup name
			// Build distinct values for Salon Name

			if ($popupname == 'appointment_report_Salon_Name') {
				$bNullValue = FALSE;
				$bEmptyValue = FALSE;
				$sFilter = $this->Filter;

				// Call Page Filtering event
				$this->Page_Filtering($this->Salon_Name, $sFilter, "popup");
				$sSql = ewr_BuildReportSql($this->Salon_Name->SqlSelect, $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), $this->Salon_Name->SqlOrderBy, $sFilter, "");
				$rswrk = $conn->Execute($sSql);
				while ($rswrk && !$rswrk->EOF) {
					$this->Salon_Name->setDbValue($rswrk->fields[0]);
					if (is_null($this->Salon_Name->CurrentValue)) {
						$bNullValue = TRUE;
					} elseif ($this->Salon_Name->CurrentValue == "") {
						$bEmptyValue = TRUE;
					} else {
						$this->Salon_Name->ViewValue = $this->Salon_Name->CurrentValue;
						ewr_SetupDistinctValues($this->Salon_Name->ValueList, $this->Salon_Name->CurrentValue, $this->Salon_Name->ViewValue, FALSE, $this->Salon_Name->FldDelimiter);
					}
					$rswrk->MoveNext();
				}
				if ($rswrk)
					$rswrk->Close();
				if ($bEmptyValue)
					ewr_SetupDistinctValues($this->Salon_Name->ValueList, EWR_EMPTY_VALUE, $ReportLanguage->Phrase("EmptyLabel"), FALSE);
				if ($bNullValue)
					ewr_SetupDistinctValues($this->Salon_Name->ValueList, EWR_NULL_VALUE, $ReportLanguage->Phrase("NullLabel"), FALSE);
				$fld = &$this->Salon_Name;
			}

			// Build distinct values for Service Name
			if ($popupname == 'appointment_report_Service_Name') {
				$bNullValue = FALSE;
				$bEmptyValue = FALSE;
				$sFilter = $this->Filter;

				// Call Page Filtering event
				$this->Page_Filtering($this->Service_Name, $sFilter, "popup");
				$sSql = ewr_BuildReportSql($this->Service_Name->SqlSelect, $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), $this->Service_Name->SqlOrderBy, $sFilter, "");
				$rswrk = $conn->Execute($sSql);
				while ($rswrk && !$rswrk->EOF) {
					$this->Service_Name->setDbValue($rswrk->fields[0]);
					if (is_null($this->Service_Name->CurrentValue)) {
						$bNullValue = TRUE;
					} elseif ($this->Service_Name->CurrentValue == "") {
						$bEmptyValue = TRUE;
					} else {
						$this->Service_Name->ViewValue = $this->Service_Name->CurrentValue;
						ewr_SetupDistinctValues($this->Service_Name->ValueList, $this->Service_Name->CurrentValue, $this->Service_Name->ViewValue, FALSE, $this->Service_Name->FldDelimiter);
					}
					$rswrk->MoveNext();
				}
				if ($rswrk)
					$rswrk->Close();
				if ($bEmptyValue)
					ewr_SetupDistinctValues($this->Service_Name->ValueList, EWR_EMPTY_VALUE, $ReportLanguage->Phrase("EmptyLabel"), FALSE);
				if ($bNullValue)
					ewr_SetupDistinctValues($this->Service_Name->ValueList, EWR_NULL_VALUE, $ReportLanguage->Phrase("NullLabel"), FALSE);
				$fld = &$this->Service_Name;
			}

			// Output data as Json
			if (!is_null($fld)) {
				$jsdb = ewr_GetJsDb($fld, $fld->FldType);
				ob_end_clean();
				echo $jsdb;
				exit();
			}
		}
	}

	// Set up popup
	function SetupPopup() {
		global $ReportLanguage;
		$conn = &$this->Connection();
		if ($this->DrillDown)
			return;

		// Process post back form
		if (ewr_IsHttpPost()) {
			$sName = @$_POST["popup"]; // Get popup form name
			if ($sName <> "") {
				$cntValues = (is_array(@$_POST["sel_$sName"])) ? count($_POST["sel_$sName"]) : 0;
				if ($cntValues > 0) {
					$arValues = ewr_StripSlashes($_POST["sel_$sName"]);
					if (trim($arValues[0]) == "") // Select all
						$arValues = EWR_INIT_VALUE;
					$this->PopupName = $sName;
					if (ewr_IsAdvancedFilterValue($arValues) || $arValues == EWR_INIT_VALUE)
						$this->PopupValue = $arValues;
					if (!ewr_MatchedArray($arValues, $_SESSION["sel_$sName"])) {
						if ($this->HasSessionFilterValues($sName))
							$this->ClearExtFilter = $sName; // Clear extended filter for this field
					}
					$_SESSION["sel_$sName"] = $arValues;
					$_SESSION["rf_$sName"] = ewr_StripSlashes(@$_POST["rf_$sName"]);
					$_SESSION["rt_$sName"] = ewr_StripSlashes(@$_POST["rt_$sName"]);
					$this->ResetPager();
				}
			}

		// Get 'reset' command
		} elseif (@$_GET["cmd"] <> "") {
			$sCmd = $_GET["cmd"];
			if (strtolower($sCmd) == "reset") {
				$this->ClearSessionSelection('Salon_Name');
				$this->ClearSessionSelection('Service_Name');
				$this->ResetPager();
			}
		}

		// Load selection criteria to array
		// Get Salon Name selected values

		if (is_array(@$_SESSION["sel_appointment_report_Salon_Name"])) {
			$this->LoadSelectionFromSession('Salon_Name');
		} elseif (@$_SESSION["sel_appointment_report_Salon_Name"] == EWR_INIT_VALUE) { // Select all
			$this->Salon_Name->SelectionList = "";
		}

		// Get Service Name selected values
		if (is_array(@$_SESSION["sel_appointment_report_Service_Name"])) {
			$this->LoadSelectionFromSession('Service_Name');
		} elseif (@$_SESSION["sel_appointment_report_Service_Name"] == EWR_INIT_VALUE) { // Select all
			$this->Service_Name->SelectionList = "";
		}
	}

	// Reset pager
	function ResetPager() {

		// Reset start position (reset command)
		$this->StartGrp = 1;
		$this->setStartGroup($this->StartGrp);
	}

	// Set up number of groups displayed per page
	function SetUpDisplayGrps() {
		$sWrk = @$_GET[EWR_TABLE_GROUP_PER_PAGE];
		if ($sWrk <> "") {
			if (is_numeric($sWrk)) {
				$this->DisplayGrps = intval($sWrk);
			} else {
				if (strtoupper($sWrk) == "ALL") { // Display all groups
					$this->DisplayGrps = -1;
				} else {
					$this->DisplayGrps = 20; // Non-numeric, load default
				}
			}
			$this->setGroupPerPage($this->DisplayGrps); // Save to session

			// Reset start position (reset command)
			$this->StartGrp = 1;
			$this->setStartGroup($this->StartGrp);
		} else {
			if ($this->getGroupPerPage() <> "") {
				$this->DisplayGrps = $this->getGroupPerPage(); // Restore from session
			} else {
				$this->DisplayGrps = 20; // Load default
			}
		}
	}

	// Render row
	function RenderRow() {
		global $rs, $Security, $ReportLanguage;
		$conn = &$this->Connection();
		if ($this->RowTotalType == EWR_ROWTOTAL_GRAND && !$this->GrandSummarySetup) { // Grand total
			$bGotCount = FALSE;
			$bGotSummary = FALSE;

			// Get total count from sql directly
			$sSql = ewr_BuildReportSql($this->getSqlSelectCount(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), "", $this->Filter, "");
			$rstot = $conn->Execute($sSql);
			if ($rstot) {
				$this->TotCount = ($rstot->RecordCount()>1) ? $rstot->RecordCount() : $rstot->fields[0];
				$rstot->Close();
				$bGotCount = TRUE;
			} else {
				$this->TotCount = 0;
			}
		$bGotSummary = TRUE;

			// Accumulate grand summary from detail records
			if (!$bGotCount || !$bGotSummary) {
				$sSql = ewr_BuildReportSql($this->getSqlSelect(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), "", $this->Filter, "");
				$rs = $conn->Execute($sSql);
				if ($rs) {
					$this->GetRow(1);
					while (!$rs->EOF) {
						$this->AccumulateGrandSummary();
						$this->GetRow(2);
					}
					$rs->Close();
				}
			}
			$this->GrandSummarySetup = TRUE; // No need to set up again
		}

		// Call Row_Rendering event
		$this->Row_Rendering();

		//
		// Render view codes
		//

		if ($this->RowType == EWR_ROWTYPE_TOTAL) { // Summary row
			$this->RowAttrs["class"] = ($this->RowTotalType == EWR_ROWTOTAL_PAGE || $this->RowTotalType == EWR_ROWTOTAL_GRAND) ? "ewRptGrpAggregate" : "ewRptGrpSummary" . $this->RowGroupLevel; // Set up row class

			// Appointment ID
			$this->Appointment_ID->HrefValue = "";

			// Start Time
			$this->Start_Time->HrefValue = "";

			// Salon Name
			$this->Salon_Name->HrefValue = "";

			// User Name
			$this->User_Name->HrefValue = "";

			// Service Name
			$this->Service_Name->HrefValue = "";

			// Price
			$this->Price->HrefValue = "";
		} else {

			// Appointment ID
			$this->Appointment_ID->ViewValue = $this->Appointment_ID->CurrentValue;
			$this->Appointment_ID->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Start Time
			$this->Start_Time->ViewValue = $this->Start_Time->CurrentValue;
			$this->Start_Time->ViewValue = ewr_FormatDateTime($this->Start_Time->ViewValue, 7);
			$this->Start_Time->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Salon Name
			$this->Salon_Name->ViewValue = $this->Salon_Name->CurrentValue;
			$this->Salon_Name->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// User Name
			$this->User_Name->ViewValue = $this->User_Name->CurrentValue;
			$this->User_Name->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Service Name
			$this->Service_Name->ViewValue = $this->Service_Name->CurrentValue;
			$this->Service_Name->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Price
			$this->Price->ViewValue = $this->Price->CurrentValue;
			$this->Price->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Appointment ID
			$this->Appointment_ID->HrefValue = "";

			// Start Time
			$this->Start_Time->HrefValue = "";

			// Salon Name
			$this->Salon_Name->HrefValue = "";

			// User Name
			$this->User_Name->HrefValue = "";

			// Service Name
			$this->Service_Name->HrefValue = "";

			// Price
			$this->Price->HrefValue = "";
		}

		// Call Cell_Rendered event
		if ($this->RowType == EWR_ROWTYPE_TOTAL) { // Summary row
		} else {

			// Appointment ID
			$CurrentValue = $this->Appointment_ID->CurrentValue;
			$ViewValue = &$this->Appointment_ID->ViewValue;
			$ViewAttrs = &$this->Appointment_ID->ViewAttrs;
			$CellAttrs = &$this->Appointment_ID->CellAttrs;
			$HrefValue = &$this->Appointment_ID->HrefValue;
			$LinkAttrs = &$this->Appointment_ID->LinkAttrs;
			$this->Cell_Rendered($this->Appointment_ID, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Start Time
			$CurrentValue = $this->Start_Time->CurrentValue;
			$ViewValue = &$this->Start_Time->ViewValue;
			$ViewAttrs = &$this->Start_Time->ViewAttrs;
			$CellAttrs = &$this->Start_Time->CellAttrs;
			$HrefValue = &$this->Start_Time->HrefValue;
			$LinkAttrs = &$this->Start_Time->LinkAttrs;
			$this->Cell_Rendered($this->Start_Time, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Salon Name
			$CurrentValue = $this->Salon_Name->CurrentValue;
			$ViewValue = &$this->Salon_Name->ViewValue;
			$ViewAttrs = &$this->Salon_Name->ViewAttrs;
			$CellAttrs = &$this->Salon_Name->CellAttrs;
			$HrefValue = &$this->Salon_Name->HrefValue;
			$LinkAttrs = &$this->Salon_Name->LinkAttrs;
			$this->Cell_Rendered($this->Salon_Name, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// User Name
			$CurrentValue = $this->User_Name->CurrentValue;
			$ViewValue = &$this->User_Name->ViewValue;
			$ViewAttrs = &$this->User_Name->ViewAttrs;
			$CellAttrs = &$this->User_Name->CellAttrs;
			$HrefValue = &$this->User_Name->HrefValue;
			$LinkAttrs = &$this->User_Name->LinkAttrs;
			$this->Cell_Rendered($this->User_Name, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Service Name
			$CurrentValue = $this->Service_Name->CurrentValue;
			$ViewValue = &$this->Service_Name->ViewValue;
			$ViewAttrs = &$this->Service_Name->ViewAttrs;
			$CellAttrs = &$this->Service_Name->CellAttrs;
			$HrefValue = &$this->Service_Name->HrefValue;
			$LinkAttrs = &$this->Service_Name->LinkAttrs;
			$this->Cell_Rendered($this->Service_Name, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Price
			$CurrentValue = $this->Price->CurrentValue;
			$ViewValue = &$this->Price->ViewValue;
			$ViewAttrs = &$this->Price->ViewAttrs;
			$CellAttrs = &$this->Price->CellAttrs;
			$HrefValue = &$this->Price->HrefValue;
			$LinkAttrs = &$this->Price->LinkAttrs;
			$this->Cell_Rendered($this->Price, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);
		}

		// Call Row_Rendered event
		$this->Row_Rendered();
		$this->SetupFieldCount();
	}

	// Setup field count
	function SetupFieldCount() {
		$this->GrpFldCount = 0;
		$this->SubGrpFldCount = 0;
		$this->DtlFldCount = 0;
		if ($this->Appointment_ID->Visible) $this->DtlFldCount += 1;
		if ($this->Start_Time->Visible) $this->DtlFldCount += 1;
		if ($this->Salon_Name->Visible) $this->DtlFldCount += 1;
		if ($this->User_Name->Visible) $this->DtlFldCount += 1;
		if ($this->Service_Name->Visible) $this->DtlFldCount += 1;
		if ($this->Price->Visible) $this->DtlFldCount += 1;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $ReportBreadcrumb;
		$ReportBreadcrumb = new crBreadcrumb();
		$url = substr(ewr_CurrentUrl(), strrpos(ewr_CurrentUrl(), "/")+1);
		$url = preg_replace('/\?cmd=reset(all){0,1}$/i', '', $url); // Remove cmd=reset / cmd=resetall
		$ReportBreadcrumb->Add("rpt", $this->TableVar, $url, "", $this->TableVar, TRUE);
	}

	function SetupExportOptionsExt() {
		global $ReportLanguage;
	}

	// Return extended filter
	function GetExtendedFilter() {
		global $gsFormError;
		$sFilter = "";
		if ($this->DrillDown)
			return "";
		$bPostBack = ewr_IsHttpPost();
		$bRestoreSession = TRUE;
		$bSetupFilter = FALSE;

		// Reset extended filter if filter changed
		if ($bPostBack) {

			// Set/clear dropdown for field Salon Name
			if ($this->PopupName == 'appointment_report_Salon_Name' && $this->PopupValue <> "") {
				if ($this->PopupValue == EWR_INIT_VALUE)
					$this->Salon_Name->DropDownValue = EWR_ALL_VALUE;
				else
					$this->Salon_Name->DropDownValue = $this->PopupValue;
				$bRestoreSession = FALSE; // Do not restore
			} elseif ($this->ClearExtFilter == 'appointment_report_Salon_Name') {
				$this->SetSessionDropDownValue(EWR_INIT_VALUE, '', 'Salon_Name');
			}

			// Set/clear dropdown for field Service Name
			if ($this->PopupName == 'appointment_report_Service_Name' && $this->PopupValue <> "") {
				if ($this->PopupValue == EWR_INIT_VALUE)
					$this->Service_Name->DropDownValue = EWR_ALL_VALUE;
				else
					$this->Service_Name->DropDownValue = $this->PopupValue;
				$bRestoreSession = FALSE; // Do not restore
			} elseif ($this->ClearExtFilter == 'appointment_report_Service_Name') {
				$this->SetSessionDropDownValue(EWR_INIT_VALUE, '', 'Service_Name');
			}

		// Reset search command
		} elseif (@$_GET["cmd"] == "reset") {

			// Load default values
			$this->SetSessionDropDownValue($this->Salon_Name->DropDownValue, $this->Salon_Name->SearchOperator, 'Salon_Name'); // Field Salon Name
			$this->SetSessionDropDownValue($this->Service_Name->DropDownValue, $this->Service_Name->SearchOperator, 'Service_Name'); // Field Service Name

			//$bSetupFilter = TRUE; // No need to set up, just use default
		} else {
			$bRestoreSession = !$this->SearchCommand;

			// Field Salon Name
			if ($this->GetDropDownValue($this->Salon_Name)) {
				$bSetupFilter = TRUE;
			} elseif ($this->Salon_Name->DropDownValue <> EWR_INIT_VALUE && !isset($_SESSION['sv_appointment_report_Salon_Name'])) {
				$bSetupFilter = TRUE;
			}

			// Field Service Name
			if ($this->GetDropDownValue($this->Service_Name)) {
				$bSetupFilter = TRUE;
			} elseif ($this->Service_Name->DropDownValue <> EWR_INIT_VALUE && !isset($_SESSION['sv_appointment_report_Service_Name'])) {
				$bSetupFilter = TRUE;
			}
			if (!$this->ValidateForm()) {
				$this->setFailureMessage($gsFormError);
				return $sFilter;
			}
		}

		// Restore session
		if ($bRestoreSession) {
			$this->GetSessionDropDownValue($this->Salon_Name); // Field Salon Name
			$this->GetSessionDropDownValue($this->Service_Name); // Field Service Name
		}

		// Call page filter validated event
		$this->Page_FilterValidated();

		// Build SQL
		$this->BuildDropDownFilter($this->Salon_Name, $sFilter, $this->Salon_Name->SearchOperator, FALSE, TRUE); // Field Salon Name
		$this->BuildDropDownFilter($this->Service_Name, $sFilter, $this->Service_Name->SearchOperator, FALSE, TRUE); // Field Service Name

		// Save parms to session
		$this->SetSessionDropDownValue($this->Salon_Name->DropDownValue, $this->Salon_Name->SearchOperator, 'Salon_Name'); // Field Salon Name
		$this->SetSessionDropDownValue($this->Service_Name->DropDownValue, $this->Service_Name->SearchOperator, 'Service_Name'); // Field Service Name

		// Setup filter
		if ($bSetupFilter) {

			// Field Salon Name
			$sWrk = "";
			$this->BuildDropDownFilter($this->Salon_Name, $sWrk, $this->Salon_Name->SearchOperator);
			ewr_LoadSelectionFromFilter($this->Salon_Name, $sWrk, $this->Salon_Name->SelectionList, $this->Salon_Name->DropDownValue);
			$_SESSION['sel_appointment_report_Salon_Name'] = ($this->Salon_Name->SelectionList == "") ? EWR_INIT_VALUE : $this->Salon_Name->SelectionList;

			// Field Service Name
			$sWrk = "";
			$this->BuildDropDownFilter($this->Service_Name, $sWrk, $this->Service_Name->SearchOperator);
			ewr_LoadSelectionFromFilter($this->Service_Name, $sWrk, $this->Service_Name->SelectionList, $this->Service_Name->DropDownValue);
			$_SESSION['sel_appointment_report_Service_Name'] = ($this->Service_Name->SelectionList == "") ? EWR_INIT_VALUE : $this->Service_Name->SelectionList;
		}

		// Field Salon Name
		ewr_LoadDropDownList($this->Salon_Name->DropDownList, $this->Salon_Name->DropDownValue);

		// Field Service Name
		ewr_LoadDropDownList($this->Service_Name->DropDownList, $this->Service_Name->DropDownValue);
		return $sFilter;
	}

	// Build dropdown filter
	function BuildDropDownFilter(&$fld, &$FilterClause, $FldOpr, $Default = FALSE, $SaveFilter = FALSE) {
		$FldVal = ($Default) ? $fld->DefaultDropDownValue : $fld->DropDownValue;
		$sSql = "";
		if (is_array($FldVal)) {
			foreach ($FldVal as $val) {
				$sWrk = $this->GetDropDownFilter($fld, $val, $FldOpr);

				// Call Page Filtering event
				if (substr($val, 0, 2) <> "@@") $this->Page_Filtering($fld, $sWrk, "dropdown", $FldOpr, $val);
				if ($sWrk <> "") {
					if ($sSql <> "")
						$sSql .= " OR " . $sWrk;
					else
						$sSql = $sWrk;
				}
			}
		} else {
			$sSql = $this->GetDropDownFilter($fld, $FldVal, $FldOpr);

			// Call Page Filtering event
			if (substr($FldVal, 0, 2) <> "@@") $this->Page_Filtering($fld, $sSql, "dropdown", $FldOpr, $FldVal);
		}
		if ($sSql <> "") {
			ewr_AddFilter($FilterClause, $sSql);
			if ($SaveFilter) $fld->CurrentFilter = $sSql;
		}
	}

	function GetDropDownFilter(&$fld, $FldVal, $FldOpr) {
		$FldName = $fld->FldName;
		$FldExpression = $fld->FldExpression;
		$FldDataType = $fld->FldDataType;
		$FldDelimiter = $fld->FldDelimiter;
		$FldVal = strval($FldVal);
		if ($FldOpr == "") $FldOpr = "=";
		$sWrk = "";
		if ($FldVal == EWR_NULL_VALUE) {
			$sWrk = $FldExpression . " IS NULL";
		} elseif ($FldVal == EWR_NOT_NULL_VALUE) {
			$sWrk = $FldExpression . " IS NOT NULL";
		} elseif ($FldVal == EWR_EMPTY_VALUE) {
			$sWrk = $FldExpression . " = ''";
		} elseif ($FldVal == EWR_ALL_VALUE) {
			$sWrk = "1 = 1";
		} else {
			if (substr($FldVal, 0, 2) == "@@") {
				$sWrk = $this->GetCustomFilter($fld, $FldVal);
			} elseif ($FldDelimiter <> "" && trim($FldVal) <> "") {
				$sWrk = ewr_GetMultiSearchSql($FldExpression, trim($FldVal), $this->DBID);
			} else {
				if ($FldVal <> "" && $FldVal <> EWR_INIT_VALUE) {
					if ($FldDataType == EWR_DATATYPE_DATE && $FldOpr <> "") {
						$sWrk = ewr_DateFilterString($FldExpression, $FldOpr, $FldVal, $FldDataType, $this->DBID);
					} else {
						$sWrk = ewr_FilterString($FldOpr, $FldVal, $FldDataType, $this->DBID);
						if ($sWrk <> "") $sWrk = $FldExpression . $sWrk;
					}
				}
			}
		}
		return $sWrk;
	}

	// Get custom filter
	function GetCustomFilter(&$fld, $FldVal) {
		$sWrk = "";
		if (is_array($fld->AdvancedFilters)) {
			foreach ($fld->AdvancedFilters as $filter) {
				if ($filter->ID == $FldVal && $filter->Enabled) {
					$sFld = $fld->FldExpression;
					$sFn = $filter->FunctionName;
					$wrkid = (substr($filter->ID,0,2) == "@@") ? substr($filter->ID,2) : $filter->ID;
					if ($sFn <> "")
						$sWrk = $sFn($sFld);
					else
						$sWrk = "";
					$this->Page_Filtering($fld, $sWrk, "custom", $wrkid);
					break;
				}
			}
		}
		return $sWrk;
	}

	// Build extended filter
	function BuildExtendedFilter(&$fld, &$FilterClause, $Default = FALSE, $SaveFilter = FALSE) {
		$sWrk = ewr_GetExtendedFilter($fld, $Default, $this->DBID);
		if (!$Default)
			$this->Page_Filtering($fld, $sWrk, "extended", $fld->SearchOperator, $fld->SearchValue, $fld->SearchCondition, $fld->SearchOperator2, $fld->SearchValue2);
		if ($sWrk <> "") {
			ewr_AddFilter($FilterClause, $sWrk);
			if ($SaveFilter) $fld->CurrentFilter = $sWrk;
		}
	}

	// Get drop down value from querystring
	function GetDropDownValue(&$fld) {
		$parm = substr($fld->FldVar, 2);
		if (ewr_IsHttpPost())
			return FALSE; // Skip post back
		if (isset($_GET["so_$parm"]))
			$fld->SearchOperator = ewr_StripSlashes(@$_GET["so_$parm"]);
		if (isset($_GET["sv_$parm"])) {
			$fld->DropDownValue = ewr_StripSlashes(@$_GET["sv_$parm"]);
			return TRUE;
		}
		return FALSE;
	}

	// Get filter values from querystring
	function GetFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		if (ewr_IsHttpPost())
			return; // Skip post back
		$got = FALSE;
		if (isset($_GET["sv_$parm"])) {
			$fld->SearchValue = ewr_StripSlashes(@$_GET["sv_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["so_$parm"])) {
			$fld->SearchOperator = ewr_StripSlashes(@$_GET["so_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["sc_$parm"])) {
			$fld->SearchCondition = ewr_StripSlashes(@$_GET["sc_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["sv2_$parm"])) {
			$fld->SearchValue2 = ewr_StripSlashes(@$_GET["sv2_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["so2_$parm"])) {
			$fld->SearchOperator2 = ewr_StripSlashes($_GET["so2_$parm"]);
			$got = TRUE;
		}
		return $got;
	}

	// Set default ext filter
	function SetDefaultExtFilter(&$fld, $so1, $sv1, $sc, $so2, $sv2) {
		$fld->DefaultSearchValue = $sv1; // Default ext filter value 1
		$fld->DefaultSearchValue2 = $sv2; // Default ext filter value 2 (if operator 2 is enabled)
		$fld->DefaultSearchOperator = $so1; // Default search operator 1
		$fld->DefaultSearchOperator2 = $so2; // Default search operator 2 (if operator 2 is enabled)
		$fld->DefaultSearchCondition = $sc; // Default search condition (if operator 2 is enabled)
	}

	// Apply default ext filter
	function ApplyDefaultExtFilter(&$fld) {
		$fld->SearchValue = $fld->DefaultSearchValue;
		$fld->SearchValue2 = $fld->DefaultSearchValue2;
		$fld->SearchOperator = $fld->DefaultSearchOperator;
		$fld->SearchOperator2 = $fld->DefaultSearchOperator2;
		$fld->SearchCondition = $fld->DefaultSearchCondition;
	}

	// Check if Text Filter applied
	function TextFilterApplied(&$fld) {
		return (strval($fld->SearchValue) <> strval($fld->DefaultSearchValue) ||
			strval($fld->SearchValue2) <> strval($fld->DefaultSearchValue2) ||
			(strval($fld->SearchValue) <> "" &&
				strval($fld->SearchOperator) <> strval($fld->DefaultSearchOperator)) ||
			(strval($fld->SearchValue2) <> "" &&
				strval($fld->SearchOperator2) <> strval($fld->DefaultSearchOperator2)) ||
			strval($fld->SearchCondition) <> strval($fld->DefaultSearchCondition));
	}

	// Check if Non-Text Filter applied
	function NonTextFilterApplied(&$fld) {
		if (is_array($fld->DropDownValue)) {
			if (is_array($fld->DefaultDropDownValue)) {
				if (count($fld->DefaultDropDownValue) <> count($fld->DropDownValue))
					return TRUE;
				else
					return (count(array_diff($fld->DefaultDropDownValue, $fld->DropDownValue)) <> 0);
			} else {
				return TRUE;
			}
		} else {
			if (is_array($fld->DefaultDropDownValue))
				return TRUE;
			else
				$v1 = strval($fld->DefaultDropDownValue);
			if ($v1 == EWR_INIT_VALUE)
				$v1 = "";
			$v2 = strval($fld->DropDownValue);
			if ($v2 == EWR_INIT_VALUE || $v2 == EWR_ALL_VALUE)
				$v2 = "";
			return ($v1 <> $v2);
		}
	}

	// Get dropdown value from session
	function GetSessionDropDownValue(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->DropDownValue, 'sv_appointment_report_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so_appointment_report_' . $parm);
	}

	// Get filter values from session
	function GetSessionFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->SearchValue, 'sv_appointment_report_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so_appointment_report_' . $parm);
		$this->GetSessionValue($fld->SearchCondition, 'sc_appointment_report_' . $parm);
		$this->GetSessionValue($fld->SearchValue2, 'sv2_appointment_report_' . $parm);
		$this->GetSessionValue($fld->SearchOperator2, 'so2_appointment_report_' . $parm);
	}

	// Get value from session
	function GetSessionValue(&$sv, $sn) {
		if (array_key_exists($sn, $_SESSION))
			$sv = $_SESSION[$sn];
	}

	// Set dropdown value to session
	function SetSessionDropDownValue($sv, $so, $parm) {
		$_SESSION['sv_appointment_report_' . $parm] = $sv;
		$_SESSION['so_appointment_report_' . $parm] = $so;
	}

	// Set filter values to session
	function SetSessionFilterValues($sv1, $so1, $sc, $sv2, $so2, $parm) {
		$_SESSION['sv_appointment_report_' . $parm] = $sv1;
		$_SESSION['so_appointment_report_' . $parm] = $so1;
		$_SESSION['sc_appointment_report_' . $parm] = $sc;
		$_SESSION['sv2_appointment_report_' . $parm] = $sv2;
		$_SESSION['so2_appointment_report_' . $parm] = $so2;
	}

	// Check if has Session filter values
	function HasSessionFilterValues($parm) {
		return ((@$_SESSION['sv_' . $parm] <> "" && @$_SESSION['sv_' . $parm] <> EWR_INIT_VALUE) ||
			(@$_SESSION['sv_' . $parm] <> "" && @$_SESSION['sv_' . $parm] <> EWR_INIT_VALUE) ||
			(@$_SESSION['sv2_' . $parm] <> "" && @$_SESSION['sv2_' . $parm] <> EWR_INIT_VALUE));
	}

	// Dropdown filter exist
	function DropDownFilterExist(&$fld, $FldOpr) {
		$sWrk = "";
		$this->BuildDropDownFilter($fld, $sWrk, $FldOpr);
		return ($sWrk <> "");
	}

	// Extended filter exist
	function ExtendedFilterExist(&$fld) {
		$sExtWrk = "";
		$this->BuildExtendedFilter($fld, $sExtWrk);
		return ($sExtWrk <> "");
	}

	// Validate form
	function ValidateForm() {
		global $ReportLanguage, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EWR_SERVER_VALIDATE)
			return ($gsFormError == "");

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			$gsFormError .= ($gsFormError <> "") ? "<p>&nbsp;</p>" : "";
			$gsFormError .= $sFormCustomError;
		}
		return $ValidateForm;
	}

	// Clear selection stored in session
	function ClearSessionSelection($parm) {
		$_SESSION["sel_appointment_report_$parm"] = "";
		$_SESSION["rf_appointment_report_$parm"] = "";
		$_SESSION["rt_appointment_report_$parm"] = "";
	}

	// Load selection from session
	function LoadSelectionFromSession($parm) {
		$fld = &$this->fields($parm);
		$fld->SelectionList = @$_SESSION["sel_appointment_report_$parm"];
		$fld->RangeFrom = @$_SESSION["rf_appointment_report_$parm"];
		$fld->RangeTo = @$_SESSION["rt_appointment_report_$parm"];
	}

	// Load default value for filters
	function LoadDefaultFilters() {

		/**
		* Set up default values for non Text filters
		*/

		// Field Salon Name
		$this->Salon_Name->DefaultDropDownValue = EWR_INIT_VALUE;
		if (!$this->SearchCommand) $this->Salon_Name->DropDownValue = $this->Salon_Name->DefaultDropDownValue;
		$sWrk = "";
		$this->BuildDropDownFilter($this->Salon_Name, $sWrk, $this->Salon_Name->SearchOperator, TRUE);
		ewr_LoadSelectionFromFilter($this->Salon_Name, $sWrk, $this->Salon_Name->DefaultSelectionList);
		if (!$this->SearchCommand) $this->Salon_Name->SelectionList = $this->Salon_Name->DefaultSelectionList;

		// Field Service Name
		$this->Service_Name->DefaultDropDownValue = EWR_INIT_VALUE;
		if (!$this->SearchCommand) $this->Service_Name->DropDownValue = $this->Service_Name->DefaultDropDownValue;
		$sWrk = "";
		$this->BuildDropDownFilter($this->Service_Name, $sWrk, $this->Service_Name->SearchOperator, TRUE);
		ewr_LoadSelectionFromFilter($this->Service_Name, $sWrk, $this->Service_Name->DefaultSelectionList);
		if (!$this->SearchCommand) $this->Service_Name->SelectionList = $this->Service_Name->DefaultSelectionList;

		/**
		* Set up default values for extended filters
		* function SetDefaultExtFilter(&$fld, $so1, $sv1, $sc, $so2, $sv2)
		* Parameters:
		* $fld - Field object
		* $so1 - Default search operator 1
		* $sv1 - Default ext filter value 1
		* $sc - Default search condition (if operator 2 is enabled)
		* $so2 - Default search operator 2 (if operator 2 is enabled)
		* $sv2 - Default ext filter value 2 (if operator 2 is enabled)
		*/

		/**
		* Set up default values for popup filters
		*/

		// Field Salon Name
		// $this->Salon_Name->DefaultSelectionList = array("val1", "val2");
		// Field Service Name
		// $this->Service_Name->DefaultSelectionList = array("val1", "val2");

	}

	// Check if filter applied
	function CheckFilter() {

		// Check Salon Name extended filter
		if ($this->NonTextFilterApplied($this->Salon_Name))
			return TRUE;

		// Check Salon Name popup filter
		if (!ewr_MatchedArray($this->Salon_Name->DefaultSelectionList, $this->Salon_Name->SelectionList))
			return TRUE;

		// Check Service Name extended filter
		if ($this->NonTextFilterApplied($this->Service_Name))
			return TRUE;

		// Check Service Name popup filter
		if (!ewr_MatchedArray($this->Service_Name->DefaultSelectionList, $this->Service_Name->SelectionList))
			return TRUE;
		return FALSE;
	}

	// Show list of filters
	function ShowFilterList() {
		global $ReportLanguage;

		// Initialize
		$sFilterList = "";

		// Field Salon Name
		$sExtWrk = "";
		$sWrk = "";
		$this->BuildDropDownFilter($this->Salon_Name, $sExtWrk, $this->Salon_Name->SearchOperator);
		if (is_array($this->Salon_Name->SelectionList))
			$sWrk = ewr_JoinArray($this->Salon_Name->SelectionList, ", ", EWR_DATATYPE_STRING, 0, $this->DBID);
		$sFilter = "";
		if ($sExtWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sExtWrk</span>";
		elseif ($sWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sWrk</span>";
		if ($sFilter <> "")
			$sFilterList .= "<div><span class=\"ewFilterCaption\">" . $this->Salon_Name->FldCaption() . "</span>" . $sFilter . "</div>";

		// Field Service Name
		$sExtWrk = "";
		$sWrk = "";
		$this->BuildDropDownFilter($this->Service_Name, $sExtWrk, $this->Service_Name->SearchOperator);
		if (is_array($this->Service_Name->SelectionList))
			$sWrk = ewr_JoinArray($this->Service_Name->SelectionList, ", ", EWR_DATATYPE_STRING, 0, $this->DBID);
		$sFilter = "";
		if ($sExtWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sExtWrk</span>";
		elseif ($sWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sWrk</span>";
		if ($sFilter <> "")
			$sFilterList .= "<div><span class=\"ewFilterCaption\">" . $this->Service_Name->FldCaption() . "</span>" . $sFilter . "</div>";
		$divstyle = "";
		$divdataclass = "";

		// Show Filters
		if ($sFilterList <> "") {
			$sMessage = "<div class=\"ewDisplayTable\"" . $divstyle . "><div id=\"ewrFilterList\" class=\"alert alert-info\"" . $divdataclass . "><div id=\"ewrCurrentFilters\">" . $ReportLanguage->Phrase("CurrentFilters") . "</div>" . $sFilterList . "</div></div>";
			$this->Message_Showing($sMessage, "");
			echo $sMessage;
		}
	}

	// Get list of filters
	function GetFilterList() {

		// Initialize
		$sFilterList = "";

		// Field Salon Name
		$sWrk = "";
		$sWrk = ($this->Salon_Name->DropDownValue <> EWR_INIT_VALUE) ? $this->Salon_Name->DropDownValue : "";
		if (is_array($sWrk))
			$sWrk = implode("||", $sWrk);
		if ($sWrk <> "")
			$sWrk = "\"sv_Salon_Name\":\"" . ewr_JsEncode2($sWrk) . "\"";
		if ($sWrk == "") {
			$sWrk = ($this->Salon_Name->SelectionList <> EWR_INIT_VALUE) ? $this->Salon_Name->SelectionList : "";
			if (is_array($sWrk))
				$sWrk = implode("||", $sWrk);
			if ($sWrk <> "")
				$sWrk = "\"sel_Salon_Name\":\"" . ewr_JsEncode2($sWrk) . "\"";
		}
		if ($sWrk <> "") {
			if ($sFilterList <> "") $sFilterList .= ",";
			$sFilterList .= $sWrk;
		}

		// Field Service Name
		$sWrk = "";
		$sWrk = ($this->Service_Name->DropDownValue <> EWR_INIT_VALUE) ? $this->Service_Name->DropDownValue : "";
		if (is_array($sWrk))
			$sWrk = implode("||", $sWrk);
		if ($sWrk <> "")
			$sWrk = "\"sv_Service_Name\":\"" . ewr_JsEncode2($sWrk) . "\"";
		if ($sWrk == "") {
			$sWrk = ($this->Service_Name->SelectionList <> EWR_INIT_VALUE) ? $this->Service_Name->SelectionList : "";
			if (is_array($sWrk))
				$sWrk = implode("||", $sWrk);
			if ($sWrk <> "")
				$sWrk = "\"sel_Service_Name\":\"" . ewr_JsEncode2($sWrk) . "\"";
		}
		if ($sWrk <> "") {
			if ($sFilterList <> "") $sFilterList .= ",";
			$sFilterList .= $sWrk;
		}

		// Return filter list in json
		if ($sFilterList <> "")
			return "{" . $sFilterList . "}";
		else
			return "null";
	}

	// Restore list of filters
	function RestoreFilterList() {

		// Return if not reset filter
		if (@$_POST["cmd"] <> "resetfilter")
			return FALSE;
		$filter = json_decode(ewr_StripSlashes(@$_POST["filter"]), TRUE);

		// Field Salon Name
		$bRestoreFilter = FALSE;
		if (array_key_exists("sv_Salon_Name", $filter)) {
			$sWrk = $filter["sv_Salon_Name"];
			if (strpos($sWrk, "||") !== FALSE)
				$sWrk = explode("||", $sWrk);
			$this->SetSessionDropDownValue($sWrk, @$filter["so_Salon_Name"], "Salon_Name");
			$bRestoreFilter = TRUE;
		}
		if (array_key_exists("sel_Salon_Name", $filter)) {
			$sWrk = $filter["sel_Salon_Name"];
			$sWrk = explode("||", $sWrk);
			$this->Salon_Name->SelectionList = $sWrk;
			$_SESSION["sel_appointment_report_Salon_Name"] = $sWrk;
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Salon_Name"); // Clear drop down
			$bRestoreFilter = TRUE;
		}
		if (!$bRestoreFilter) { // Clear filter
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Salon_Name");
			$this->Salon_Name->SelectionList = "";
			$_SESSION["sel_appointment_report_Salon_Name"] = "";
		}

		// Field Service Name
		$bRestoreFilter = FALSE;
		if (array_key_exists("sv_Service_Name", $filter)) {
			$sWrk = $filter["sv_Service_Name"];
			if (strpos($sWrk, "||") !== FALSE)
				$sWrk = explode("||", $sWrk);
			$this->SetSessionDropDownValue($sWrk, @$filter["so_Service_Name"], "Service_Name");
			$bRestoreFilter = TRUE;
		}
		if (array_key_exists("sel_Service_Name", $filter)) {
			$sWrk = $filter["sel_Service_Name"];
			$sWrk = explode("||", $sWrk);
			$this->Service_Name->SelectionList = $sWrk;
			$_SESSION["sel_appointment_report_Service_Name"] = $sWrk;
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Service_Name"); // Clear drop down
			$bRestoreFilter = TRUE;
		}
		if (!$bRestoreFilter) { // Clear filter
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Service_Name");
			$this->Service_Name->SelectionList = "";
			$_SESSION["sel_appointment_report_Service_Name"] = "";
		}
	}

	// Return popup filter
	function GetPopupFilter() {
		$sWrk = "";
		if ($this->DrillDown)
			return "";
		if (!$this->DropDownFilterExist($this->Salon_Name, $this->Salon_Name->SearchOperator)) {
			if (is_array($this->Salon_Name->SelectionList)) {
				$sFilter = ewr_FilterSQL($this->Salon_Name, "`Salon Name`", EWR_DATATYPE_STRING, $this->DBID);

				// Call Page Filtering event
				$this->Page_Filtering($this->Salon_Name, $sFilter, "popup");
				$this->Salon_Name->CurrentFilter = $sFilter;
				ewr_AddFilter($sWrk, $sFilter);
			}
		}
		if (!$this->DropDownFilterExist($this->Service_Name, $this->Service_Name->SearchOperator)) {
			if (is_array($this->Service_Name->SelectionList)) {
				$sFilter = ewr_FilterSQL($this->Service_Name, "`Service Name`", EWR_DATATYPE_STRING, $this->DBID);

				// Call Page Filtering event
				$this->Page_Filtering($this->Service_Name, $sFilter, "popup");
				$this->Service_Name->CurrentFilter = $sFilter;
				ewr_AddFilter($sWrk, $sFilter);
			}
		}
		return $sWrk;
	}

	//-------------------------------------------------------------------------------
	// Function GetSort
	// - Return Sort parameters based on Sort Links clicked
	// - Variables setup: Session[EWR_TABLE_SESSION_ORDER_BY], Session["sort_Table_Field"]
	function GetSort() {
		if ($this->DrillDown)
			return "";

		// Check for Ctrl pressed
		$bCtrl = (@$_GET["ctrl"] <> "");

		// Check for a resetsort command
		if (strlen(@$_GET["cmd"]) > 0) {
			$sCmd = @$_GET["cmd"];
			if ($sCmd == "resetsort") {
				$this->setOrderBy("");
				$this->setStartGroup(1);
				$this->Appointment_ID->setSort("");
				$this->Start_Time->setSort("");
				$this->Salon_Name->setSort("");
				$this->User_Name->setSort("");
				$this->Service_Name->setSort("");
				$this->Price->setSort("");
			}

		// Check for an Order parameter
		} elseif (@$_GET["order"] <> "") {
			$this->CurrentOrder = ewr_StripSlashes(@$_GET["order"]);
			$this->CurrentOrderType = @$_GET["ordertype"];
			$this->UpdateSort($this->Appointment_ID, $bCtrl); // Appointment ID
			$this->UpdateSort($this->Start_Time, $bCtrl); // Start Time
			$this->UpdateSort($this->Salon_Name, $bCtrl); // Salon Name
			$this->UpdateSort($this->User_Name, $bCtrl); // User Name
			$this->UpdateSort($this->Service_Name, $bCtrl); // Service Name
			$this->UpdateSort($this->Price, $bCtrl); // Price
			$sSortSql = $this->SortSql();
			$this->setOrderBy($sSortSql);
			$this->setStartGroup(1);
		}
		return $this->getOrderBy();
	}

	// Export to EXCEL
	function ExportExcel($html) {
		global $gsExportFile;
		header('Content-Type: application/vnd.ms-excel' . (EWR_CHARSET <> '' ? ';charset=' . EWR_CHARSET : ''));
		header('Content-Disposition: attachment; filename=' . $gsExportFile . '.xls');
		echo $html;
	}

	// Export to PDF
	function ExportPdf($html) {
		ob_end_clean();
		echo($html);
		ewr_DeleteTmpImages($html);
		exit();
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ewr_Header(FALSE) ?>
<?php

// Create page object
if (!isset($appointment_report_rpt)) $appointment_report_rpt = new crappointment_report_rpt();
if (isset($Page)) $OldPage = $Page;
$Page = &$appointment_report_rpt;

// Page init
$Page->Page_Init();

// Page main
$Page->Page_Main();

// Global Page Rendering event (in ewrusrfn*.php)
Page_Rendering();

// Page Rendering event
$Page->Page_Render();
?>
<?php include_once "phprptinc/header.php" ?>
<?php if ($Page->Export == "") { ?>
<script type="text/javascript">

// Create page object
var appointment_report_rpt = new ewr_Page("appointment_report_rpt");

// Page properties
appointment_report_rpt.PageID = "rpt"; // Page ID
var EWR_PAGE_ID = appointment_report_rpt.PageID;

// Extend page with Chart_Rendering function
appointment_report_rpt.Chart_Rendering = 
 function(chart, chartid) { // DO NOT CHANGE THIS LINE!

 	//alert(chartid);
 }

// Extend page with Chart_Rendered function
appointment_report_rpt.Chart_Rendered = 
 function(chart, chartid) { // DO NOT CHANGE THIS LINE!

 	//alert(chartid);
 }
</script>
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<script type="text/javascript">

// Form object
var CurrentForm = fappointment_reportrpt = new ewr_Form("fappointment_reportrpt");

// Validate method
fappointment_reportrpt.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);

	// Call Form Custom Validate event
	if (!this.Form_CustomValidate(fobj))
		return false;
	return true;
}

// Form_CustomValidate method
fappointment_reportrpt.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }
<?php if (EWR_CLIENT_VALIDATE) { ?>
fappointment_reportrpt.ValidateRequired = true; // Uses JavaScript validation
<?php } else { ?>
fappointment_reportrpt.ValidateRequired = false; // No JavaScript validation
<?php } ?>

// Use Ajax
fappointment_reportrpt.Lists["sv_Salon_Name"] = {"LinkField":"sv_Salon_Name","Ajax":true,"DisplayFields":["sv_Salon_Name","","",""],"ParentFields":[],"FilterFields":[],"Options":[],"Template":""};
fappointment_reportrpt.Lists["sv_Service_Name"] = {"LinkField":"sv_Service_Name","Ajax":true,"DisplayFields":["sv_Service_Name","","",""],"ParentFields":[],"FilterFields":[],"Options":[],"Template":""};
</script>
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
<?php 
		echo date('d-m-Y');
	?>
<?php if ($Page->Export == "") { ?>
<!-- container (begin) -->
<div id="ewContainer" class="ewContainer">
<!-- top container (begin) -->
<div id="ewTop" class="ewTop">
<a id="top"></a>
<?php } ?>
<!-- top slot -->
<div class="ewToolbar">
<?php if ($Page->Export == "" && (!$Page->DrillDown || !$Page->DrillDownInPanel)) { ?>
<?php if ($ReportBreadcrumb) $ReportBreadcrumb->Render(); ?>
<?php } ?>
<?php
if (!$Page->DrillDownInPanel) {
	$Page->ExportOptions->Render("body");
	$Page->SearchOptions->Render("body");
	$Page->FilterOptions->Render("body");
}
?>
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<?php echo $ReportLanguage->SelectionForm(); ?>
<?php } ?>
<div class="clearfix"></div>
</div>
<?php $Page->ShowPageHeader(); ?>
<?php $Page->ShowMessage(); ?>
<?php if ($Page->Export == "") { ?>
</div>
<!-- top container (end) -->
	<!-- left container (begin) -->
	<div id="ewLeft" class="ewLeft">
<?php } ?>
	<!-- Left slot -->
<?php if ($Page->Export == "") { ?>
	</div>
	<!-- left container (end) -->
	<!-- center container - report (begin) -->
	<div id="ewCenter" class="ewCenter">
<?php } ?>
	<!-- center slot -->
<!-- summary report starts -->
<?php if ($Page->Export <> "pdf") { ?>
<div id="report_summary">
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<!-- Search form (begin) -->
<form name="fappointment_reportrpt" id="fappointment_reportrpt" class="form-inline ewForm ewExtFilterForm" action="<?php echo ewr_CurrentPage() ?>">
<?php $SearchPanelClass = ($Page->Filter <> "") ? " in" : " in"; ?>
<div id="fappointment_reportrpt_SearchPanel" class="ewSearchPanel collapse<?php echo $SearchPanelClass ?>">
<input type="hidden" name="cmd" value="search">
<div id="r_1" class="ewRow">
<div id="c_Salon_Name" class="ewCell form-group">
	<label for="sv_Salon_Name" class="ewSearchCaption ewLabel"><?php echo $Page->Salon_Name->FldCaption() ?></label>
	<span class="ewSearchField">
<?php ewr_PrependClass($Page->Salon_Name->EditAttrs["class"], "form-control"); ?>
<select data-table="appointment_report" data-field="x_Salon_Name" data-value-separator="<?php echo ewr_HtmlEncode(is_array($Page->Salon_Name->DisplayValueSeparator) ? json_encode($Page->Salon_Name->DisplayValueSeparator) : $Page->Salon_Name->DisplayValueSeparator) ?>" id="sv_Salon_Name" name="sv_Salon_Name"<?php echo $Page->Salon_Name->EditAttributes() ?>>
<option value=""><?php echo $ReportLanguage->Phrase("PleaseSelect") ?></option>
<?php
	$cntf = is_array($Page->Salon_Name->AdvancedFilters) ? count($Page->Salon_Name->AdvancedFilters) : 0;
	$cntd = is_array($Page->Salon_Name->DropDownList) ? count($Page->Salon_Name->DropDownList) : 0;
	$totcnt = $cntf + $cntd;
	$wrkcnt = 0;
	if ($cntf > 0) {
		foreach ($Page->Salon_Name->AdvancedFilters as $filter) {
			if ($filter->Enabled) {
				$selwrk = ewr_MatchedFilterValue($Page->Salon_Name->DropDownValue, $filter->ID) ? " selected" : "";
?>
<option value="<?php echo $filter->ID ?>"<?php echo $selwrk ?>><?php echo $filter->Name ?></option>
<?php
				$wrkcnt += 1;
			}
		}
	}
	for ($i = 0; $i < $cntd; $i++) {
		$selwrk = " selected";
?>
<option value="<?php echo $Page->Salon_Name->DropDownList[$i] ?>"<?php echo $selwrk ?>><?php echo ewr_DropDownDisplayValue($Page->Salon_Name->DropDownList[$i], "", 0) ?></option>
<?php
		$wrkcnt += 1;
	}
?>
</select>
<?php
$Page->Salon_Name->LookupSql = "SELECT DISTINCT `Salon Name`, `Salon Name` AS `DispFld` FROM `appointment report`";
$sWhereWrk = "";

// Call Lookup selecting
$Page->Lookup_Selecting($Page->Salon_Name, $sWhereWrk);
if ($sWhereWrk <> "") $Page->Salon_Name->LookupSql .= " WHERE " . $sWhereWrk;
$Page->Salon_Name->LookupSql .= " ORDER BY `Salon Name`";
?>
<input type="hidden" name="s_sv_Salon_Name" id="s_sv_Salon_Name" value="s=<?php echo ewr_Encrypt($Page->Salon_Name->LookupSql) ?>&amp;f0=<?php echo ewr_Encrypt("`Salon Name` = {filter_value}"); ?>&amp;t0=200&amp;ds=&amp;df=0&amp;dlm=<?php echo ewr_Encrypt($Page->Salon_Name->FldDelimiter) ?>&amp;d=DB"></span>
</div>
</div>
<div id="r_2" class="ewRow">
<div id="c_Service_Name" class="ewCell form-group">
	<label for="sv_Service_Name" class="ewSearchCaption ewLabel"><?php echo $Page->Service_Name->FldCaption() ?></label>
	<span class="ewSearchField">
<?php ewr_PrependClass($Page->Service_Name->EditAttrs["class"], "form-control"); ?>
<select data-table="appointment_report" data-field="x_Service_Name" data-value-separator="<?php echo ewr_HtmlEncode(is_array($Page->Service_Name->DisplayValueSeparator) ? json_encode($Page->Service_Name->DisplayValueSeparator) : $Page->Service_Name->DisplayValueSeparator) ?>" id="sv_Service_Name" name="sv_Service_Name"<?php echo $Page->Service_Name->EditAttributes() ?>>
<option value=""><?php echo $ReportLanguage->Phrase("PleaseSelect") ?></option>
<?php
	$cntf = is_array($Page->Service_Name->AdvancedFilters) ? count($Page->Service_Name->AdvancedFilters) : 0;
	$cntd = is_array($Page->Service_Name->DropDownList) ? count($Page->Service_Name->DropDownList) : 0;
	$totcnt = $cntf + $cntd;
	$wrkcnt = 0;
	if ($cntf > 0) {
		foreach ($Page->Service_Name->AdvancedFilters as $filter) {
			if ($filter->Enabled) {
				$selwrk = ewr_MatchedFilterValue($Page->Service_Name->DropDownValue, $filter->ID) ? " selected" : "";
?>
<option value="<?php echo $filter->ID ?>"<?php echo $selwrk ?>><?php echo $filter->Name ?></option>
<?php
				$wrkcnt += 1;
			}
		}
	}
	for ($i = 0; $i < $cntd; $i++) {
		$selwrk = " selected";
?>
<option value="<?php echo $Page->Service_Name->DropDownList[$i] ?>"<?php echo $selwrk ?>><?php echo ewr_DropDownDisplayValue($Page->Service_Name->DropDownList[$i], "", 0) ?></option>
<?php
		$wrkcnt += 1;
	}
?>
</select>
<?php
$Page->Service_Name->LookupSql = "SELECT DISTINCT `Service Name`, `Service Name` AS `DispFld` FROM `appointment report`";
$sWhereWrk = "";

// Call Lookup selecting
$Page->Lookup_Selecting($Page->Service_Name, $sWhereWrk);
if ($sWhereWrk <> "") $Page->Service_Name->LookupSql .= " WHERE " . $sWhereWrk;
$Page->Service_Name->LookupSql .= " ORDER BY `Service Name`";
?>
<input type="hidden" name="s_sv_Service_Name" id="s_sv_Service_Name" value="s=<?php echo ewr_Encrypt($Page->Service_Name->LookupSql) ?>&amp;f0=<?php echo ewr_Encrypt("`Service Name` = {filter_value}"); ?>&amp;t0=200&amp;ds=&amp;df=0&amp;dlm=<?php echo ewr_Encrypt($Page->Service_Name->FldDelimiter) ?>&amp;d=DB"></span>
</div>
</div>
<div class="ewRow"><input type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary" value="<?php echo $ReportLanguage->Phrase("Search") ?>">
<input type="reset" name="btnreset" id="btnreset" class="btn hide" value="<?php echo $ReportLanguage->Phrase("Reset") ?>"></div>
</div>
</form>
<script type="text/javascript">
fappointment_reportrpt.Init();
fappointment_reportrpt.FilterList = <?php echo $Page->GetFilterList() ?>;
</script>
<!-- Search form (end) -->
<?php } ?>
<?php if ($Page->ShowCurrentFilter) { ?>
<?php $Page->ShowFilterList() ?>
<?php } ?>
<?php } ?>
<?php

// Set the last group to display if not export all
if ($Page->ExportAll && $Page->Export <> "") {
	$Page->StopGrp = $Page->TotalGrps;
} else {
	$Page->StopGrp = $Page->StartGrp + $Page->DisplayGrps - 1;
}

// Stop group <= total number of groups
if (intval($Page->StopGrp) > intval($Page->TotalGrps))
	$Page->StopGrp = $Page->TotalGrps;
$Page->RecCount = 0;
$Page->RecIndex = 0;

// Get first row
if ($Page->TotalGrps > 0) {
	$Page->GetRow(1);
	$Page->GrpCount = 1;
}
$Page->GrpIdx = ewr_InitArray(2, -1);
$Page->GrpIdx[0] = -1;
$Page->GrpIdx[1] = $Page->StopGrp - $Page->StartGrp + 1;
while ($rs && !$rs->EOF && $Page->GrpCount <= $Page->DisplayGrps || $Page->ShowHeader) {

	// Show dummy header for custom template
	// Show header

	if ($Page->ShowHeader) {
?>
<?php if ($Page->Export <> "pdf") { ?>
<div class="panel panel-default ewGrid"<?php echo $Page->ReportTableStyle ?>>
<?php } ?>
<!-- Report grid (begin) -->
<?php if ($Page->Export <> "pdf") { ?>
<div class="<?php if (ewr_IsResponsiveLayout()) { echo "table-responsive "; } ?>ewGridMiddlePanel">
<?php } ?>
<table class="<?php echo $Page->ReportTableClass ?>">
<thead>
	<!-- Table header -->
	<tr class="ewTableHeader">
<?php if ($Page->Appointment_ID->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Appointment_ID"><div class="appointment_report_Appointment_ID"><span class="ewTableHeaderCaption"><?php echo $Page->Appointment_ID->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Appointment_ID">
<?php if ($Page->SortUrl($Page->Appointment_ID) == "") { ?>
		<div class="ewTableHeaderBtn appointment_report_Appointment_ID">
			<span class="ewTableHeaderCaption"><?php echo $Page->Appointment_ID->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer appointment_report_Appointment_ID" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Appointment_ID) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Appointment_ID->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Appointment_ID->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Appointment_ID->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Start_Time->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Start_Time"><div class="appointment_report_Start_Time"><span class="ewTableHeaderCaption"><?php echo $Page->Start_Time->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Start_Time">
<?php if ($Page->SortUrl($Page->Start_Time) == "") { ?>
		<div class="ewTableHeaderBtn appointment_report_Start_Time">
			<span class="ewTableHeaderCaption"><?php echo $Page->Start_Time->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer appointment_report_Start_Time" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Start_Time) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Start_Time->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Start_Time->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Start_Time->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Salon_Name->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Salon_Name"><div class="appointment_report_Salon_Name"><span class="ewTableHeaderCaption"><?php echo $Page->Salon_Name->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Salon_Name">
<?php if ($Page->SortUrl($Page->Salon_Name) == "") { ?>
		<div class="ewTableHeaderBtn appointment_report_Salon_Name">
			<span class="ewTableHeaderCaption"><?php echo $Page->Salon_Name->FldCaption() ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'appointment_report_Salon_Name', false, '<?php echo $Page->Salon_Name->RangeFrom; ?>', '<?php echo $Page->Salon_Name->RangeTo; ?>');" id="x_Salon_Name<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer appointment_report_Salon_Name" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Salon_Name) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Salon_Name->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Salon_Name->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Salon_Name->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'appointment_report_Salon_Name', false, '<?php echo $Page->Salon_Name->RangeFrom; ?>', '<?php echo $Page->Salon_Name->RangeTo; ?>');" id="x_Salon_Name<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->User_Name->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="User_Name"><div class="appointment_report_User_Name"><span class="ewTableHeaderCaption"><?php echo $Page->User_Name->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="User_Name">
<?php if ($Page->SortUrl($Page->User_Name) == "") { ?>
		<div class="ewTableHeaderBtn appointment_report_User_Name">
			<span class="ewTableHeaderCaption"><?php echo $Page->User_Name->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer appointment_report_User_Name" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->User_Name) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->User_Name->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->User_Name->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->User_Name->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Service_Name->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Service_Name"><div class="appointment_report_Service_Name"><span class="ewTableHeaderCaption"><?php echo $Page->Service_Name->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Service_Name">
<?php if ($Page->SortUrl($Page->Service_Name) == "") { ?>
		<div class="ewTableHeaderBtn appointment_report_Service_Name">
			<span class="ewTableHeaderCaption"><?php echo $Page->Service_Name->FldCaption() ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'appointment_report_Service_Name', false, '<?php echo $Page->Service_Name->RangeFrom; ?>', '<?php echo $Page->Service_Name->RangeTo; ?>');" id="x_Service_Name<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer appointment_report_Service_Name" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Service_Name) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Service_Name->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Service_Name->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Service_Name->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'appointment_report_Service_Name', false, '<?php echo $Page->Service_Name->RangeFrom; ?>', '<?php echo $Page->Service_Name->RangeTo; ?>');" id="x_Service_Name<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Price->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Price"><div class="appointment_report_Price"><span class="ewTableHeaderCaption"><?php echo $Page->Price->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Price">
<?php if ($Page->SortUrl($Page->Price) == "") { ?>
		<div class="ewTableHeaderBtn appointment_report_Price">
			<span class="ewTableHeaderCaption"><?php echo $Page->Price->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer appointment_report_Price" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Price) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Price->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Price->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Price->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
	</tr>
</thead>
<tbody>
<?php
		if ($Page->TotalGrps == 0) break; // Show header only
		$Page->ShowHeader = FALSE;
	}
	$Page->RecCount++;
	$Page->RecIndex++;

		// Render detail row
		$Page->ResetAttrs();
		$Page->RowType = EWR_ROWTYPE_DETAIL;
		$Page->RenderRow();
?>
	<tr<?php echo $Page->RowAttributes(); ?>>
<?php if ($Page->Appointment_ID->Visible) { ?>
		<td data-field="Appointment_ID"<?php echo $Page->Appointment_ID->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_appointment_report_Appointment_ID"<?php echo $Page->Appointment_ID->ViewAttributes() ?>><?php echo $Page->Appointment_ID->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Start_Time->Visible) { ?>
		<td data-field="Start_Time"<?php echo $Page->Start_Time->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_appointment_report_Start_Time"<?php echo $Page->Start_Time->ViewAttributes() ?>><?php echo $Page->Start_Time->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Salon_Name->Visible) { ?>
		<td data-field="Salon_Name"<?php echo $Page->Salon_Name->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_appointment_report_Salon_Name"<?php echo $Page->Salon_Name->ViewAttributes() ?>><?php echo $Page->Salon_Name->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->User_Name->Visible) { ?>
		<td data-field="User_Name"<?php echo $Page->User_Name->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_appointment_report_User_Name"<?php echo $Page->User_Name->ViewAttributes() ?>><?php echo $Page->User_Name->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Service_Name->Visible) { ?>
		<td data-field="Service_Name"<?php echo $Page->Service_Name->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_appointment_report_Service_Name"<?php echo $Page->Service_Name->ViewAttributes() ?>><?php echo $Page->Service_Name->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Price->Visible) { ?>
		<td data-field="Price"<?php echo $Page->Price->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_appointment_report_Price"<?php echo $Page->Price->ViewAttributes() ?>><?php echo $Page->Price->ListViewValue() ?></span></td>
<?php } ?>
	</tr>
<?php

		// Accumulate page summary
		$Page->AccumulateSummary();

		// Get next record
		$Page->GetRow(2);
	$Page->GrpCount++;
} // End while
?>
<?php if ($Page->TotalGrps > 0) { ?>
</tbody>
<tfoot>
	</tfoot>
<?php } elseif (!$Page->ShowHeader && TRUE) { // No header displayed ?>
<?php if ($Page->Export <> "pdf") { ?>
<div class="panel panel-default ewGrid"<?php echo $Page->ReportTableStyle ?>>
<?php } ?>
<!-- Report grid (begin) -->
<?php if ($Page->Export <> "pdf") { ?>
<div class="<?php if (ewr_IsResponsiveLayout()) { echo "table-responsive "; } ?>ewGridMiddlePanel">
<?php } ?>
<table class="<?php echo $Page->ReportTableClass ?>">
<?php } ?>
<?php if ($Page->TotalGrps > 0 || TRUE) { // Show footer ?>
</table>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<?php if ($Page->Export == "" && !($Page->DrillDown && $Page->TotalGrps > 0)) { ?>
<div class="panel-footer ewGridLowerPanel">
<?php include "appointment_reportrptpager.php" ?>
<div class="clearfix"></div>
</div>
<?php } ?>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<?php } ?>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<!-- Summary Report Ends -->
<?php if ($Page->Export == "") { ?>
	</div>
	<!-- center container - report (end) -->
	<!-- right container (begin) -->
	<div id="ewRight" class="ewRight">
<?php } ?>
	<!-- Right slot -->
<?php if ($Page->Export == "") { ?>
	</div>
	<!-- right container (end) -->
<div class="clearfix"></div>
<!-- bottom container (begin) -->
<div id="ewBottom" class="ewBottom">
<?php } ?>
	<!-- Bottom slot -->
<?php if ($Page->Export == "") { ?>
	</div>
<!-- Bottom Container (End) -->
</div>
<!-- Table Container (End) -->
<?php } ?>
<?php $Page->ShowPageFooter(); ?>
<?php if (EWR_DEBUG_ENABLED) echo ewr_DebugMsg(); ?>
<?php

// Close recordsets
if ($rsgrp) $rsgrp->Close();
if ($rs) $rs->Close();
?>
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php } ?>
<?php include_once "phprptinc/footer.php" ?>
<?php
$Page->Page_Terminate();
if (isset($OldPage)) $Page = $OldPage;
?>
