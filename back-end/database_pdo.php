<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/back-end/constants.php";
/*
UPDATE LOG
JRS_20170602 - support for different databases added; updated SatoshiCommands to use database "satoshi_prod"
JRS_20170530 - Added SatoshiCommands


REQUIRES:
define("DB_SERVER"                    , "localhost");
define("DB_USER"                      , "root");
define("DB_PASS"                      , "");
define("DB_NAME"                      , "coincafe");


DOCUMENTATION
▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
USING DATABASE OBJECT:

	▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
	 SINGLE RECORD
	▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬

		CREATE New Record (does an INSERT on save. To get the new primary key, call $USER->Save() and then check $USER->PK())
			$USER = new User();

		LOAD Record (based on primary key; does an UPDATE on save)
			$USER = new User(50);

		READ Column Data
			$name = $USER->first_name;

		READ Primary Key
			$pk = $USER->PK();

		READ All Fields
			$all_data = $USER->Data()

		READ Changed Data
			$changed_data = $USER->ChangedData()

		SET/UPDATE Column Data (call Save() to write to DB)
			$USER->first_name = "bill";

		SAVE Data to Database
			$USER->Save();

		DELETE A Record (if deleted is enabled in class declaration)
			$USER->Delete();



	▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
	 RECORD COLLECTIONS
	▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬

		Loading Data
			// Default Selects ALL Fields/Records
			$TRANSACTIONS = new TransactionsCollection();

			// Custom WHERE
			$TRANSACTIONS = new TransactionsCollection(array
			(	"where"    => "user_id = 24989"
			));

			// Custom SELECT
			// "*" is default if "select" is left out
			// Primary key must be included or it won't structure data properly (won't work)
			$TRANSACTIONS = new TransactionsCollection(array
			(	"select"   => "transaction_id, user_id, balance_curr, balance_prev"
			,	"where"    => "user_id = 24989"
			));

			// Custom Extended Query Options Variables
			$TRANSACTIONS = new TransactionsCollection(array
			(	"select"   => "transaction_id, user_id, balance_curr, balance_prev"
			,	"where"    => "user_id = 24989"
			,	"extended" => "ORDER BY transaction_id DESC LIMIT 50"
			));

			// Joining Tables
			$SDN = new SDNCheckCollection(array
			(	'select'   => '*, sdn_check.id AS sdn_check_id, sdn_check.admin_notes AS sdn_admin_notes'
			,	'join'     => 'LEFT JOIN tbl_member ON tbl_member.id = sdn_check.user_id'
			,	'where'    => $filter
			));

--->		// REQUIRED - SQL INJECTION PROTECTION
			// WEB FORM DATA WITHIN SQL QUERIES - assume all form data contains sql injection code
			//   (hacking tools submit data to the website. it doesn't matter what kind of form validation is running on the front end)
			// Using PHP PDO within a query
			$TRANSACTIONS = new TransactionsCollection(array
			(	"select"   => "transaction_id, user_id, balance_curr, balance_prev"
			,	"where"    => array
				(	"where_pdo"  => "walletaddress_sentto = :sentto AND amount = :amount" // normal query with each variable :tagged for replacement
				,	":sentto"    => $form_sentto  // each :variable itemized
				,	":amount"    => $form_amount
				)
			,	"extended" => "ORDER BY transaction_id DESC LIMIT 50"
			));

			// Using SQL Commands
			$USER_BALANCES = new UserCollection(array
			(	"select"  => "sum as SUM('balance_curr')"
			)

		Accessing Data Sets

		IMPORTANT: Access data with OBJECT syntax
		"Rows" and "RowsKeyed" are an ARRAY of OBJECTS, e.g.:
			php syntax
				array:  $my_array[123]
				object: $my_object->trans_id
			$date = $TRANSACTIONS->Rows[0]->date_created
			$date = $TRANSACTIONS->Rows[1]->date_created
			$date = $TRANSACTIONS->Rows[2]->date_created
			$date = $TRANSACTIONS->RowsKeyed[1040]->date_created
			$date = $TRANSACTIONS->RowsKeyed[2041]->date_created
			$date = $TRANSACTIONS->RowsKeyed[1234]->date_created

		Access Full Arrays
			$all_results       = $TRANSACTIONS->Rows;
			$all_results_keyed = $TRANSACTIONS->RowsKeyed; //(array key id is also = the row's primary key id)

		Access Single Row
			$single_result     = $TRANSACTIONS->Rows[0];

		Access Data Within a Row
			$transaction_id    = $TRANSACTIONS->Rows[0]->id;

		Iterate Through Results
			foreach($TRANSACTIONS->Rows as $index => $row){
				$transaction_id = $row->transaction_id; // primary key
				$user           = $row->user_id;
				$wallet         = $row->walletaddress_sentto;
			}
			foreach($TRANSACTIONS->RowsKeyed as $primary_key => $row){
				$transaction_id = $primary_key;
				$user           = $row->user_id;
				$wallet         = $row->walletaddress_sentto;
			}


		Helper Functions
			$data_set->PrintTable()                  // output table
			$data_set->FirstPK                       // first primary key of the first result row (instead of $data_set->Rows[0]->primary_key)
			$data_set->FirstRow->name                // first row of the first results set (instead of $data_set->Rows[0]->name)
			$data_set->ColumnSet('first_name')       // list of all first_names in the data set
			$data_set->ColumnSet('first_name', true) // list of all first_names in the data set but index by primary key
			$data_set->DownloadCSV()                 // download the results in the browser


		Tricks:
			Assign a row to a local variable to reference data more clearly
				complex:
					if($TRANSACTIONS->Rows[10]->user_id == $form_id || $TRANSACTIONS->Rows[10]->email == $form_email){
				simple:
					$trans = $TRANSACTIONS->Rows[10];
					if($trans->user_id == $form_id || $trans->email == $form_email){
			SUM
				$sum = array_sum($data_set->ColummSet('amount'));



▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
 IMPORT TO DOCUMENTATION
▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬

"BATCH" (under R&D)
	Purpose:
		Easily rollback DB changes if a rollback situation is encountered
		This is an important logic bug safeguard
	Operation:
		Make changes to data, once save is finalized, commit all changes
	Use Case:
		complex function starts updating a reference object, (for instance User Object)
		error situation is encountered (something that couldn't easily be tested for before the update process starts)
		changes must be aborted
		instead of having a DB call revert data
	UPGRADE:
		reconsider implementation engineering to simply use a ->Revert() function to restore original DB
			(I believe this was vetoed to simplify error return situations. The function could simply be abandoned without special handling.)
	JRS_20180822:
		Note: adding table history and better error reporting has made this obsolete. consider removing from codebase.

▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬


▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
 PENDING IMPROVEMENTS
▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
 - update class structure for no table data duplication; not a big deal in the table definitions, but it could be cleaner

▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬
/**/


class DBAccess {
	protected $DB;
	protected $ErrorCode       = null;
	protected $ErrorInfo       = null;
	private   $Exception       = null;
	public    $Errors          = ""; // Friendly Name
	public    $ConnectionError = "";

	// Establish Database Connection
	// $assert_errors
	//    true  - normal for production (stop execution of script)
	//    false - special case for the error to be returned
	// ATTR_PERSISTENT
	//    Save time with a persistent DB connection between script calls.
	//    JRS_20161028 - Persistent DB connections ONLY if PDO ODBC driver/libraries ARE NOT using ODBC Connection
	//       Pooling (PDO_MYSQL driver *should not* be using connection pooling)
	//    JRS_20170320 - It would seem that without this enabled, at least on my local environment (WAMP), each instance
	//       of this class opens a port (slow and undesirable). it seems to crash at about 4000 calls? enabling this
	//       seems to have fixed that. I believe this has been happening on the server as well
	//    JRS_20170923 turning off to check how it affects database load
	//       (it was enabled, but during a DDOS attack, DB connections maxed out and crashed the server for 12 hours. it
	//       was difficult to know exactly how to recover maxed out DB connections. it eventually self-recovered.)
	//
	// ErrorInfo array looks like:
	//    (   0 => [SQLState error code]
	//    ,   1 => [Driver-specific error code]
	//    ,   2 => [Driver-specific error message]
	//    )
	protected function __construct($assert_errors = true, $dbname = null) {
		try{
			$dbname = $dbname ? $dbname : DB_NAME;
			@$this->DB = new PDO
			(	("mysql:host=" . DB_SERVER . ";dbname={$dbname};charset=utf8;")
				,	DB_USER
				,	DB_PASS
				,	array
				(	PDO::ATTR_ERRMODE          => PDO::ERRMODE_WARNING
				,	PDO::ATTR_EMULATE_PREPARES => false
					//,	PDO::ATTR_PERSISTENT       => true // ^ note above
				)
			);

			// CHECK: General Connection Error
			$this->ErrorCode = $this->DB->ErrorCode();
			$this->ErrorInfo = $this->DB->ErrorInfo(); // ^ notes above
			if($this->ErrorInfo[1]){
				$this->ConnectionError = "Database Error. See log for details";
				if($assert_errors) $this->DBFatalError();
			}
		}
			// CHECK: Fatal Connection Error
		catch(Exception $e) {
			$this->Exception = $e;
			$this->ConnectionError = "Database Access Error. Check host connection string, database name and error log.";
			if($assert_errors) $this->DBFatalError();
		}
	}

	// Check if the connection was successful or not
	function Connected(){
		return !(bool)$this->ConnectionError;
	}

	// Usage Errors
	protected function UsageError($parent = null, $error, $data = null, $email_error = true){
		$error_log = "[DBO] {$error}";

		// Error Log
		error_log($error_log);

		// Display
		if(!SERVER_PROD) {
			echo "{$error_log}<br/>";
		}
		$this->Errors = $error;

		// prep data
		if(!is_array($data)){
			$data = array('data' => $data);
		}

		// *** UPGRADE: report errors to table error log, regular emails report errors instead of each error being reported live
		// ACTION: Email Tech/Admin
		if(!$email_error){
			error_log("\nDB UsageError: Function {$parent}, Error: {$error}");
		}
		else if(function_exists("EMAIL_TECH")){
			$report = array_merge(array
			(	'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME']
			,	'REQUEST_URI' => $_SERVER['REQUEST_URI']
			,	'Function'    => $parent . "()"
			,	'Error'       => $error
			), $data);
			EMAIL_TECH("[DBO] UsageError", $report);
		}
		else{
			error_log("\nWARNING: database_pdo.php unable to email admin error information");
		}
	}

	// Critical Errors
	protected function ExceptionError($error, $exception){
		$this->UsageError(__FUNCTION__, $error);

		// ACTION: Email Tech/Admin
		if(function_exists("EMAIL_TECH_AND_ADMIN")){
			$trace = $this->Exception->getTrace();
			if($trace) unset($trace[0]); // Remove DB connection string information
			EMAIL_TECH_AND_ADMIN("Database Exception Error", array
			(	'error'             => $error
			,	'exception code'    => $exception->getCode()
			,	'exception message' => $exception->getMessage()
			,	'stack trace'       => print_rr($trace, false)
			));
		}
		else{
			error_log("\nWARNING: database_pdo.php unable to email admin error information");
		}
	}

	// Fatal Errors
	private function DBFatalError(){
		// CALCULATE: Error Report
		$error_display = $this->ErrorReport(false);

		// ACTION: Output Critical Error
		if(SERVER_PROD){
			echo "<html><body><h3>Website Maintenance</h3>Please check back shortly</body></html>";
		}
		else{
			echo $error_display;
		}

		// ACTION: Email Tech/Admin
		if(function_exists("EMAIL_TECH_AND_ADMIN")){
			EMAIL_TECH_AND_ADMIN("CRITICAL: Database Error", $error_display);
		}
		else{
			error_log("\nWARNING: database_pdo.php unable to email admin error information");
		}

		// ACTION: Kill Site
		exit();
	}


	function ErrorReport($echo_output = true, $echo_trace = false){
		$nice_output = function_exists("print_rr");
		$output = "<div style='color:red;border:1px solid red;padding:10px;'>";
		$output .= "<div style='font-weight:bold'>DATABASE</div>";

		// If an exception is thrown, there are no other errors
		if($this->Exception){
			$output .= "Exception Thrown<br/>";
			$output .= "CODE: " . $this->Exception->getCode() . "<br/>";
			$output .= "MESSAGE: " . $this->Exception->getMessage() . "<br/>";
			if($echo_trace){
				$trace = $this->Exception->getTrace();
				unset($trace[0]); // CRITICAL: Remove DB connection string/password from error report
				$output .= "TRACE: <br/>" . print_rr($trace, false) . "<br/>";
				$output .= "TRACE: <br/>" . ($nice_output ? print_rr($this->ErrorCode, false) : var_export($this->ErrorCode, true)) . "<br/>";
			}
		}
		else{
			$output .= "DB ErrorCode:<br/>";
			$output .= $nice_output ? print_rr($this->ErrorCode, false) : var_export($this->ErrorCode, true);
			$output .= "<br/>";
			$output .= "<br/>";
			$output .= "DB ErrorInfo:<br/>";
			$output .= $nice_output ? print_rr($this->ErrorInfo, false) : var_export($this->ErrorInfo, true);
		}
		$output .= "</div>";
		if($echo_output) echo $output;
		return $output;
	}

}

// Access root database connection to check health
// EXAMPLE:
//    $testdb = new DBRaw();
//    if(!$testdb->Connected) $testdb->ErrorReport();
class DBRaw extends DBAccess{
	public $Connected = null;
	public $DBLinkPDO = null;
	public $Error     = null;

	public function __construct(){
		parent::__construct(false);
		$this->Connected = $this->Connected();
		$this->DBLinkPDO = $this->DB;
		$this->Error     = $this->ErrorInfo;
	}
}


class DBAccessSingleItem extends DBAccess{

	// Child Classes Declare:
	/*
	protected $Table              = "tbl_members";   // REQUIRED: table name
	protected $PK                 = "id";            // REQUIRED: table primary key
	protected $AutoDateModified   = "date_modified"; // OPTIONAL: automatically update the modified date on insert/update
	protected $AutoDateCreated    = "date_created";  // OPTIONAL: automatically update the created date on insert
	protected $DeleteEnabled      = true;            // OPTIONAL: delete enabled/disabled. DEFAULT: disabled
	protected $InsertEnabled      = false;           // OPTIONAL: insert enabled/disabled. DEFAULT: enabled
	protected $UpdateEnabled      = false;           // OPTIONAL: update enabled/disabled. DEFAULT: enabled
	protected $ReadOnly           = true;            // OPTIONAL: no insert/update.        DEFAULT: disabled
	protected $DatabaseName       = "satoshi_prod";  // OPTIONAL: different database than coincafe_prod
	/**/

	protected $Data               = array();
	protected $DataOriginal       = array();
	protected $NewRecord          = false;
	protected $Changed            = false;
	protected $ChangedData        = array();
	protected $LoadError          = false;
	protected $ErrorEmails        = true;  // Currently only implemented on some Select() and Save() operations

	// JRS_20180307 - note: __construct() return value can't be checked in practice
	//   no code is relying on this directly, but if it does in the future, add another flag to check "success"
	public function __construct($key = "", $error_email_notify = null) {
		$dbname = isset($this->DatabaseName) ? $this->DatabaseName : null;
		parent::__construct(true, $dbname);

		// Error Notification
		if($error_email_notify !== null){
			$this->ErrorEmails = (bool)$error_email_notify;
		}

		if($key) {
			if(is_array($key)) {
				$OtherClass = str_replace("SingleItem", "Collection", get_called_class());
				$this->UsageError(__FUNCTION__, "ERROR: This class " . get_called_class() . " is for accessing a single item. To make a query, use class " . $OtherClass);
				return false;
			}
			if(!$this->Select($key)) {
				return false; // error returned by sub-function
			}
		}
		else {
			$this->NewRecord = true;
		}

		return true;
	}


	function __get($field) {
		if(array_key_exists($field, $this->Data)) {
			return $this->Data[$field];
		}
		else if($this->NewRecord) {
			return NULL;
		}
		else {
			$this->UsageError(__FUNCTION__, "BUG WARNING: Reference to non-existant field. Table: '{$this->Table}', Field: {$field}");
		}

		return NULL;
	}


	function __set($index, $value) {
		// Protect Primary Key
		if($index == $this->PK) {
			$this->UsageError(__FUNCTION__, "ERROR: ".get_called_class()." - Primary key can not be changed.");
			return false;
		}

		// CHECK: Invalid Type
		if(is_array($value) || is_object($value)){
			$this->UsageError(__FUNCTION__, "BUG WARNING: Array or Object to String Conversion. Table: '{$this->Table}', Field: {$index}", array('table' => $this->Table, 'field' => $index,'value' => $value));
		}

		if(array_key_exists($index, $this->Data)) {
			$this->Data[$index] = $value;

			// Set Change Flag
			if(!$this->NewRecord && ($this->Data[$index] !== $this->DataOriginal[$index])) {
				$this->Changed = true;
				$this->ChangedData[$index] = array
				(	'old' => $this->DataOriginal[$index]
				,	'new' => $this->Data[$index]
				);
			}
		}
		else if($this->NewRecord) {
			// Add New Field
			$this->Data[$index] = $value;
		}
		else {
			// Access Error
			$this->UsageError(__FUNCTION__, "BUG WARNING: Trying to set a field that doesn't exist. Table: '{$this->Table}', Field: {$index}", array('table' => $this->Table, 'field' => $index));

			return false;
		}

		return true;
	}


	private function Reset() {
		$this->Data         = array();
		$this->DataOriginal = array();
		$this->NewRecord    = false;
		$this->Changed      = false;
		$this->ChangedData  = array();
		$this->Errors       = "";
	}


	function Data() {
		return $this->Data;
	}


	// Return a list of changed data items
	function ChangedData(){
		return $this->ChangedData;
	}


	function PK() {
		return (isset($this->Data[$this->PK]) ? $this->Data[$this->PK] : NULL);
	}


	// This is only for a batch update on existing records
	function BatchStart(){
		if($this->NewRecord){
			return false;
		}
		return (object)$this->Data;
	}


	function BatchCommit($UpdateData){
		$pk = $this->PK;
		unset($UpdateData->$pk);

		foreach($UpdateData as $key => $item){
			$this->__set($key, $item);
		}

		$changes = $this->ChangedData();
		$save    = $this->Save();
		return (object)array
		(	'success' => $save
		,	'changes' => $changes
		);
	}


	protected function Select($key) {
		try {
			$stmt = $this->DB->prepare("SELECT * FROM `{$this->Table}` WHERE `{$this->PK}` = :key LIMIT 1");
			$stmt->bindValue(':key', $key);
			$stmt->execute();

			if($stmt->rowCount()) {
				foreach($stmt->fetch(PDO::FETCH_ASSOC) as $col => $val) {
					$this->Data[$col]         = $val;
					$this->DataOriginal[$col] = $val;
				}
				return true;
			}
			else {
				$this->UsageError(__FUNCTION__, "LOAD ERROR: Specified record not found. No database operations can be performed.", array
				(	'Table'       => $this->Table
				,	'Primary Key' => $this->PK
				,	'Value'       => $key
				), $this->ErrorEmails);
				$this->LoadError = true;
				$this->NewRecord = true;

				// For Data Recovery
				$this->Data[$this->PK] = $key;
				return false;
			}
		}
		catch(PDOException $X) {
			//*** this doesn't catch fatal errors from ->bindValue(). investigate what kind of errors can be thrown
			$this->ExceptionError("CRITICAL SELECT ERROR:" . $X->getMessage(), $X);
			return false;
		}
	}


	function Delete() {
		// CHECK: Valid Record
		if($this->LoadError){
			return false;
		}

		// CHECK: Delete Enabled
		if(!(isset($this->DeleteEnabled) && $this->DeleteEnabled)) {
			$this->UsageError(__FUNCTION__, "ERROR: Delete not enabled for table: {$this->Table}. Enable it by adding \"\$DeleteEnabled = true;\" in Class: " . get_called_class());
			return false;
		}

		try {
			$q    = "DELETE FROM `{$this->Table}` WHERE `{$this->PK}` = :key LIMIT 1";
			$stmt = $this->DB->prepare($q);

			$stmt->bindValue(':key', $this->Data[$this->PK]);
			$stmt->execute();
		}
		catch(PDOException $X) {
			$this->ExceptionError("CRITICAL DELETE ERROR:" . $X->getMessage(), $X);
			return false;
		}

		$this->Reset();
		return true;
	}


	function Save() {
		// CHECK: Valid Record
		if($this->LoadError){
			$this->UsageError(__FUNCTION__, " data_recovery_bandaid: Trying to save data to a record that does not exist.", array_merge(array('table' => $this->Table, '---&nbsp;recovery&nbsp;data&nbsp;below&nbsp;---' => "---"), $this->Data), $this->ErrorEmails);
			return false;
		}

		// CHECK: Read Only
		if(isset($this->ReadOnly) && $this->ReadOnly){
			$this->UsageError(__FUNCTION__, "ERROR: Read Only is enabled for table: {$this->Table}. To change, set \"\$ReadOnly = false;\" (or delete variable) in Class: " . get_called_class());
			return false;
		}

		// CHECK: Insert Blocked
		if($this->NewRecord && isset($this->InsertEnabled) && !$this->InsertEnabled){
			$this->UsageError(__FUNCTION__, "ERROR: Insert not allowed for table: {$this->Table}. To change, set \"\$InsertEnabled = true;\" (or delete variable) in Class: " . get_called_class());
			return false;
		}

		// CHECK: Update Blocked
		if(!$this->NewRecord && isset($this->UpdateEnabled) && !$this->UpdateEnabled){
			$this->UsageError(__FUNCTION__, "ERROR: Update not allowed for table: {$this->Table}. To change, set \"\$UpdateEnabled = true;\" (or delete variable) in Class: " . get_called_class());
			return false;
		}

		// CHECK: if no changes, bypass
		if(!$this->Changed && !$this->NewRecord) {
			return true;
		}

		// Automatic date fill
		if(isset($this->AutoDateModified) && $this->AutoDateModified) {
			$this->Data[$this->AutoDateModified] = date("Y-m-d H:i:s");
		}

		// Insert New Record
		if($this->NewRecord) {
			return $this->Insert();
		}
		// Update Existing Record
		else {
			return $this->Update();
		}
	}


	private function Insert(){
		// Automatic created date fill
		if(isset($this->AutoDateCreated) && $this->AutoDateCreated) {
			$this->Data[$this->AutoDateCreated] = date("Y-m-d H:i:s");
		}

		try {
			$c = "`" . implode('`,`', array_keys($this->Data)) . "`";
			$¿ = str_repeat("?,", count($this->Data) - 1) . "?";
			$q = "INSERT INTO `{$this->Table}` ($c) VALUES ($¿)";
			$r = $this->DB->prepare($q);

			if(!$r) {
				echo $q;
				$this->UsageError(__FUNCTION__, "ERROR: INSERT SQL Prepare Fail", array('query' => $q, 'data' => $this->Data));
				error_log("PHP ERROR (DBO ERROR ACTUALLY). Hard Stop. Insert() Prepare fail");
				exit; // hard errors are better than subtle bugs
			}


			$r->execute(array_values($this->Data));
			$e = $r->errorInfo();

			if($e[1]) {
				$this->UsageError(__FUNCTION__, "ERROR: INSERT SQL Execution Fail", array('query' => $q, 'data' => $this->Data, 'error' => $e), $this->ErrorEmails);
				return false;
			}

			// Save new key
			$id = $this->DB->lastInsertId();

			// Reload record for subsequent operations
			$this->Reset();
			$this->Select($id);
		}
		catch(PDOException $X) {
			$this->ExceptionError("CRITICAL INSERT ERROR:" . $X->getMessage(), $X);
			return false;
		}
		return true;
	}


	private function Update(){
		try {
			// Get New Data
			$new_data = array();
			foreach($this->Data as $col => $val){
				if($val !== $this->DataOriginal[$col]){
					$new_data[$col] = $val;
				}
			}
			if(!$new_data){
				$this->UsageError(__FUNCTION__, "WARNING: No New Data. Programmer: Investigate. It was flagged as changed but no changes were found.");
				return false;
			}

			// Get primary key
			$id = $this->Data[$this->PK];

			$s = "`" . implode('`=?,`', array_keys($new_data)) . "`=?";
			$q = "UPDATE `{$this->Table}` SET $s WHERE `{$this->PK}` = '$id'";
			$r = $this->DB->prepare($q);

			if(!$r) {
				$this->UsageError(__FUNCTION__, "ERROR: UPDATE SQL Prepare Fail", array('query' => $q, 'data' => $new_data), $this->ErrorEmails);
				error_log("PHP ERROR (DBO ERROR ACTUALLY). Hard Stop. Update() Prepare fail");
				exit(); // hard errors are better than subtle bugs
			}

			$r->execute(array_values($new_data));
			$e = $r->errorInfo();

			if($e[1]) {
				$this->UsageError(__FUNCTION__, "ERROR: UPDATE SQL Execution Fail", array('query' => $q, 'data' => $new_data, 'error' => $e), $this->ErrorEmails);
				return false;
			}

			// Save Change History
			// TODO: JRS_20180926 - probably better to splinter this off to another function
			if(isset($this->ChangeHistory)){
				global $USER;

				// CALCULATE: Basic Data
				$logged_in_user = (is_object($USER) && $USER->PK()) ? $USER->PK() : null;
				$script         = $_SERVER['SCRIPT_NAME'];
				$save_data      = $this->ChangedData;

				// CALCULATE: Data Exclusions
				//   Exclude - Date Changes
				if(isset($this->AutoDateModified)){
					unset($save_data[$this->AutoDateModified]);
				}
				//   Exclude - Explicitly Excluded
				if(isset($this->HistoryExclusions)){
					foreach($save_data as $field_name => $field_data){
						foreach($this->HistoryExclusions as $exclude){
							if($field_name == $exclude){
								unset($save_data[$field_name]);
								break;
							}
						}
					}
				}
				//   Exclude - Blank "Old" Data
				//   (When the old data is blank, there is no old data to record.)
				foreach($save_data as $field_name => $field_data){
					if($field_data['old'] === null || $field_data['old'] === ""){
						unset($save_data[$field_name]);
					}
				}
				//   Exclude - "Same" Data
				//   (This class sees "1" (string) and 1 (integer) as different.)
				foreach($save_data as $field_name => $field_data){
					if($field_data['old'] == $field_data['new']){
						unset($save_data[$field_name]);
					}
				}

				// CHECK: Still Data to Save
				if($save_data){

					// CALCULATE: Cap Max Length (prevent SQL error)
					$save_data = substr(json_encode($save_data), 0, 4096);
					$script    = substr($script, 0, 256);

					// ACTION: Save Data to Database
					$sql_data = array
					(   $this->PK()
					,   $save_data
					,   $logged_in_user
					,   $script
					,   SYSTEM_SQL_DATE
					);
					$q = "INSERT INTO `{$this->ChangeHistory}` (`record_primary_key`, `record_changed_data`, `user_logged_in`, `script_making_update`, `date_created`) VALUES (?,?,?,?,?)";
					$r = $this->DB->prepare($q);
					$r->execute($sql_data);
					$e = $r->errorInfo();

					// CHECK: Success
					if($e[1]){
						$this->UsageError(__FUNCTION__, "ERROR: CREATE SQL Execution Fail", array('query' => $q, 'data' => $sql_data, 'error' => $e), $this->ErrorEmails);
					}
				}
			}


			// Marked changes as saved
			$this->DataOriginal = $this->Data;
			$this->Changed      = false;
		}
		catch(PDOException $X) {
			$this->ExceptionError("CRITICAL UPDATE ERROR:" . $X->getMessage(), $X);
			return false;
		}
		return true;
	}

}



class DBAccessCollection extends DBAccess{
	// Child Classes Declare:
	/*
	protected $Table        = "atm_callbacks";
	protected $PK           = "id";
	protected $DatabaseName = "satoshi_prod"; // optional alternate database (default database credentials must be able to access it)
	/**/

	public $Rows      = array();
	public $RowsKeyed = array();
	public $RowCount  = 0;
	public $FirstPK   = NULL; // Frequently accessed. Shorthand for ->Rows[0]->(primary key)
	public $FirstRow  = NULL; // Shorthand for ->Rows[0]

	public function __construct($key = null) {
		$dbname = isset($this->DatabaseName) ? $this->DatabaseName : null;
		parent::__construct(true, $dbname);

		// CHECK: array
		if(!is_array($key) && $key != null) {
			$OtherClass = str_replace("Collection", "", get_called_class());
			$this->UsageError(__FUNCTION__, "ERROR: This class " . get_called_class() . " is for accessing a query. To access or create a single item, use class " . $OtherClass);
			return false;
		}

		// Load DB
		if(!$this->SetSelect($key)) {
			return false; // (error message returned by sub-function
		}

		return true;
	}

	// DOCUMENTATION: See notes above for query syntax and options
	private function SetSelect($Q) {
		$missing_pk = false;

		// Setup Query
		$query  = isset($Q['select']   ) ? " SELECT {$Q['select']} " : " SELECT * ";
		$query .= " FROM `{$this->Table}` "; // $Table from table definition
		$query .= isset($Q['join']     ) ? " {$Q['join']} " : "";
		$query .= isset($Q['where']    ) ? " WHERE " . (is_array($Q['where']) ? "{$Q['where']['where_pdo']} " : "{$Q['where']} ") : "";
		$query .= isset($Q['extended'] ) ? " {$Q['extended']} " : "";

		try {
			$result = $this->DB->prepare($query);

			if(!$result) {
				$this->UsageError(__FUNCTION__, "ERROR: COLLECTION SQL Prepare Fail", array('query' => $query));
				exit; // hard errors are better than subtle bugs
			}

			// If WHERE PDO is sent
			if(isset($Q['where']) && is_array($Q['where'])) {
				unset($Q['where']['where_pdo']);
				foreach($Q['where'] as $key => $value) {
					// Prepared where variables
					$result->bindValue($key, $value);
				}
			}

			$result->execute();
			$e = $result->errorInfo();

			if($e[1]) {
				$this->UsageError(__FUNCTION__, "ERROR: COLLECTION SQL Execute Fail", array('query' => $query, 'error' => $e));
				return false;
			}

			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				if(!array_key_exists($this->PK, $row)){
					$missing_pk = true;
					// Note: No primary key indexing! This is a bug if there are multiple rows.
					$this->RowsKeyed[] = (object)$row;
					$this->Rows[]      = (object)$row;
				}
				else{
					// Using object reference for sub-items
					$this->RowsKeyed[$row[$this->PK]] = (object)$row;
					$this->Rows[] = &$this->RowsKeyed[$row[$this->PK]]; // $PK from table definition
				}
			}
			$this->RowCount = count($this->Rows);

			if($missing_pk){
				// CASE 1: Upto 1 Row
				if($this->RowCount <= 1){
					// Acceptable.
					// No primary key is indexed but that's probably ok since this is probably running SQL commands
				}
				// CASE 2: Multiple Rows
				else{
					// Class Usage Code Error
					$this->UsageError
					(	__FUNCTION__
						,	"ERROR: Missing primary key '{$this->PK}' in the SELECT statement."
						,	array
						(	'Class'  => get_called_class()
						,	'PK'     => $this->PK
						,	'Query'  => $query
						,	'ACTION' => "To fix this, add the primary key '{$this->PK}' to the 'select' statement in PHP. e.g.<br/><br/><b>new ".get_called_class()."(array('select' => '{$this->PK}, [...the rest of your query...]'</b><br/><br/>The primary key must be included in the query for RowsKeyed to be indexed by primary key."
						,	'Note'   => "This was designed for loading data sets. If you are using fancy SQL, considering using mysqli directly."
						)
					);
					return false;
				}
			}
		}
		catch(PDOException $X) {
			$this->ExceptionError("CRITICAL SELECT ERROR:" . $X->getMessage(), $X);
			return false;
		}

		// Save First Primary Key Value
		if($this->RowCount){
			$pk = $this->PK; // $PK from table definition
			$this->FirstPK  = isset($this->Rows[0]->$pk) ? $this->Rows[0]->$pk : 0;
			$this->FirstRow = &$this->Rows[0];
		}
		return true;
	}

	public function ColumnSet($column_name, $keyed = false){
		// CHECK: Results
		if(!$this->RowCount){
			return array();
		}
		// CHECK: Column Name
		if(!isset($this->Rows[0]->$column_name)){
			return false;
		}
		$ValueList = array();
		foreach($this->RowsKeyed as $key => $row){
			if($keyed) $ValueList[$key] = $row->$column_name;
			else       $ValueList[]     = $row->$column_name;
		}
		return $ValueList;
	}

	public function TableHTML($css = true){
		$output = "No Data";
		if($this->RowCount){
			$keys = array_keys((array)$this->Rows[0]);
			$size = count($keys);

			// CALCULATE: CSS
			$style = !$css?"":"<style>.dbo_table>thead>tr>td{font-weight:bold;font-size:16px;text-align:center;}.dbo_table>tbody>tr:nth-child(odd){background-color:#eff0ff;}.dbo_table>tbody>tr>td{padding:4px;}.dbo_div{border:1px solid #CCC;border-radius:15px;font-size:14px;padding:4px 8px;}</style>";

			// CALCULATE: Header
			$thead = "<thead><tr>";
			foreach($keys as $name){
				$thead .= "<td><div class='dbo_div'>{$name}</div></td>";
			}
			$thead .= "</tr></thead>";

			// CALCULATE: Body
			$tbody = "<tbody>";
			foreach($this->Rows as $row){
				$tbody .= "<tr>";
				foreach($row as $column){
					$tbody .= "<td>{$column}</td>";
				}
				$tbody .= "</tr>";
			}
			$tbody .= "</tbody>";

			$output = "{$style}<table cellspacing='0' cellpadding='3'class='dbo_table'>{$thead}{$tbody}</table>";
		}

		return $output;
	}

	public function PrintTable($css = true){
		echo $this->TableHTML($css);
	}

	// IMPORTANT: This is an endpoint. To call this function, do not output any headers or data to the client before this.
	//    Once this is called, it will start a download for the client and end the script.
	//		Nothing will be executed after this call.
	// OPTIONAL: Specify custom header for each column, like: $header = array("First Name", "Last Name", "Balance")
	// $custom_2D_array Requirements:
	//    2 Dimensional ordered by simple number keys (0, 1, 2)
	public function DownloadCSV($header = NULL, $filename = "file.csv", $custom_2D_array = null){
		$data_src  = $custom_2D_array ? $custom_2D_array : $this->Rows;
		$row_count = count($data_src);

		reset($data_src);
		$column_keys = @array_keys((array)$data_src[key($data_src)]);
		//++ header keys to not have to be 0,1,2...

		// CHECK: Headers not Sent
		if(headers_sent()){
			track($error = "CSV File Download Error: Header data already sent to client.");
			return $error;
		}

		// CHECK: Data or a Header
		if(!$row_count && !$header){
			track($error = "CSV File Download Error: No Data to Return.");
			return $error;
		}

		// CHECK: Custom Table Header Row Count Matches Data Results
		if(is_array($header)){
			if($row_count && (count($header) != count($column_keys))){

				track($error = "CSV File Download Error: Invalid Header Count. Headers: " . count($header) . ". Columns: " . count((array)$this->Rows[0]));
				return $error;
			}
		}

		// CALCULATE: Header
		if(!is_array($header)){
			$header = $column_keys;
		}

		// CHECK: Excel Header Bug
		if($header[0] == "ID") $header[0] = 'Id'; // MS Excel bug. First entry can't be "ID" in caps


		// LOAD: File Output Buffer
		$f = fopen('php://memory', 'w');

		// LOAD: Set Header Row
		fputcsv($f, $header);

		// LOAD: Set Content Rows
		if($row_count){
			foreach ($data_src as $key => $line) {
				fputcsv($f, (array)$line);
			}
		}

		// ACTION: Send File Header Information
		header("Content-Type: text/csv");
		header("Content-Disposition: attachement; filename=\"{$filename}\";");

		// ACTION: Send File to Client
		fseek($f, 0);
		fpassthru($f);

		// ACTION: Exit (explicit requirement of a file download)
		exit();
	}
}

// CLASS DECLARATIONS
/*
// Access Single Records
class ATMCallbacks extends DBAccessSingleItem {
	protected $Table              = "atm_callbacks"; // table name
	protected $PK                 = "id";            // primary key
	protected $AutoDateModified   = "date_modified"; // OPTIONAL: automatically update the modified date on insert/update
	protected $AutoDateCreated    = "date_created";  // OPTIONAL: automatically update the created date on insert
	protected $DeleteEnabled      = true;            // OPTIONAL: delete enabled/disabled. DEFAULT: disabled
	protected $InsertEnabled      = false;           // OPTIONAL: insert enabled/disabled. DEFAULT: enabled
	protected $UpdateEnabled      = false;           // OPTIONAL: update enabled/disabled. DEFAULT: enabled
	protected $ReadOnly           = true;            // OPTIONAL: no insert/update.        DEFAULT: disabled
	protected $ErrorEmails        = false;           // OPTIONAL: default error notifications
}
// Access Multiple Records
class ATMCallbacksCollection extends DBAccessCollection {
	protected $Table              = "atm_callbacks"; // table name
	protected $PK                 = "id";            // primary key
}

//*** update class structure for efficiency and no data duplication... though... here, it's not a big deal

/**/


class Users extends DBAccessSingleItem {
	protected $Table              = "users";
	protected $PK                 = "id";
	protected $AutoDateCreated    = "date_created";
}

class UsersCollection  extends DBAccessCollection {
	protected $Table              = "users";
	protected $PK                 = "id";
}

class Rooms extends DBAccessSingleItem {
	protected $Table              = "rooms";
	protected $PK                 = "id";
	protected $AutoDateCreated    = "date_created";
}

class RoomsCollectin  extends DBAccessCollection {
	protected $Table              = "rooms";
	protected $PK                 = "id";
}