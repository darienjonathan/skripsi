<!DOCTYPE HTML>
<html>
<head></head>
<body>
	<form method="POST" action="cekbatas.php">
		<table>
			<tr>
				<td>Versi 1.2</td>
			</tr>
			<tr>
				<td>id paragraf test : </td>
				<td><input type="text" name="idptest"></td>
			</tr>
			<tr>
				<td><input type="Radio" name="threshold" value="mutlak">Threshold Mutlak</td>
			</tr>
			<tr>
				<td><input type="Radio" name="threshold" value="ranking">Ranking</td>
			</tr>
			<tr>
				<td><input type="Radio" name="threshold" value="LVQ">LVQ</td>
			</tr>			
			<tr>
				<td><input type="Radio" name="threshold" value="normaldist">Distribusi Normal</td>
			</tr>
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
		$threshold = $_POST["threshold"];
		$thrfnorm = $_POST["thrfnorm"];
		$thrdeg = $_POST["thrdeg"];
		$idptest_stop = $idptest+40;
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
			switch ($threshold) {
				case "mutlak":
					foreach ($frobnorm as $key => $value) {
						// batas minimal frob norm 30, sudut 20
						if($value<$thrfnorm) $stfnorm = "plagiat";
						else $stfnorm = "tidak";
						if($slicedeg[$key]<$thrdeg) $stslice = "plagiat";
						else $stslice = "tidak";
						if($paddeg[$key]<$thrdeg) $stpad = "plagiat";
						else $stpad = "tidak";

						// simpan di tbstatus
						$sqlstatus = "insert into tbstatus (idptest,idpref,stfnorm,stslice,stpad) values ($idptest,$key,'$stfnorm','$stslice','$stpad')";
						$rsstatus = $db->Execute($sqlstatus);
					}
					break;

				case "ranking":		
					// urutkan masing-masing array		
					asort($frobnorm);
					asort($slicedeg);
					asort($paddeg);
					/*
					echo "frobnorm: <br>";
					print_r($frobnorm);
					echo "<br>slicedeg: <br>";
					print_r($slicedeg);
					echo "<br>paddeg: <br>";
					print_r($paddeg);
					echo "<br>";
					*/

					// init counter dari 0 sampai 9 (10 nilai terendah)
					$state = 0;
					foreach ($frobnorm as $key => $value) {
						if($state==0) {
							$stfnorm = "plagiat"; //karena disort dr yg terendah, yg pertama pasti plagiat dong
							$state = 1;
							$batasnilai = $value;
						}
						else {
							// jika nilai selanjutnya sama dgn nilai terkecil, tetap dianggap plagiat
							if($value <= $batasnilai) {
								$stfnorm = "plagiat";
							}
							else {
								$stfnorm = "tidak";
							}
						}

						$sqlstatus = "insert into tbstatus (idptest,idpref,stfnorm) values ($idptest,$key,'$stfnorm')";
						$rsstatus = $db->Execute($sqlstatus);
					}
					$state = 0;
					foreach ($slicedeg as $key => $value) {
						if($state==0) {
							$stslice = "plagiat";
							$state = 1;
							$batasnilai = $value;
						}
						else {
							if($value <= $batasnilai) {
								$stslice = "plagiat";
							}
							else {
								$stslice = "tidak";
							}
						}

						$sqlstatus = "UPDATE tbstatus SET stslice='$stslice' WHERE idpref=$key";
						$rsstatus = $db->Execute($sqlstatus);
					}
					$state = 0;
					foreach ($paddeg as $key => $value) {
						if($state==0) {
							$stpad = "plagiat";
							$state = 1;
							$batasnilai = $value;
						}
						else {
							if($value <= $batasnilai) {
								$stpad = "plagiat";
							}
							else {
								$stpad = "tidak";
							}
						}

						$sqlstatus = "UPDATE tbstatus SET stpad='$stpad' WHERE idpref=$key";
						$rsstatus = $db->Execute($sqlstatus);
					}
					break;

				case "LVQ":
					$w[0][0] = 90.2156;
					$w[0][1] = 11.8117;
					$w[0][2] = 14.1595;
					$w[1][0] = 21.7091;
					$w[1][1] = 39.1458;
					$w[1][2] = 43.4205;		
					foreach ($frobnorm as $key => $value) {
						$data[$key][0] = $value;
					}
					foreach ($slicedeg as $key => $value) {
						$data[$key][1] = $value;
					}
					foreach ($paddeg as $key => $value) {
						$data[$key][2] = $value;
					}
					foreach($frobnorm as $key => $value) {
						for($j=0;$j<2;$j++){
							$distance[$j] = sqrt(pow(array_sum(subtract($w[$j],$data[$key])),2));
						}
						echo array_search(min($distance), $distance);
						echo "<br>";
						var_dump($distance);
						echo "<br>";
					}
					break;

				case "normaldist":
					$mean_fnorm = array_sum($frobnorm)/count($frobnorm);
					$std_fnorm = sd($frobnorm);
					//echo "<h5>Frob Norm</h5><table border='1'>";
					foreach ($frobnorm as $key => $value) {			
						//echo "<tr><td>$idptest vs $key</td><td>".$value."</td><td>".($mean_fnorm - $std_fnorm)*1.2;
						if ($value < ($mean_fnorm - 1.1*$std_fnorm)*0.8) $stfnorm = "plagiat";
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
						if ($value < ($mean_sliced - 1.1*$std_sliced)*0.8) $stslice = "plagiat";
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
						if ($value < ($mean_padd - 1.1*$std_padd)*0.8) $stpad = "plagiat";
						else $stpad = "tidak";

						//echo "</td><td>".$stpad."</td></tr>";
						$sqlstatus = "UPDATE tbstatus SET stpad='$stpad' WHERE idpref=$key AND idptest=$idptest";
						$rsstatus = $db->Execute($sqlstatus);
					}
					//echo "</table>";
					// $mean_fnorm = array_sum($frobnorm)/count($frobnorm);
					// $std_fnorm = sd($frobnorm);
					// foreach ($frobnorm as $key => $value) {
					// 	$frobnorm[$key] = ($value-$mean_fnorm)/$std_fnorm;
					// 	// print("<br>"); print("$key");
					// 	// print(", "); print($frobnorm[$key]);
					// }

					// $mean_sliced = array_sum($slicedeg)/count($slicedeg);
					// $std_sliced = sd($slicedeg);
					// foreach ($slicedeg as $key => $value) {
					// 	$slicedeg[$key] = ($value-$mean_sliced)/$std_sliced;
					// 	// print("<br>"); print("$key");
					// 	// print(", "); print($slicedeg[$key]);
					// }

					// $mean_padd = array_sum($paddeg)/count($paddeg);				
					// $std_padd = sd($paddeg);
					// foreach ($paddeg as $key => $value) {
					// 	$paddeg[$key] = ($value-$mean_padd)/$std_padd;
					// 	// print("<br>"); print("$key");
					// 	// print(", "); print($paddeg[$key]);
					// }

					// // urutkan masing-masing array		
					// asort($frobnorm);
					// asort($slicedeg);
					// asort($paddeg);

					// // init counter dari 0 sampai 9 (10 nilai terendah)
					// $counter = 0;
					// foreach ($frobnorm as $key => $value) {
					// 	if($counter<10) {
					// 		$stfnorm = "plagiat";
					// 	}
					// 	else {
					// 		// jika nilai selanjutnya sama dgn nilai ke10, tetap dianggap plagiat
					// 		if($value <= $batasnilai) {
					// 			$stfnorm = "plagiat";
					// 		}
					// 		else {
					// 			$stfnorm = "tidak";
					// 		}
					// 	}
					// 	if($counter==9) $batasnilai = $value;
					// 	$counter++;

					// 	$sqlstatus = "insert into tbstatus (idptest,idpref,stfnorm) values ($idptest,$key,'$stfnorm')";
					// 	$rsstatus = $db->Execute($sqlstatus);
					// }
					// $counter = 0;
					// foreach ($slicedeg as $key => $value) {
					// 	if($counter<10) {
					// 		$stslice = "plagiat";
					// 	}
					// 	else {
					// 		if($value <= $batasnilai) {
					// 			$stslice = "plagiat";
					// 		}
					// 		else {
					// 			$stslice = "tidak";
					// 		}
					// 	}
					// 	if($counter==9) $batasnilai = $value;
					// 	$counter++;

					// 	$sqlstatus = "UPDATE tbstatus SET stslice='$stslice' WHERE idpref=$key";
					// 	$rsstatus = $db->Execute($sqlstatus);
					// }
					// $counter = 0;
					// foreach ($paddeg as $key => $value) {
					// 	if($counter<10) {
					// 		$stpad = "plagiat";
					// 	}
					// 	else {
					// 		if($value <= $batasnilai) {
					// 			$stpad = "plagiat";
					// 		}
					// 		else {
					// 			$stpad = "tidak";
					// 		}
					// 	}
					// 	if($counter==9) $batasnilai = $value;
					// 	$counter++;

					// 	$sqlstatus = "UPDATE tbstatus SET stpad='$stpad' WHERE idpref=$key";
					// 	$rsstatus = $db->Execute($sqlstatus);
					// }
					
					break;

				default:
					# code...
					break;
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

	function subtract($arr1,$arr2){
		$ret = array();
		foreach ($arr1 as $key => $value) {
			$ret[$key] = $arr2[$key] - $arr1[$key];
  		}
  		return $ret;
	}
?>