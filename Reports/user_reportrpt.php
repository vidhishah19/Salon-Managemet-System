<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start();
?>
<?php include_once "phprptinc/ewrcfg9.php" ?>
<?php include_once ((EW_USE_ADODB) ? "adodb5/adodb.inc.php" : "phprptinc/ewmysql.php") ?>
<?php include_once "phprptinc/ewrfn9.php" ?>
<?php include_once "phprptinc/ewrusrfn9.php" ?>
<?php include_once "user_reportrptinfo.php" ?>
<?php

//
// Page class
//

$user_report_rpt = NULL; // Initialize page object first

class cruser_report_rpt extends cruser_report {

	// Page ID
	var $PageID = 'rpt';

	// Project ID
	var $ProjectID = "{3C488A29-896C-4371-BB76-02105C3C2105}";

	// Page object name
	var $PageObjName = 'user_report_rpt';

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

		// Table object (user_report)
		if (!isset($GLOBALS["user_report"])) {
			$GLOBALS["user_report"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["user_report"];
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
			define("EWR_TABLE_NAME", 'user report', TRUE);

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
		$this->FilterOptions->TagClassName = "ewFilterOption fuser_reportrpt";
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
		$item->Body = "<a title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToEmail", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToEmail", TRUE)) . "\" id=\"emf_user_report\" href=\"javascript:void(0);\" onclick=\"ewr_EmailDialogShow({lnk:'emf_user_report',hdr:ewLanguage.Phrase('ExportToEmail'),url:'$url',exportid:'$exportid',el:this});\">" . $ReportLanguage->Phrase("ExportToEmail") . "</a>";
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
		$item->Body = "<button type=\"button\" class=\"btn btn-default ewSearchToggle" . $SearchToggleClass . "\" title=\"" . $ReportLanguage->Phrase("SearchBtn", TRUE) . "\" data-caption=\"" . $ReportLanguage->Phrase("SearchBtn", TRUE) . "\" data-toggle=\"button\" data-form=\"fuser_reportrpt\">" . $ReportLanguage->Phrase("SearchBtn") . "</button>";
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
		$item->Body = "<a class=\"ewSaveFilter\" data-form=\"fuser_reportrpt\" href=\"#\">" . $ReportLanguage->Phrase("SaveCurrentFilter") . "</a>";
		$item->Visible = TRUE;
		$item = &$this->FilterOptions->Add("deletefilter");
		$item->Body = "<a class=\"ewDeleteFilter\" data-form=\"fuser_reportrpt\" href=\"#\">" . $ReportLanguage->Phrase("DeleteFilter") . "</a>";
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
		$this->Ratings->SelectionList = "";
		$this->Ratings->DefaultSelectionList = "";
		$this->Ratings->ValueList = "";
		$this->Bookmarked_Salon->SelectionList = "";
		$this->Bookmarked_Salon->DefaultSelectionList = "";
		$this->Bookmarked_Salon->ValueList = "";

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
				$this->FirstRowData['User_ID'] = ewr_Conv($rs->fields('User ID'),3);
				$this->FirstRowData['User_Name'] = ewr_Conv($rs->fields('User Name'),200);
				$this->FirstRowData['Service_Name'] = ewr_Conv($rs->fields('Service Name'),200);
				$this->FirstRowData['Ratings'] = ewr_Conv($rs->fields('Ratings'),131);
				$this->FirstRowData['Price'] = ewr_Conv($rs->fields('Price'),131);
				$this->FirstRowData['Bookmarked_Salon'] = ewr_Conv($rs->fields('Bookmarked Salon'),200);
		} else { // Get next row
			$rs->MoveNext();
		}
		if (!$rs->EOF) {
			$this->User_ID->setDbValue($rs->fields('User ID'));
			$this->User_Name->setDbValue($rs->fields('User Name'));
			$this->Service_Name->setDbValue($rs->fields('Service Name'));
			$this->Ratings->setDbValue($rs->fields('Ratings'));
			$this->Price->setDbValue($rs->fields('Price'));
			$this->Bookmarked_Salon->setDbValue($rs->fields('Bookmarked Salon'));
			$this->Val[1] = $this->User_ID->CurrentValue;
			$this->Val[2] = $this->User_Name->CurrentValue;
			$this->Val[3] = $this->Service_Name->CurrentValue;
			$this->Val[4] = $this->Ratings->CurrentValue;
			$this->Val[5] = $this->Price->CurrentValue;
			$this->Val[6] = $this->Bookmarked_Salon->CurrentValue;
		} else {
			$this->User_ID->setDbValue("");
			$this->User_Name->setDbValue("");
			$this->Service_Name->setDbValue("");
			$this->Ratings->setDbValue("");
			$this->Price->setDbValue("");
			$this->Bookmarked_Salon->setDbValue("");
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
			// Build distinct values for Ratings

			if ($popupname == 'user_report_Ratings') {
				$bNullValue = FALSE;
				$bEmptyValue = FALSE;
				$sFilter = $this->Filter;

				// Call Page Filtering event
				$this->Page_Filtering($this->Ratings, $sFilter, "popup");
				$sSql = ewr_BuildReportSql($this->Ratings->SqlSelect, $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), $this->Ratings->SqlOrderBy, $sFilter, "");
				$rswrk = $conn->Execute($sSql);
				while ($rswrk && !$rswrk->EOF) {
					$this->Ratings->setDbValue($rswrk->fields[0]);
					if (is_null($this->Ratings->CurrentValue)) {
						$bNullValue = TRUE;
					} elseif ($this->Ratings->CurrentValue == "") {
						$bEmptyValue = TRUE;
					} else {
						$this->Ratings->ViewValue = $this->Ratings->CurrentValue;
						ewr_SetupDistinctValues($this->Ratings->ValueList, $this->Ratings->CurrentValue, $this->Ratings->ViewValue, FALSE, $this->Ratings->FldDelimiter);
					}
					$rswrk->MoveNext();
				}
				if ($rswrk)
					$rswrk->Close();
				if ($bEmptyValue)
					ewr_SetupDistinctValues($this->Ratings->ValueList, EWR_EMPTY_VALUE, $ReportLanguage->Phrase("EmptyLabel"), FALSE);
				if ($bNullValue)
					ewr_SetupDistinctValues($this->Ratings->ValueList, EWR_NULL_VALUE, $ReportLanguage->Phrase("NullLabel"), FALSE);
				$fld = &$this->Ratings;
			}

			// Build distinct values for Bookmarked Salon
			if ($popupname == 'user_report_Bookmarked_Salon') {
				$bNullValue = FALSE;
				$bEmptyValue = FALSE;
				$sFilter = $this->Filter;

				// Call Page Filtering event
				$this->Page_Filtering($this->Bookmarked_Salon, $sFilter, "popup");
				$sSql = ewr_BuildReportSql($this->Bookmarked_Salon->SqlSelect, $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), $this->Bookmarked_Salon->SqlOrderBy, $sFilter, "");
				$rswrk = $conn->Execute($sSql);
				while ($rswrk && !$rswrk->EOF) {
					$this->Bookmarked_Salon->setDbValue($rswrk->fields[0]);
					if (is_null($this->Bookmarked_Salon->CurrentValue)) {
						$bNullValue = TRUE;
					} elseif ($this->Bookmarked_Salon->CurrentValue == "") {
						$bEmptyValue = TRUE;
					} else {
						$this->Bookmarked_Salon->ViewValue = $this->Bookmarked_Salon->CurrentValue;
						ewr_SetupDistinctValues($this->Bookmarked_Salon->ValueList, $this->Bookmarked_Salon->CurrentValue, $this->Bookmarked_Salon->ViewValue, FALSE, $this->Bookmarked_Salon->FldDelimiter);
					}
					$rswrk->MoveNext();
				}
				if ($rswrk)
					$rswrk->Close();
				if ($bEmptyValue)
					ewr_SetupDistinctValues($this->Bookmarked_Salon->ValueList, EWR_EMPTY_VALUE, $ReportLanguage->Phrase("EmptyLabel"), FALSE);
				if ($bNullValue)
					ewr_SetupDistinctValues($this->Bookmarked_Salon->ValueList, EWR_NULL_VALUE, $ReportLanguage->Phrase("NullLabel"), FALSE);
				$fld = &$this->Bookmarked_Salon;
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
				$this->ClearSessionSelection('Ratings');
				$this->ClearSessionSelection('Bookmarked_Salon');
				$this->ResetPager();
			}
		}

		// Load selection criteria to array
		// Get Ratings selected values

		if (is_array(@$_SESSION["sel_user_report_Ratings"])) {
			$this->LoadSelectionFromSession('Ratings');
		} elseif (@$_SESSION["sel_user_report_Ratings"] == EWR_INIT_VALUE) { // Select all
			$this->Ratings->SelectionList = "";
		}

		// Get Bookmarked Salon selected values
		if (is_array(@$_SESSION["sel_user_report_Bookmarked_Salon"])) {
			$this->LoadSelectionFromSession('Bookmarked_Salon');
		} elseif (@$_SESSION["sel_user_report_Bookmarked_Salon"] == EWR_INIT_VALUE) { // Select all
			$this->Bookmarked_Salon->SelectionList = "";
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

			// User ID
			$this->User_ID->HrefValue = "";

			// User Name
			$this->User_Name->HrefValue = "";

			// Service Name
			$this->Service_Name->HrefValue = "";

			// Ratings
			$this->Ratings->HrefValue = "";

			// Price
			$this->Price->HrefValue = "";

			// Bookmarked Salon
			$this->Bookmarked_Salon->HrefValue = "";
		} else {

			// User ID
			$this->User_ID->ViewValue = $this->User_ID->CurrentValue;
			$this->User_ID->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// User Name
			$this->User_Name->ViewValue = $this->User_Name->CurrentValue;
			$this->User_Name->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Service Name
			$this->Service_Name->ViewValue = $this->Service_Name->CurrentValue;
			$this->Service_Name->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Ratings
			$this->Ratings->ViewValue = $this->Ratings->CurrentValue;
			$this->Ratings->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Price
			$this->Price->ViewValue = $this->Price->CurrentValue;
			$this->Price->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Bookmarked Salon
			$this->Bookmarked_Salon->ViewValue = $this->Bookmarked_Salon->CurrentValue;
			$this->Bookmarked_Salon->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// User ID
			$this->User_ID->HrefValue = "";

			// User Name
			$this->User_Name->HrefValue = "";

			// Service Name
			$this->Service_Name->HrefValue = "";

			// Ratings
			$this->Ratings->HrefValue = "";

			// Price
			$this->Price->HrefValue = "";

			// Bookmarked Salon
			$this->Bookmarked_Salon->HrefValue = "";
		}

		// Call Cell_Rendered event
		if ($this->RowType == EWR_ROWTYPE_TOTAL) { // Summary row
		} else {

			// User ID
			$CurrentValue = $this->User_ID->CurrentValue;
			$ViewValue = &$this->User_ID->ViewValue;
			$ViewAttrs = &$this->User_ID->ViewAttrs;
			$CellAttrs = &$this->User_ID->CellAttrs;
			$HrefValue = &$this->User_ID->HrefValue;
			$LinkAttrs = &$this->User_ID->LinkAttrs;
			$this->Cell_Rendered($this->User_ID, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

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

			// Ratings
			$CurrentValue = $this->Ratings->CurrentValue;
			$ViewValue = &$this->Ratings->ViewValue;
			$ViewAttrs = &$this->Ratings->ViewAttrs;
			$CellAttrs = &$this->Ratings->CellAttrs;
			$HrefValue = &$this->Ratings->HrefValue;
			$LinkAttrs = &$this->Ratings->LinkAttrs;
			$this->Cell_Rendered($this->Ratings, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Price
			$CurrentValue = $this->Price->CurrentValue;
			$ViewValue = &$this->Price->ViewValue;
			$ViewAttrs = &$this->Price->ViewAttrs;
			$CellAttrs = &$this->Price->CellAttrs;
			$HrefValue = &$this->Price->HrefValue;
			$LinkAttrs = &$this->Price->LinkAttrs;
			$this->Cell_Rendered($this->Price, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Bookmarked Salon
			$CurrentValue = $this->Bookmarked_Salon->CurrentValue;
			$ViewValue = &$this->Bookmarked_Salon->ViewValue;
			$ViewAttrs = &$this->Bookmarked_Salon->ViewAttrs;
			$CellAttrs = &$this->Bookmarked_Salon->CellAttrs;
			$HrefValue = &$this->Bookmarked_Salon->HrefValue;
			$LinkAttrs = &$this->Bookmarked_Salon->LinkAttrs;
			$this->Cell_Rendered($this->Bookmarked_Salon, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);
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
		if ($this->User_ID->Visible) $this->DtlFldCount += 1;
		if ($this->User_Name->Visible) $this->DtlFldCount += 1;
		if ($this->Service_Name->Visible) $this->DtlFldCount += 1;
		if ($this->Ratings->Visible) $this->DtlFldCount += 1;
		if ($this->Price->Visible) $this->DtlFldCount += 1;
		if ($this->Bookmarked_Salon->Visible) $this->DtlFldCount += 1;
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

			// Set/clear dropdown for field Ratings
			if ($this->PopupName == 'user_report_Ratings' && $this->PopupValue <> "") {
				if ($this->PopupValue == EWR_INIT_VALUE)
					$this->Ratings->DropDownValue = EWR_ALL_VALUE;
				else
					$this->Ratings->DropDownValue = $this->PopupValue;
				$bRestoreSession = FALSE; // Do not restore
			} elseif ($this->ClearExtFilter == 'user_report_Ratings') {
				$this->SetSessionDropDownValue(EWR_INIT_VALUE, '', 'Ratings');
			}

			// Set/clear dropdown for field Bookmarked Salon
			if ($this->PopupName == 'user_report_Bookmarked_Salon' && $this->PopupValue <> "") {
				if ($this->PopupValue == EWR_INIT_VALUE)
					$this->Bookmarked_Salon->DropDownValue = EWR_ALL_VALUE;
				else
					$this->Bookmarked_Salon->DropDownValue = $this->PopupValue;
				$bRestoreSession = FALSE; // Do not restore
			} elseif ($this->ClearExtFilter == 'user_report_Bookmarked_Salon') {
				$this->SetSessionDropDownValue(EWR_INIT_VALUE, '', 'Bookmarked_Salon');
			}

		// Reset search command
		} elseif (@$_GET["cmd"] == "reset") {

			// Load default values
			$this->SetSessionDropDownValue($this->Ratings->DropDownValue, $this->Ratings->SearchOperator, 'Ratings'); // Field Ratings
			$this->SetSessionDropDownValue($this->Bookmarked_Salon->DropDownValue, $this->Bookmarked_Salon->SearchOperator, 'Bookmarked_Salon'); // Field Bookmarked Salon

			//$bSetupFilter = TRUE; // No need to set up, just use default
		} else {
			$bRestoreSession = !$this->SearchCommand;

			// Field Ratings
			if ($this->GetDropDownValue($this->Ratings)) {
				$bSetupFilter = TRUE;
			} elseif ($this->Ratings->DropDownValue <> EWR_INIT_VALUE && !isset($_SESSION['sv_user_report_Ratings'])) {
				$bSetupFilter = TRUE;
			}

			// Field Bookmarked Salon
			if ($this->GetDropDownValue($this->Bookmarked_Salon)) {
				$bSetupFilter = TRUE;
			} elseif ($this->Bookmarked_Salon->DropDownValue <> EWR_INIT_VALUE && !isset($_SESSION['sv_user_report_Bookmarked_Salon'])) {
				$bSetupFilter = TRUE;
			}
			if (!$this->ValidateForm()) {
				$this->setFailureMessage($gsFormError);
				return $sFilter;
			}
		}

		// Restore session
		if ($bRestoreSession) {
			$this->GetSessionDropDownValue($this->Ratings); // Field Ratings
			$this->GetSessionDropDownValue($this->Bookmarked_Salon); // Field Bookmarked Salon
		}

		// Call page filter validated event
		$this->Page_FilterValidated();

		// Build SQL
		$this->BuildDropDownFilter($this->Ratings, $sFilter, $this->Ratings->SearchOperator, FALSE, TRUE); // Field Ratings
		$this->BuildDropDownFilter($this->Bookmarked_Salon, $sFilter, $this->Bookmarked_Salon->SearchOperator, FALSE, TRUE); // Field Bookmarked Salon

		// Save parms to session
		$this->SetSessionDropDownValue($this->Ratings->DropDownValue, $this->Ratings->SearchOperator, 'Ratings'); // Field Ratings
		$this->SetSessionDropDownValue($this->Bookmarked_Salon->DropDownValue, $this->Bookmarked_Salon->SearchOperator, 'Bookmarked_Salon'); // Field Bookmarked Salon

		// Setup filter
		if ($bSetupFilter) {

			// Field Ratings
			$sWrk = "";
			$this->BuildDropDownFilter($this->Ratings, $sWrk, $this->Ratings->SearchOperator);
			ewr_LoadSelectionFromFilter($this->Ratings, $sWrk, $this->Ratings->SelectionList, $this->Ratings->DropDownValue);
			$_SESSION['sel_user_report_Ratings'] = ($this->Ratings->SelectionList == "") ? EWR_INIT_VALUE : $this->Ratings->SelectionList;

			// Field Bookmarked Salon
			$sWrk = "";
			$this->BuildDropDownFilter($this->Bookmarked_Salon, $sWrk, $this->Bookmarked_Salon->SearchOperator);
			ewr_LoadSelectionFromFilter($this->Bookmarked_Salon, $sWrk, $this->Bookmarked_Salon->SelectionList, $this->Bookmarked_Salon->DropDownValue);
			$_SESSION['sel_user_report_Bookmarked_Salon'] = ($this->Bookmarked_Salon->SelectionList == "") ? EWR_INIT_VALUE : $this->Bookmarked_Salon->SelectionList;
		}

		// Field Ratings
		ewr_LoadDropDownList($this->Ratings->DropDownList, $this->Ratings->DropDownValue);

		// Field Bookmarked Salon
		ewr_LoadDropDownList($this->Bookmarked_Salon->DropDownList, $this->Bookmarked_Salon->DropDownValue);
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
		$this->GetSessionValue($fld->DropDownValue, 'sv_user_report_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so_user_report_' . $parm);
	}

	// Get filter values from session
	function GetSessionFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->SearchValue, 'sv_user_report_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so_user_report_' . $parm);
		$this->GetSessionValue($fld->SearchCondition, 'sc_user_report_' . $parm);
		$this->GetSessionValue($fld->SearchValue2, 'sv2_user_report_' . $parm);
		$this->GetSessionValue($fld->SearchOperator2, 'so2_user_report_' . $parm);
	}

	// Get value from session
	function GetSessionValue(&$sv, $sn) {
		if (array_key_exists($sn, $_SESSION))
			$sv = $_SESSION[$sn];
	}

	// Set dropdown value to session
	function SetSessionDropDownValue($sv, $so, $parm) {
		$_SESSION['sv_user_report_' . $parm] = $sv;
		$_SESSION['so_user_report_' . $parm] = $so;
	}

	// Set filter values to session
	function SetSessionFilterValues($sv1, $so1, $sc, $sv2, $so2, $parm) {
		$_SESSION['sv_user_report_' . $parm] = $sv1;
		$_SESSION['so_user_report_' . $parm] = $so1;
		$_SESSION['sc_user_report_' . $parm] = $sc;
		$_SESSION['sv2_user_report_' . $parm] = $sv2;
		$_SESSION['so2_user_report_' . $parm] = $so2;
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
		$_SESSION["sel_user_report_$parm"] = "";
		$_SESSION["rf_user_report_$parm"] = "";
		$_SESSION["rt_user_report_$parm"] = "";
	}

	// Load selection from session
	function LoadSelectionFromSession($parm) {
		$fld = &$this->fields($parm);
		$fld->SelectionList = @$_SESSION["sel_user_report_$parm"];
		$fld->RangeFrom = @$_SESSION["rf_user_report_$parm"];
		$fld->RangeTo = @$_SESSION["rt_user_report_$parm"];
	}

	// Load default value for filters
	function LoadDefaultFilters() {

		/**
		* Set up default values for non Text filters
		*/

		// Field Ratings
		$this->Ratings->DefaultDropDownValue = EWR_INIT_VALUE;
		if (!$this->SearchCommand) $this->Ratings->DropDownValue = $this->Ratings->DefaultDropDownValue;
		$sWrk = "";
		$this->BuildDropDownFilter($this->Ratings, $sWrk, $this->Ratings->SearchOperator, TRUE);
		ewr_LoadSelectionFromFilter($this->Ratings, $sWrk, $this->Ratings->DefaultSelectionList);
		if (!$this->SearchCommand) $this->Ratings->SelectionList = $this->Ratings->DefaultSelectionList;

		// Field Bookmarked Salon
		$this->Bookmarked_Salon->DefaultDropDownValue = EWR_INIT_VALUE;
		if (!$this->SearchCommand) $this->Bookmarked_Salon->DropDownValue = $this->Bookmarked_Salon->DefaultDropDownValue;
		$sWrk = "";
		$this->BuildDropDownFilter($this->Bookmarked_Salon, $sWrk, $this->Bookmarked_Salon->SearchOperator, TRUE);
		ewr_LoadSelectionFromFilter($this->Bookmarked_Salon, $sWrk, $this->Bookmarked_Salon->DefaultSelectionList);
		if (!$this->SearchCommand) $this->Bookmarked_Salon->SelectionList = $this->Bookmarked_Salon->DefaultSelectionList;

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

		// Field Ratings
		// $this->Ratings->DefaultSelectionList = array("val1", "val2");
		// Field Bookmarked Salon
		// $this->Bookmarked_Salon->DefaultSelectionList = array("val1", "val2");

	}

	// Check if filter applied
	function CheckFilter() {

		// Check Ratings extended filter
		if ($this->NonTextFilterApplied($this->Ratings))
			return TRUE;

		// Check Ratings popup filter
		if (!ewr_MatchedArray($this->Ratings->DefaultSelectionList, $this->Ratings->SelectionList))
			return TRUE;

		// Check Bookmarked Salon extended filter
		if ($this->NonTextFilterApplied($this->Bookmarked_Salon))
			return TRUE;

		// Check Bookmarked Salon popup filter
		if (!ewr_MatchedArray($this->Bookmarked_Salon->DefaultSelectionList, $this->Bookmarked_Salon->SelectionList))
			return TRUE;
		return FALSE;
	}

	// Show list of filters
	function ShowFilterList() {
		global $ReportLanguage;

		// Initialize
		$sFilterList = "";

		// Field Ratings
		$sExtWrk = "";
		$sWrk = "";
		$this->BuildDropDownFilter($this->Ratings, $sExtWrk, $this->Ratings->SearchOperator);
		if (is_array($this->Ratings->SelectionList))
			$sWrk = ewr_JoinArray($this->Ratings->SelectionList, ", ", EWR_DATATYPE_NUMBER, 0, $this->DBID);
		$sFilter = "";
		if ($sExtWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sExtWrk</span>";
		elseif ($sWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sWrk</span>";
		if ($sFilter <> "")
			$sFilterList .= "<div><span class=\"ewFilterCaption\">" . $this->Ratings->FldCaption() . "</span>" . $sFilter . "</div>";

		// Field Bookmarked Salon
		$sExtWrk = "";
		$sWrk = "";
		$this->BuildDropDownFilter($this->Bookmarked_Salon, $sExtWrk, $this->Bookmarked_Salon->SearchOperator);
		if (is_array($this->Bookmarked_Salon->SelectionList))
			$sWrk = ewr_JoinArray($this->Bookmarked_Salon->SelectionList, ", ", EWR_DATATYPE_STRING, 0, $this->DBID);
		$sFilter = "";
		if ($sExtWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sExtWrk</span>";
		elseif ($sWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sWrk</span>";
		if ($sFilter <> "")
			$sFilterList .= "<div><span class=\"ewFilterCaption\">" . $this->Bookmarked_Salon->FldCaption() . "</span>" . $sFilter . "</div>";
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

		// Field Ratings
		$sWrk = "";
		$sWrk = ($this->Ratings->DropDownValue <> EWR_INIT_VALUE) ? $this->Ratings->DropDownValue : "";
		if (is_array($sWrk))
			$sWrk = implode("||", $sWrk);
		if ($sWrk <> "")
			$sWrk = "\"sv_Ratings\":\"" . ewr_JsEncode2($sWrk) . "\"";
		if ($sWrk == "") {
			$sWrk = ($this->Ratings->SelectionList <> EWR_INIT_VALUE) ? $this->Ratings->SelectionList : "";
			if (is_array($sWrk))
				$sWrk = implode("||", $sWrk);
			if ($sWrk <> "")
				$sWrk = "\"sel_Ratings\":\"" . ewr_JsEncode2($sWrk) . "\"";
		}
		if ($sWrk <> "") {
			if ($sFilterList <> "") $sFilterList .= ",";
			$sFilterList .= $sWrk;
		}

		// Field Bookmarked Salon
		$sWrk = "";
		$sWrk = ($this->Bookmarked_Salon->DropDownValue <> EWR_INIT_VALUE) ? $this->Bookmarked_Salon->DropDownValue : "";
		if (is_array($sWrk))
			$sWrk = implode("||", $sWrk);
		if ($sWrk <> "")
			$sWrk = "\"sv_Bookmarked_Salon\":\"" . ewr_JsEncode2($sWrk) . "\"";
		if ($sWrk == "") {
			$sWrk = ($this->Bookmarked_Salon->SelectionList <> EWR_INIT_VALUE) ? $this->Bookmarked_Salon->SelectionList : "";
			if (is_array($sWrk))
				$sWrk = implode("||", $sWrk);
			if ($sWrk <> "")
				$sWrk = "\"sel_Bookmarked_Salon\":\"" . ewr_JsEncode2($sWrk) . "\"";
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

		// Field Ratings
		$bRestoreFilter = FALSE;
		if (array_key_exists("sv_Ratings", $filter)) {
			$sWrk = $filter["sv_Ratings"];
			if (strpos($sWrk, "||") !== FALSE)
				$sWrk = explode("||", $sWrk);
			$this->SetSessionDropDownValue($sWrk, @$filter["so_Ratings"], "Ratings");
			$bRestoreFilter = TRUE;
		}
		if (array_key_exists("sel_Ratings", $filter)) {
			$sWrk = $filter["sel_Ratings"];
			$sWrk = explode("||", $sWrk);
			$this->Ratings->SelectionList = $sWrk;
			$_SESSION["sel_user_report_Ratings"] = $sWrk;
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Ratings"); // Clear drop down
			$bRestoreFilter = TRUE;
		}
		if (!$bRestoreFilter) { // Clear filter
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Ratings");
			$this->Ratings->SelectionList = "";
			$_SESSION["sel_user_report_Ratings"] = "";
		}

		// Field Bookmarked Salon
		$bRestoreFilter = FALSE;
		if (array_key_exists("sv_Bookmarked_Salon", $filter)) {
			$sWrk = $filter["sv_Bookmarked_Salon"];
			if (strpos($sWrk, "||") !== FALSE)
				$sWrk = explode("||", $sWrk);
			$this->SetSessionDropDownValue($sWrk, @$filter["so_Bookmarked_Salon"], "Bookmarked_Salon");
			$bRestoreFilter = TRUE;
		}
		if (array_key_exists("sel_Bookmarked_Salon", $filter)) {
			$sWrk = $filter["sel_Bookmarked_Salon"];
			$sWrk = explode("||", $sWrk);
			$this->Bookmarked_Salon->SelectionList = $sWrk;
			$_SESSION["sel_user_report_Bookmarked_Salon"] = $sWrk;
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Bookmarked_Salon"); // Clear drop down
			$bRestoreFilter = TRUE;
		}
		if (!$bRestoreFilter) { // Clear filter
			$this->SetSessionDropDownValue(EWR_INIT_VALUE, "", "Bookmarked_Salon");
			$this->Bookmarked_Salon->SelectionList = "";
			$_SESSION["sel_user_report_Bookmarked_Salon"] = "";
		}
	}

	// Return popup filter
	function GetPopupFilter() {
		$sWrk = "";
		if ($this->DrillDown)
			return "";
		if (!$this->DropDownFilterExist($this->Ratings, $this->Ratings->SearchOperator)) {
			if (is_array($this->Ratings->SelectionList)) {
				$sFilter = ewr_FilterSQL($this->Ratings, "`Ratings`", EWR_DATATYPE_NUMBER, $this->DBID);

				// Call Page Filtering event
				$this->Page_Filtering($this->Ratings, $sFilter, "popup");
				$this->Ratings->CurrentFilter = $sFilter;
				ewr_AddFilter($sWrk, $sFilter);
			}
		}
		if (!$this->DropDownFilterExist($this->Bookmarked_Salon, $this->Bookmarked_Salon->SearchOperator)) {
			if (is_array($this->Bookmarked_Salon->SelectionList)) {
				$sFilter = ewr_FilterSQL($this->Bookmarked_Salon, "`Bookmarked Salon`", EWR_DATATYPE_STRING, $this->DBID);

				// Call Page Filtering event
				$this->Page_Filtering($this->Bookmarked_Salon, $sFilter, "popup");
				$this->Bookmarked_Salon->CurrentFilter = $sFilter;
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
				$this->User_ID->setSort("");
				$this->User_Name->setSort("");
				$this->Service_Name->setSort("");
				$this->Ratings->setSort("");
				$this->Price->setSort("");
				$this->Bookmarked_Salon->setSort("");
			}

		// Check for an Order parameter
		} elseif (@$_GET["order"] <> "") {
			$this->CurrentOrder = ewr_StripSlashes(@$_GET["order"]);
			$this->CurrentOrderType = @$_GET["ordertype"];
			$this->UpdateSort($this->User_ID, $bCtrl); // User ID
			$this->UpdateSort($this->User_Name, $bCtrl); // User Name
			$this->UpdateSort($this->Service_Name, $bCtrl); // Service Name
			$this->UpdateSort($this->Ratings, $bCtrl); // Ratings
			$this->UpdateSort($this->Price, $bCtrl); // Price
			$this->UpdateSort($this->Bookmarked_Salon, $bCtrl); // Bookmarked Salon
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
if (!isset($user_report_rpt)) $user_report_rpt = new cruser_report_rpt();
if (isset($Page)) $OldPage = $Page;
$Page = &$user_report_rpt;

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
var user_report_rpt = new ewr_Page("user_report_rpt");

// Page properties
user_report_rpt.PageID = "rpt"; // Page ID
var EWR_PAGE_ID = user_report_rpt.PageID;

// Extend page with Chart_Rendering function
user_report_rpt.Chart_Rendering = 
 function(chart, chartid) { // DO NOT CHANGE THIS LINE!

 	//alert(chartid);
 }

// Extend page with Chart_Rendered function
user_report_rpt.Chart_Rendered = 
 function(chart, chartid) { // DO NOT CHANGE THIS LINE!

 	//alert(chartid);
 }
</script>
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<script type="text/javascript">

// Form object
var CurrentForm = fuser_reportrpt = new ewr_Form("fuser_reportrpt");

// Validate method
fuser_reportrpt.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);

	// Call Form Custom Validate event
	if (!this.Form_CustomValidate(fobj))
		return false;
	return true;
}

// Form_CustomValidate method
fuser_reportrpt.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }
<?php if (EWR_CLIENT_VALIDATE) { ?>
fuser_reportrpt.ValidateRequired = true; // Uses JavaScript validation
<?php } else { ?>
fuser_reportrpt.ValidateRequired = false; // No JavaScript validation
<?php } ?>

// Use Ajax
fuser_reportrpt.Lists["sv_Ratings"] = {"LinkField":"sv_Ratings","Ajax":true,"DisplayFields":["sv_Ratings","","",""],"ParentFields":[],"FilterFields":[],"Options":[],"Template":""};
fuser_reportrpt.Lists["sv_Bookmarked_Salon"] = {"LinkField":"sv_Bookmarked_Salon","Ajax":true,"DisplayFields":["sv_Bookmarked_Salon","","",""],"ParentFields":[],"FilterFields":[],"Options":[],"Template":""};
</script>
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown) { ?>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
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
<form name="fuser_reportrpt" id="fuser_reportrpt" class="form-inline ewForm ewExtFilterForm" action="<?php echo ewr_CurrentPage() ?>">
<?php $SearchPanelClass = ($Page->Filter <> "") ? " in" : " in"; ?>
<div id="fuser_reportrpt_SearchPanel" class="ewSearchPanel collapse<?php echo $SearchPanelClass ?>">
<input type="hidden" name="cmd" value="search">
<div id="r_1" class="ewRow">
<div id="c_Ratings" class="ewCell form-group">
	<label for="sv_Ratings" class="ewSearchCaption ewLabel"><?php echo $Page->Ratings->FldCaption() ?></label>
	<span class="ewSearchField">
<?php ewr_PrependClass($Page->Ratings->EditAttrs["class"], "form-control"); ?>
<select data-table="user_report" data-field="x_Ratings" data-value-separator="<?php echo ewr_HtmlEncode(is_array($Page->Ratings->DisplayValueSeparator) ? json_encode($Page->Ratings->DisplayValueSeparator) : $Page->Ratings->DisplayValueSeparator) ?>" id="sv_Ratings" name="sv_Ratings"<?php echo $Page->Ratings->EditAttributes() ?>>
<option value=""><?php echo $ReportLanguage->Phrase("PleaseSelect") ?></option>
<?php
	$cntf = is_array($Page->Ratings->AdvancedFilters) ? count($Page->Ratings->AdvancedFilters) : 0;
	$cntd = is_array($Page->Ratings->DropDownList) ? count($Page->Ratings->DropDownList) : 0;
	$totcnt = $cntf + $cntd;
	$wrkcnt = 0;
	if ($cntf > 0) {
		foreach ($Page->Ratings->AdvancedFilters as $filter) {
			if ($filter->Enabled) {
				$selwrk = ewr_MatchedFilterValue($Page->Ratings->DropDownValue, $filter->ID) ? " selected" : "";
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
<option value="<?php echo $Page->Ratings->DropDownList[$i] ?>"<?php echo $selwrk ?>><?php echo ewr_DropDownDisplayValue($Page->Ratings->DropDownList[$i], "", 0) ?></option>
<?php
		$wrkcnt += 1;
	}
?>
</select>
<?php
$Page->Ratings->LookupSql = "SELECT DISTINCT `Ratings`, `Ratings` AS `DispFld` FROM `user report`";
$sWhereWrk = "";

// Call Lookup selecting
$Page->Lookup_Selecting($Page->Ratings, $sWhereWrk);
if ($sWhereWrk <> "") $Page->Ratings->LookupSql .= " WHERE " . $sWhereWrk;
$Page->Ratings->LookupSql .= " ORDER BY `Ratings`";
?>
<input type="hidden" name="s_sv_Ratings" id="s_sv_Ratings" value="s=<?php echo ewr_Encrypt($Page->Ratings->LookupSql) ?>&amp;f0=<?php echo ewr_Encrypt("`Ratings` = {filter_value}"); ?>&amp;t0=131&amp;ds=&amp;df=0&amp;dlm=<?php echo ewr_Encrypt($Page->Ratings->FldDelimiter) ?>&amp;d=DB"></span>
</div>
</div>
<div id="r_2" class="ewRow">
<div id="c_Bookmarked_Salon" class="ewCell form-group">
	<label for="sv_Bookmarked_Salon" class="ewSearchCaption ewLabel"><?php echo $Page->Bookmarked_Salon->FldCaption() ?></label>
	<span class="ewSearchField">
<?php ewr_PrependClass($Page->Bookmarked_Salon->EditAttrs["class"], "form-control"); ?>
<select data-table="user_report" data-field="x_Bookmarked_Salon" data-value-separator="<?php echo ewr_HtmlEncode(is_array($Page->Bookmarked_Salon->DisplayValueSeparator) ? json_encode($Page->Bookmarked_Salon->DisplayValueSeparator) : $Page->Bookmarked_Salon->DisplayValueSeparator) ?>" id="sv_Bookmarked_Salon" name="sv_Bookmarked_Salon"<?php echo $Page->Bookmarked_Salon->EditAttributes() ?>>
<option value=""><?php echo $ReportLanguage->Phrase("PleaseSelect") ?></option>
<?php
	$cntf = is_array($Page->Bookmarked_Salon->AdvancedFilters) ? count($Page->Bookmarked_Salon->AdvancedFilters) : 0;
	$cntd = is_array($Page->Bookmarked_Salon->DropDownList) ? count($Page->Bookmarked_Salon->DropDownList) : 0;
	$totcnt = $cntf + $cntd;
	$wrkcnt = 0;
	if ($cntf > 0) {
		foreach ($Page->Bookmarked_Salon->AdvancedFilters as $filter) {
			if ($filter->Enabled) {
				$selwrk = ewr_MatchedFilterValue($Page->Bookmarked_Salon->DropDownValue, $filter->ID) ? " selected" : "";
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
<option value="<?php echo $Page->Bookmarked_Salon->DropDownList[$i] ?>"<?php echo $selwrk ?>><?php echo ewr_DropDownDisplayValue($Page->Bookmarked_Salon->DropDownList[$i], "", 0) ?></option>
<?php
		$wrkcnt += 1;
	}
?>
</select>
<?php
$Page->Bookmarked_Salon->LookupSql = "SELECT DISTINCT `Bookmarked Salon`, `Bookmarked Salon` AS `DispFld` FROM `user report`";
$sWhereWrk = "";

// Call Lookup selecting
$Page->Lookup_Selecting($Page->Bookmarked_Salon, $sWhereWrk);
if ($sWhereWrk <> "") $Page->Bookmarked_Salon->LookupSql .= " WHERE " . $sWhereWrk;
$Page->Bookmarked_Salon->LookupSql .= " ORDER BY `Bookmarked Salon`";
?>
<input type="hidden" name="s_sv_Bookmarked_Salon" id="s_sv_Bookmarked_Salon" value="s=<?php echo ewr_Encrypt($Page->Bookmarked_Salon->LookupSql) ?>&amp;f0=<?php echo ewr_Encrypt("`Bookmarked Salon` = {filter_value}"); ?>&amp;t0=200&amp;ds=&amp;df=0&amp;dlm=<?php echo ewr_Encrypt($Page->Bookmarked_Salon->FldDelimiter) ?>&amp;d=DB"></span>
</div>
</div>
<div class="ewRow"><input type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary" value="<?php echo $ReportLanguage->Phrase("Search") ?>">
<input type="reset" name="btnreset" id="btnreset" class="btn hide" value="<?php echo $ReportLanguage->Phrase("Reset") ?>"></div>
</div>
</form>
<script type="text/javascript">
fuser_reportrpt.Init();
fuser_reportrpt.FilterList = <?php echo $Page->GetFilterList() ?>;
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
<?php if ($Page->User_ID->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="User_ID"><div class="user_report_User_ID"><span class="ewTableHeaderCaption"><?php echo $Page->User_ID->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="User_ID">
<?php if ($Page->SortUrl($Page->User_ID) == "") { ?>
		<div class="ewTableHeaderBtn user_report_User_ID">
			<span class="ewTableHeaderCaption"><?php echo $Page->User_ID->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer user_report_User_ID" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->User_ID) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->User_ID->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->User_ID->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->User_ID->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->User_Name->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="User_Name"><div class="user_report_User_Name"><span class="ewTableHeaderCaption"><?php echo $Page->User_Name->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="User_Name">
<?php if ($Page->SortUrl($Page->User_Name) == "") { ?>
		<div class="ewTableHeaderBtn user_report_User_Name">
			<span class="ewTableHeaderCaption"><?php echo $Page->User_Name->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer user_report_User_Name" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->User_Name) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->User_Name->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->User_Name->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->User_Name->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Service_Name->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Service_Name"><div class="user_report_Service_Name"><span class="ewTableHeaderCaption"><?php echo $Page->Service_Name->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Service_Name">
<?php if ($Page->SortUrl($Page->Service_Name) == "") { ?>
		<div class="ewTableHeaderBtn user_report_Service_Name">
			<span class="ewTableHeaderCaption"><?php echo $Page->Service_Name->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer user_report_Service_Name" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Service_Name) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Service_Name->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Service_Name->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Service_Name->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Ratings->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Ratings"><div class="user_report_Ratings"><span class="ewTableHeaderCaption"><?php echo $Page->Ratings->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Ratings">
<?php if ($Page->SortUrl($Page->Ratings) == "") { ?>
		<div class="ewTableHeaderBtn user_report_Ratings">
			<span class="ewTableHeaderCaption"><?php echo $Page->Ratings->FldCaption() ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'user_report_Ratings', false, '<?php echo $Page->Ratings->RangeFrom; ?>', '<?php echo $Page->Ratings->RangeTo; ?>');" id="x_Ratings<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer user_report_Ratings" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Ratings) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Ratings->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Ratings->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Ratings->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'user_report_Ratings', false, '<?php echo $Page->Ratings->RangeFrom; ?>', '<?php echo $Page->Ratings->RangeTo; ?>');" id="x_Ratings<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Price->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Price"><div class="user_report_Price"><span class="ewTableHeaderCaption"><?php echo $Page->Price->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Price">
<?php if ($Page->SortUrl($Page->Price) == "") { ?>
		<div class="ewTableHeaderBtn user_report_Price">
			<span class="ewTableHeaderCaption"><?php echo $Page->Price->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer user_report_Price" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Price) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Price->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Price->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Price->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Bookmarked_Salon->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Bookmarked_Salon"><div class="user_report_Bookmarked_Salon"><span class="ewTableHeaderCaption"><?php echo $Page->Bookmarked_Salon->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Bookmarked_Salon">
<?php if ($Page->SortUrl($Page->Bookmarked_Salon) == "") { ?>
		<div class="ewTableHeaderBtn user_report_Bookmarked_Salon">
			<span class="ewTableHeaderCaption"><?php echo $Page->Bookmarked_Salon->FldCaption() ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'user_report_Bookmarked_Salon', false, '<?php echo $Page->Bookmarked_Salon->RangeFrom; ?>', '<?php echo $Page->Bookmarked_Salon->RangeTo; ?>');" id="x_Bookmarked_Salon<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer user_report_Bookmarked_Salon" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Bookmarked_Salon) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Bookmarked_Salon->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Bookmarked_Salon->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Bookmarked_Salon->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
			<a class="ewTableHeaderPopup" title="<?php echo $ReportLanguage->Phrase("Filter"); ?>" onclick="ewr_ShowPopup.call(this, event, 'user_report_Bookmarked_Salon', false, '<?php echo $Page->Bookmarked_Salon->RangeFrom; ?>', '<?php echo $Page->Bookmarked_Salon->RangeTo; ?>');" id="x_Bookmarked_Salon<?php echo $Page->Cnt[0][0]; ?>"><span class="icon-filter"></span></a>
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
<?php if ($Page->User_ID->Visible) { ?>
		<td data-field="User_ID"<?php echo $Page->User_ID->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_user_report_User_ID"<?php echo $Page->User_ID->ViewAttributes() ?>><?php echo $Page->User_ID->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->User_Name->Visible) { ?>
		<td data-field="User_Name"<?php echo $Page->User_Name->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_user_report_User_Name"<?php echo $Page->User_Name->ViewAttributes() ?>><?php echo $Page->User_Name->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Service_Name->Visible) { ?>
		<td data-field="Service_Name"<?php echo $Page->Service_Name->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_user_report_Service_Name"<?php echo $Page->Service_Name->ViewAttributes() ?>><?php echo $Page->Service_Name->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Ratings->Visible) { ?>
		<td data-field="Ratings"<?php echo $Page->Ratings->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_user_report_Ratings"<?php echo $Page->Ratings->ViewAttributes() ?>><?php echo $Page->Ratings->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Price->Visible) { ?>
		<td data-field="Price"<?php echo $Page->Price->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_user_report_Price"<?php echo $Page->Price->ViewAttributes() ?>><?php echo $Page->Price->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Bookmarked_Salon->Visible) { ?>
		<td data-field="Bookmarked_Salon"<?php echo $Page->Bookmarked_Salon->CellAttributes() ?>>
<span data-class="tpx<?php echo $Page->RecCount ?>_<?php echo $Page->RecCount ?>_user_report_Bookmarked_Salon"<?php echo $Page->Bookmarked_Salon->ViewAttributes() ?>><?php echo $Page->Bookmarked_Salon->ListViewValue() ?></span></td>
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
<?php include "user_reportrptpager.php" ?>
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
