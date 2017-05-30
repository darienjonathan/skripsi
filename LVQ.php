
<html>
	<form action="LVQ.php" method="post" id="form">
		<input type="checkbox" name="f" value="f">Frobenius Norm
		<input type="checkbox" name="s" value="s">Slice
		<input type="checkbox" name="p" value="p">Pad
		<br>Alpha: <input type="text" name="alpha">
		n: <input type="text" name="n">
		<input type="submit" name="submit" value="submit">
	</form>
</html>
<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";
	if(isset($_POST['submit']))
	{
		$sql = "TRUNCATE tbstatus_lvq";
		$query = $db->Execute($sql);
		$var = 0;
		if(isset($_POST['f'])){$f = 1; $var++;}else{$f = 0;}
		if(isset($_POST['s'])){$s = 1; $var++;}else{$s = 0;}
		if(isset($_POST['p'])){$p = 1; $var++;}else{$p = 0;}
		$alpha = $_POST['alpha'];
		$n = $_POST['n'];
		//$alpha = 0.01;
		//$n = 0.05;

		$sql = "SELECT * FROM tbscore";
		$query = $db->Execute($sql);
		while(!$query->EOF)
		{	
			if($f){$frobnorm[$query->fields["idptest"]][$query->fields["idpref"]] = abs(100-$query->fields["frobn"]);}
			if($s){$slicedeg[$query->fields["idptest"]][$query->fields["idpref"]] = (float)$query->fields["slice"];}
			if($p){$paddeg[$query->fields["idptest"]][$query->fields["idpref"]] = (float)$query->fields["pad"];}
			$query->MoveNext();
		}

		$row = 0;
		$sql = "SELECT * FROM target_lvq";
		$query = $db->Execute($sql);
		$target = array();
		while(!$query->EOF)
		{
			$target[$row] = $query->fields["target"];
			$row++;
			$query->MoveNext();
		}

		$j = 2; //dimensi neuron output

		$w = array(array());
		for($k = 0; $k < $j; $k++)
		{
			if($f){$frob_w = array_slice($frobnorm,0,1);}
			if($s){$slice_w = array_slice($slicedeg,0,1);}
			if($p){$pad_w = array_slice($paddeg,0,1);}

			$count = 0;
			if($f)
			{
				$w[$k][$count] = $frob_w[0][$k+1001];
				$count++;
			}
			if($s)
			{
				$w[$k][$count] = $slice_w[0][$k+1001];	
				$count++;			
			}
			if($p)
			{
				$w[$k][$count] = $pad_w[0][$k+1001];				
			}
		}

		$count = 0;
		if($f)
		{
			foreach ($frobnorm as $key => $value) {
				foreach ($value as $key2 => $value2) {
					$data[$key][$key2][$count] = $value2;
				}
			}
			$count++;
		}

		if($s)
		{
			foreach ($slicedeg as $key => $value) {
				foreach ($value as $key2 => $value2) {
					$data[$key][$key2][$count] = $value2;
				}
			}
			$count++;
		}

		if($p)
		{
			foreach ($paddeg as $key => $value) {
				foreach ($value as $key2 => $value2) {
					$data[$key][$key2][$count] = $value2;
				}
			}
		}

		if(isset($frobnorm)){$a = $frobnorm;}
		else if(isset($slicedeg)){$a = $slicedeg;}
		else if(isset($paddeg)){$a = $paddeg;}
		
		/*TRAINING*/
		while($alpha > pow(10,-8))
		{
			$x = 0;
			foreach($a as $key => $value) {
				foreach($value as $key2 => $value2){
					for($m = 0; $m < $j; $m++){
						$distance[$m] = sqrt(array_sum(subtract($w[$m],$data[$key][$key2])));
					}
					$winner = array_search(min($distance), $distance);

					$jarak[$key][$key2] = $winner;

					if($jarak[$key][$key2] == $target[$x]){
						for($in = 0; $in < $var; $in++){
							$w[$jarak[$key][$key2]][$in] = $w[$jarak[$key][$key2]][$in] + $alpha*($data[$key][$key2][$in] - $w[$jarak[$key][$key2]][$in]);
						}
					}
					else
					{
						for($in = 0; $in < $var; $in++){
							$w[$jarak[$key][$key2]][$in] = $w[$jarak[$key][$key2]][$in] - $alpha*($data[$key][$key2][$in] - $w[$jarak[$key][$key2]][$in]);
						}						
					}
					$x++;
				}
			}
			$alpha = $alpha * $n;
		}

		/*TESTING*/
		foreach($a as $key => $value) {
			foreach($value as $key2 => $value2){
				for($m = 0; $m < $j; $m++){
					$distance[$m] = sqrt(array_sum(subtract($w[$m],$data[$key][$key2])));
				}
				$winner = array_search(min($distance), $distance);
				$jarak[$key][$key2] = $winner;
			}
		}	


		/*DATABASE*/
		$x = 0;
		foreach($jarak as $key => $value){
			foreach($value as $key2 => $value2){
				if($value2 == 0){
					$jarak[$key][$key2] = "plagiat";
					$x++;
				}
				else{
					$jarak[$key][$key2] = "tidak";
				}
				echo "$key $key2 ".$jarak[$key][$key2]."<br>";
				$sql = "INSERT INTO tbstatus_lvq (idptest, idpref, status) VALUES($key,$key2,'".$jarak[$key][$key2]."')";
				$query = $db->Execute($sql);
			}
		}
		echo $x;

	}


	function subtract($arr1,$arr2){
		$ret = array();
		foreach ($arr1 as $key => $value) {
			$ret[$key] = pow(($arr2[$key] - $arr1[$key]),2);
  		}
  		return $ret;
	}

?>