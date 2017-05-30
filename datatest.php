<html>
	<form action="datatest.php" method="post" id="form">
		Besaran Normaldist yang mau di AND in sama LVQ:<br>
		<input type="checkbox" name="f" value="f">Frobenius Norm
		<input type="checkbox" name="s" value="s">Slice
		<input type="checkbox" name="p" value="p">Pad
		<br><br>
		<input type="submit" name="submit" value="submit">
	</form>
</html>
<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";
	if(isset($_POST['submit']))
	{
		$sql = "SELECT * FROM tbstatus_lvq ORDER by idptest ASC";
		$query = $db->Execute($sql);
		while(!$query->EOF)
		{
			$status[$query->fields['idptest']][$query->fields['idpref']] = $query->fields['status'];
			$query->MoveNext();
		}

		if(isset($_POST['f'])){$f = 1;}else{$f = 0;}
		if(isset($_POST['s'])){$s = 1;}else{$s = 0;}
		if(isset($_POST['p'])){$p = 1;}else{$p = 0;}

		$sql = "SELECT * FROM tbstatus ORDER BY idptest ASC";
		$query = $db->Execute($sql);
		while(!$query->EOF)
		{	
			echo $query->fields['idptest']." ".$query->fields['idpref']." ".$query->fields['stfnorm']." ".$query->fields['stslice']." ".$query->fields['stpad']."<br>";
			if($f){$stfnorm[$query->fields["idptest"]][$query->fields["idpref"]] = $query->fields["stfnorm"];}
			if($s){$stslice[$query->fields["idptest"]][$query->fields["idpref"]] = $query->fields["stslice"];}
			if($p){$stpad[$query->fields["idptest"]][$query->fields["idpref"]] = $query->fields["stpad"];}
			$query->MoveNext();
		}

		$x = 0;

		/*Pilih sesuai pilihan normaldist*/
		foreach($status as $key => $value){
			foreach($value as $key2 => $value2){
				if($f == 1 && $s == 0 && $p == 0)
				{
					if($value2 == $stfnorm[$key][$key2] && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				else if($f == 0 && $s == 1 && $p == 0)
				{
					if($value2 == $stslice[$key][$key2] && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				else if($f == 0 && $s == 0 && $p == 1)
				{
					if($value2 == $stpad[$key][$key2] && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				else if($f == 1 && $s == 1 && $p == 0)
				{
					if($value2 == $stfnorm[$key][$key2] && $value2 == $stslice[$key][$key2] && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				else if($f == 1 && $s == 0 && $p == 1)
				{
					if($value2 == $stfnorm[$key][$key2] && $value2 == $stpad[$key][$key2] && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				else if($f == 0 && $s == 1 && $p == 1)
				{
					if($value2 == $stslice[$key][$key2] && $value2 == $stpad[$key][$key2] && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				else if($f == 1 && $s == 1 && $p == 1)
				{
					if($value2 == $stfnorm[$key][$key2] && $value2 == $stslice[$key][$key2] && $value2 == $stpad[$key][$key2]  && $value2 == "plagiat"){
						$indikasi[$x] = "plagiat";
					}
					else{
						$indikasi[$x] = "tidak";
					}
				}
				$sql = "INSERT INTO tbstatus_and (idptest, idpref, status) VALUES($key,$key2,'".$indikasi[$x]."')";
				$query = $db->Execute($sql);
				$x++;																								
			}
		}
	}
?>