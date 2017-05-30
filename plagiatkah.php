<!DOCTYPE HTML>
<html>
<head></head>
<body>
	<form method="POST" action="plagiatkah.php">
		<table>
			<tr>
				<td>Versi 1.2</td>
			</tr>
			<tr>
				<td>id paragraf test : </td>
				<td><input type="text" name="idptest"></td>
			</tr>
			<tr>
				<td><input value="reset" type="button" onclick="window.location.href='plagiatkah.php'; " ></td>
				<td><button name="ok" type="submit">ok</button></td>
			</tr>
		</table>
	</form>
</body>
<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";

	if(isset($_POST['idptest']))
	{
		/*query data hasil plagiarisme*/
		$idptest = $_POST['idptest'];
		$query = "SELECT idptest,idpref FROM tbstatus_z WHERE idptest > $idptest AND idptest < $idptest+100 AND idpref > 5000 AND (stfnorm = 'plagiat' OR stslice = 'plagiat' OR stpad = 'plagiat')";
		$sql = $db->Execute($query);
		$array_hasil = array(array());
		$i = 0;
		while(!$sql->EOF)
		{	
			$array_hasil[$i][0] = $sql->fields['idptest'];
			$array_hasil[$i][1] = $sql->fields['idpref'];
			$sql->MoveNext();
			$i++;
		}
		/*query jumlah paragraf di paper tersebut*/
		$query = "SELECT COUNT(idtext) FROM tested WHERE idtext > $idptest AND idtext < $idptest+100";
		$sql = $db->Execute($query);
		$test_count = sizeof($array_hasil);
		$paragraph_count = $sql->fields['COUNT(idtext)'];

		/*Ngebuat Arraynya jadi $idptest -> list $idref yang plagiat*/
		$temp = 0;
		for($j = 0; $j < $i; $j++)
		{
			foreach($array_hasil[$j] as $key => $value)
			{
				if($key == 0)
				{
					if($value != $temp)
					{
						$temp = $value;
						$k = 0;
					} 
				}
				if($key == 1)
				{
					$test[$temp][$k] = $value;				
					$k++;
				}
				$test[$temp][$k] = $k;
			}
		}

		$persen = sizeof($test)/$paragraph_count*100;
		echo "<h4>PAPER $idptest</h4>";
		echo "Plagiat: $persen %<br><br>";


		/*Nampilin hasil array yang ud diganti*/
		echo "<table border='1'><thead><td>Paragraf Terdeteksi Plagiat</td><td>Jumlah Deteksi</td></thead>";
		foreach($test as $key => $value){
			echo "<tr><td>$key</td>";
			echo "<td>".end($value)."</td>";
			for($i = 0; $i < end($value); $i++){
				echo "<td>".$value[$i]."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";


	}
?>