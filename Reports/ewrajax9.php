<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start();
?>
<?php include_once "phprptinc/ewrcfg9.php" ?>
<?php include_once ((EW_USE_ADODB) ? "adodb5/adodb.inc.php" : "phprptinc/ewmysql.php") ?>
<?php include_once "phprptinc/ewrfn9.php" ?>
<?php include_once "phprptinc/ewrusrfn9.php" ?>
<?php
ewr_Header(FALSE, 'utf-8');
$lookup = new crlookup;
$lookup->Page_Main();

//
// Page class for lookup
//
class crlookup {

	// Page ID
	var $PageID = "lookup";

	// Project ID
	var $ProjectID = "{3C488A29-896C-4371-BB76-02105C3C2105}";

	// Page object name
	var $PageObjName = "lookup";

	// Page name
	function PageName() {
		return ewr_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		return ewr_CurrentPage() . "?";
	}

	// Main
	function Page_Main() {
		global $conn;
		global $ReportLanguage;
		$GLOBALS["Page"] = &$this;
		$post = ewr_StripSlashes($_POST);
		if (count($post) == 0)
			die("Missing post data.");

		//$sql = $qs->getValue("s");
		$sql = @$post["s"];
		$sql = ewr_Decrypt($sql);
		if ($sql == "")
			die("Missing SQL.");

		// Field delimiter
		$dlm = @$post["dlm"];
		$dlm = ewr_Decrypt($dlm);

		// Language object
		$ReportLanguage = new crLanguage();
		$dbid = @$post["d"];
		$conn = ewr_Connect($dbid);

		// Global Page Loading event (in userfn*.php)
		Page_Loading();
		ob_clean(); // Clear output
		if (strpos($sql, "{filter}") > 0) {
			$filters = "";
			$ar = preg_grep('/^f\d+$/', array_keys($post));
			foreach ($ar as $key) {

				// Get the filter values (for "IN")
				$filter = ewr_Decrypt(@$post[$key]);
				if ($filter <> "") {
					$i = preg_replace('/^f/', '', $key);
					$value = @$post["v" . $i];
					if ($value == "") {
						if ($i > 0) // Empty parent field

							//continue; // Allow
							ewr_AddFilter($filters, "1=0"); // Disallow
						continue;
					}
					$arValue = explode(",", $value);
					$fldtype = intval(@$post["t" . $i]);
					$wrkfilter = "";
					for ($j = 0, $cnt = count($arValue); $j < $cnt; $j++) {
						if ($wrkfilter <> "") $wrkfilter .= " OR ";
						$val = $arValue[$j];
						if ($val == EWR_NULL_VALUE)
							$wrkfilter .= str_replace(" = {filter_value}", " IS NULL", $filter);
						elseif ($val == EWR_NOT_NULL_VALUE)
							$wrkfilter .= str_replace(" = {filter_value}", " IS NOT NULL", $filter);
						elseif ($val == EWR_EMPTY_VALUE)
							$wrkfilter .= str_replace(" = {filter_value}", " = ''", $filter);
						else
							$wrkfilter .= str_replace("{filter_value}", ewr_QuotedValue($val, ewr_FieldDataType($fldtype), $this->DBID), $filter);
					}
					ewr_AddFilter($filters, $wrkfilter);
				}
			}
			$sql = str_replace("{filter}", ($filters <> "") ? $filters : "1=1", $sql);
		}

		// Get the query value (for "LIKE" or "=")
		$value = ewr_AdjustSql(@$_GET["q"], $dbid); // Get the query value from querystring
		if ($value == "") $value = ewr_AdjustSql(@$post["q"], $dbid); // Get the value from post
		if ($value <> "") {
			$sql = preg_replace('/LIKE \'(%)?\{query_value\}%\'/', ewr_Like('\'$1{query_value}%\'', $dbid), $sql);
			$sql = str_replace("{query_value}", $value, $sql);
		}

		// Replace {query_value_n}
		preg_match_all('/\{query_value_(\d+)\}/', $sql, $out);
		$cnt = count($out[0]);
		for ($i = 0; $i < $cnt; $i++) {
			$j = $out[1][$i];
			$v = ewr_AdjustSql(@$post["q" . $j], $dbid);
			$sql = str_replace("{query_value_" . $j . "}", $v, $sql);
		}
		$ds = @$post["ds"]; // Date search type
		$df = @$post["df"]; // Date format
		$this->GetLookupValues($sql, $ds, $df, $dlm, $dbid);

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();

		// Close connection
		ewr_CloseConn();
	}

	// Get lookup values
	function GetLookupValues($sql, $ds, $df, $dlm, $dbid) {
		global $ReportLanguage;
		$rsarr = array();
		$rowcnt = 0;
		if ($rs = ReportConn($dbid)->Execute($sql)) {
			$rowcnt = $rs->RecordCount();
			$fldcnt = $rs->FieldCount();
			$rsarr = $rs->GetRows();
			$rs->Close();
		}

		// Clean output buffer
		if (ob_get_length())
			ob_end_clean();

		// Output
		$key = array();
		$arr = array();
		if (is_array($rsarr) && $rowcnt > 0) {
			for ($i = 0; $i < $rowcnt; $i++) {
				$row = $rsarr[$i];
				if ($dlm <> "") {
					$cnt = 0;
					for ($j = 0; $j < $fldcnt; $j++) {
						if (strpos(strval($row[$j]), $dlm) !== FALSE) {
							$row[$j] = explode($dlm, $row[$j]);
							if (count($row[$j]) > $cnt) $cnt = count($row[$j]);
						} else {
							if ($cnt < 1) $cnt = 1;
						}
					}
				} else {
					$cnt = 1;
				}
				for ($k = 0; $k < $cnt; $k++) {
					$val0 = "";
					$str0 = "";
					$rec = array();
					for ($j = 0; $j < $fldcnt; $j++) {
						if ($dlm <> "" && is_array($row[$j])) {
							if (count($row[$j]) > $k)
								$val = $row[$j][$k];
							else
								$val = $row[$j][0];
						} else {
							$val = $row[$j];
						}
						if ($j == 0) {
							$str = ewr_ConvertValue($ds, $val);
							$val0 = $val;
							$str0 = $str;
						} elseif ($j == 1 && is_null($val0)) {
							$str = $ReportLanguage->Phrase("NullLabel");
						} elseif ($j == 1 && strval($val0) == "") {
							$str = $ReportLanguage->Phrase("EmptyLabel");
						} elseif ($j == 1) {
							$str = ewr_DropDownDisplayValue(ewr_ConvertValue($ds, $val), $ds, $df);
						} else {
							$str = strval($val);
						}
						$str = ewr_ConvertToUtf8($str);
						$rec[$j] = $str;
					}
					if (!in_array($str0, $key)) {
						$arr[] = $rec;
						$key[] = $str0;
					}
				}
			}
		}
		echo ewr_ArrayToJson($arr);
	}
}
?>
