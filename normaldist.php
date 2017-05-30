<!DOCTYPE HTML>
<html>
<head></head>
<body>
	<form method="POST" action="normaldist.php">
		<table>
			<tr><td>id paragraf test:</td><td><input type="text" name="idptest"></td></tr>
			<tr><td>k</td><td><input type="text" name="k"></td></tr>
			<tr><td>z</td><td><input type="text" name="z" placeholder="di php nya jadi minus"></td></tr>		
			<tr>
				<td><input value="reset" type="button" onclick="window.location.href='cekbatas.php'; " ></td>
				<td><button name="ok" type="submit">ok</button></td>
			</tr>
		</table>
	</form>
</body>
</html>
<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";

	if(isset($_POST["idptest"])){
		$idptest = $_POST["idptest"];
		$idptest_stop = $idptest+40;
		$k = $_POST['k'];
		$z = $_POST['z'];

		// 1. ambil seluruh data percobaan paragraf test
		while($idptest < $idptest_stop)
		{
			// 1. ambil seluruh data percobaan paragraf test
			$sql = "SELECT idpref,frobn,slice,pad from tbscore where idptest=$idptest";
			$query = $db->Execute($sql);

			// 2. simpan di array masing-masing
			$frobnorm = array();
			$slicedeg = array();
			$paddeg = array();
			while (!$query->EOF){
				$frobnorm[$query->fields["idpref"]] = abs(100-$query->fields["frobn"]);
				$slicedeg[$query->fields["idpref"]] = $query->fields["slice"];
				$paddeg[$query->fields["idpref"]] = $query->fields["pad"];
				$query->MoveNext();
			}
			// print("<br>"); print_r($frobnorm);
			// print("<br>"); print_r($slicedeg);
			// print("<br>"); print_r($paddeg);

			// 3. cek masing-masing nilai terhadap batas yang ditentukan
			$mean_fnorm = array_sum($frobnorm)/count($frobnorm);
			$std_fnorm = sd($frobnorm);
			//echo "<h5>Frob Norm</h5><table border='1'>";
			foreach ($frobnorm as $key => $value) {			
				//echo "<tr><td>$idptest vs $key</td><td>".$value."</td><td>".($mean_fnorm - $std_fnorm)*1.2;
				if ($value < ($mean_fnorm - $z*$std_fnorm)*$k) $stfnorm = "plagiat";
				else $stfnorm = "tidak";
				//echo "</td><td>".$stfnorm."</td></tr>";
				$sqlstatus = "insert into tbstatus (idptest,idpref,stfnorm) values ($idptest,$key,'$stfnorm')";
				$rsstatus = $db->Execute($sqlstatus);
			}

			//echo "</table><h5>Slice</h5><table border='1'>";
			$mean_sliced = array_sum($slicedeg)/count($slicedeg);
			$std_sliced = sd($slicedeg);
			foreach ($slicedeg as $key => $value) {
				//echo "<tr><td>$idptest vs $key</td><td>".$value."</td><td>".($mean_sliced - $std_sliced)*1.2;
				if ($value < ($mean_sliced - $z*$std_sliced)*$k) $stslice = "plagiat";
				else $stslice = "tidak";
				//echo "</td><td>".$stslice."</td></tr>";
				$sqlstatus = "UPDATE tbstatus SET stslice='$stslice' WHERE idpref=$key AND idptest=$idptest";
				$rsstatus = $db->Execute($sqlstatus);
			}

			//echo "</table><h5>Pad</h5><table border='1'>";
			$mean_padd = array_sum($paddeg)/count($paddeg);				
			$std_padd = sd($paddeg);
			foreach ($paddeg as $key => $value) {
				//echo "<tr><td>$idptest vs $key</td><td>".$value."</td><td>".($mean_padd - $std_padd)*1.2;
				if ($value < ($mean_padd - $z*$std_padd)*$k) $stpad = "plagiat";
				else $stpad = "tidak";
				//echo "</td><td>".$stpad."</td></tr>";
				$sqlstatus = "UPDATE tbstatus SET stpad='$stpad' WHERE idpref=$key AND idptest=$idptest";
				$rsstatus = $db->Execute($sqlstatus);
			}		

			$idptest++;
		}
	}

	// Function to calculate square of value - mean
	function sd_square($x, $mean) { 
		return pow($x - $mean,2); 
	}

	// Function to calculate standard deviation (uses sd_square)   
	function sd($array) {
	   
		// square root of sum of squares devided by N-1
		return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	} 
?>