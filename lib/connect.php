<?php
define ("DB_ACCESS", "mysqli");
define ("DB_HOST", "localhost");
define ("DB_NAME", "plague_test"); //ganti balik
define ("DB_USER", "root");
define ("DB_PASSWORD", "");

// include db engine
global $dbdtk_dbaccess, $dbdtk_dbhost, $dbdtk_dbuser, $dbdtk_dbpasswd, $dbdtk_dbname;

$dbdtk_dbaccess 	= DB_ACCESS;
$dbdtk_dbhost 		= DB_HOST;
$dbdtk_dbuser 		= DB_USER;
$dbdtk_dbpasswd		= DB_PASSWORD;
$dbdtk_dbname 		= DB_NAME;

$db = &ADONewConnection($dbdtk_dbaccess);
$db->debug = 0;
$db->Connect($dbdtk_dbhost, $dbdtk_dbuser, $dbdtk_dbpasswd, $dbdtk_dbname);
$ADODB_FETCH_MODE = ADODB_FETCH_BOTH;
?>
