<?php
	function strclean($text)
	{
		$kalimat="";
		$kalimat = str_replace(".", "", $text);
		$kalimat = str_replace(",", "", $kalimat);
		$kalimat = str_replace("/", " ", $kalimat);
		$kalimat = str_replace("(", "", $kalimat);
		$kalimat = str_replace(")", "", $kalimat);
		$kalimat = str_replace(";", "", $kalimat);
                $kalimat = str_replace(chr(9), " ", $kalimat);
		return trim($kalimat);
	}

	function choptext($startidx,$maxindex,$kal) //memotong per 10 kata, $startidx mulai dgn 0
	{
		$rets="";		
		$scope = $startidx + 9;
		
		while (($startidx<=$scope) && ($startidx<=$maxindex))
		{
			$rets = $rets." ".$kal[$startidx];
			$startidx++;	
		}
		return $rets;
	}
	
	
	function matlab_exec($strcommand)
	{
		$m1 = new COM("Matlab.Application") or die ("connection create fail");
		//$result = com_invoke ( $m1,'Execute' , "$strcommand");
		$result = $m1 -> Execute("$strcommand");
		if(substr($result,1,3)=="???")
    	{
        	$result = "ERROR...";
        	exit ;
    	}
    	
    	$result = str_replace("ans =","",$result);
		return $result;
	}

	function scale_range($score)
	{
		if ($score>80)
			$alp = 'A';
		elseif (($score<=80) && ($score>70))		
			$alp = 'B';
		elseif (($score<=70) && ($score>50))		
			$alp = 'C';	
		elseif (($score<=50) && ($score>40))		
			$alp = 'D';	
		else
			$alp = 'E';	
		
		return $alp;	
	}

?>	