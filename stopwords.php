<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";

	$sql = "SELECT * from stopwords WHERE idstopwords='ende'";
	$query = $db->Execute($sql);

	$stopwords = $query->fields["words"];

	$stopwords = explode("\n",$stopwords);

	foreach($stopwords as $key){
		echo $key."<br>";
	}
	//print_r($stopwords);
?>