<!DOCTYPE html>
<html>
<body>

<form method="POST" action=insert.php>
Text id:<br>
<input type="text" name="idtext">
<br>
Text :<br>
<textarea name="txt" cols="50" rows="10">
</textarea>
<br>
<button name="ok" type="submit">OK</button>
</form>
</body>
</html>
<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";

	$idtext = $_POST["idtext"];
	$txt = $_POST["txt"];

	$txt_in_p = preg_split('/\t/', $txt, -1, PREG_SPLIT_NO_EMPTY);

	foreach ($txt_in_p as $key => $value) {
		$id = $idtext + $key;
		echo $id;
		echo $value;
		$sql = "insert into tested (idtext, testedtext) values ($id, '$value')";
		$query = $db->Execute($sql);
	}


?>