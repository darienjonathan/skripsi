<?php
function lsaMatrixGenerator($pilih, $pilihGlobal, $jawaban, $kunci, $bobot) {
	echo "Kunci: <br>";
	print_r($kunci);
	echo "<br>";
	end($kunci);
	$jumlahKunci = key($kunci) + 1;
	reset($kunci);
	echo "<br>Junlah Kunci: ".$jumlahKunci."<br>";
	$jawabSplit = array_chunk($jawaban, 10); //array_chunk: bikin array jd multidimensi, e.g. $array size x di array_chunk($array,10) -> $array[x/10][10].
	$jumlahBaris = count($jawabSplit); 
	echo("<br>Chunk:<br>");
	print_r($jawabSplit);
	echo("<br>");
	//print_r($kunci);
	//print_r($bobot);
	echo "<br>Jumlah Baris: ".$jumlahBaris."<br>";
	//LSA MATRIX GENERATOR: $pilih = ? (1); $pilihGlobal = ? (1); $jawaban = bwt ngisi matriks; $kunci = keyword; $bobot = ? ($array kosong)
	
	$bobotGlobal = array();
	switch($pilihGlobal) {
		case 0:
			for ($i=0;$i<$jumlahBaris;$i++) {
				$count = array_count_values($jawabSplit[$i]);
				for ($j=0;$j<$jumlahKunci;$j++) {
					if (isset($count[$kunci[$j]])) $bobotGlobal[$i] += 1;
					else $bobotGlobal[$i] += 0;
				}
				$bobotGlobal[$i] = log($jumlahBaris / $bobotGlobal[$i], 10);
			}
			break;
		case 1:
			for ($i=0;$i<$jumlahBaris;$i++) $bobotGlobal[$i] = 1;
			echo "<br>Bobot Global: <br>";
			print_r($bobotGlobal);

	}

	$matrix = array(array());
	for ($i=0;$i<$jumlahBaris;$i++) {
		echo "<h4>Baris ke $i</h4>";
		$count = array_count_values($jawabSplit[$i]); //array_count_values: count kalo isi array ad yg sama. e.g. $x[5] = {a,b,d,b,a} -> $count[a] = 2; $count[b] = 2; $count[d] = 1
		echo "Count: <br>";
		print_r($count);
		echo "<br>";
		$kunciKalimat=0;
		$kunciKalimat2=0;
		for ($j=0;$j<$jumlahKunci;$j++) {
			if (isset($count[$kunci[$j]])) {
				$kunciKalimat += $count[$kunci[$j]];
				$kunciKalimat2 += $count[$kunci[$j]] * $count[$kunci[$j]];
			}
		}
		echo "<br>kunciKalimat // kunciKalimat2: <br>$kunciKalimat // $kunciKalimat2<br>";

		for ($j=0;$j<$jumlahKunci;$j++) {
			switch ($pilih) {			
				case 0:
					if (isset($count[$kunci[$j]])) $matrix[$j][$i] = $count[$kunci[$j]] * $bobotGlobal[$i];
					else $matrix[$j][$i] = 0;
					break;
				case 1:
					if (isset($count[$kunci[$j]])) {
						$matrix[$j][$i] = $count[$kunci[$j]];
						if (array_search($kunci[$j], $bobot)!==FALSE) $matrix[$j][$i] = $matrix[$j][$i] * 2 * $bobotGlobal[$i];
					}
					else $matrix[$j][$i] = 0;
					echo $matrix[$j][$i];
					break;
				case 2:
					if (isset($count[$kunci[$j]])) $matrix[$j][$i] = 1 * $bobotGlobal[$i];
					else $matrix[$j][$i] = 0;
					break;					
				case 3:
					if (isset($count[$kunci[$j]])) $matrix[$j][$i] = 1 + log($count[$kunci[$j]],10) * $bobotGlobal[$i];
					else $matrix[$j][$i] = 0;
					break;
				case 4:
					if (isset($count[$kunci[$j]])) $matrix[$j][$i] = 1 + sqrt($count[$kunci[$j]] - 0.5) * $bobotGlobal[$i];
					else $matrix[$j][$i] = 0;
					break;
				case 5: 
					if (isset($count[$kunci[$j]])) $matrix[$j][$i] = $count[$kunci[$j]] / $kunciKalimat * $bobotGlobal[$i];
					else $matrix[$j][$i] = 0;
					break;
				case 6: 
					if (isset($count[$kunci[$j]])) $matrix[$j][$i] = $count[$kunci[$j]] / sqrt($kunciKalimat2) * $bobotGlobal[$i];
					else $matrix[$j][$i] = 0;
					break;

			}
		}	
		echo "<br>";
	}	

	//tampilkan matriksnya
	echo "<br>";
	echo "<table border='1'>";
	for($j=0;$j<$jumlahKunci;$j++){
		if(isset($kunci[$j])){
			echo "<tr><td>".$kunci[$j].":</td>";
		}
		else echo "<tr><td>No Kunci:</td>";
		for($i=0;$i<$jumlahBaris;$i++){
			echo "<td>".$matrix[$j][$i]."</td>";
		}
		echo "</tr>";
	}

	echo "</table>";
	return $matrix;

}

function carikata($kata) {
	
	include "lib/connect.php";
	$kata=trim ($kata, " \t\n\r\0\x0B");
	$query=$db->Execute("SELECT * FROM tb_kata_kata WHERE kata LIKE '$kata'");
	if (!$query->EOF) return $query->fields['persamaan'];
	else return 0;
}

function persamaanKata($arai) {
	//echo 'awal'; print_r($arai);
	//for ($i=0;$i<count($arai);$i++) if (carikata($arai[$i])!=0) $arai[$i] = carikata($arai[$i]);
	foreach ($arai as $i => $ara) if (carikata($ara)!=0) $arai[$i] = carikata($ara);
	//echo 'persamaan'; print_r($arai);
	return $arai;
}

function hitungNilai($matrix){
	
	$MTX = new Matrix($matrix);
	// $MTX->toHTML();
	$MTX->svd2();
	$result = $MTX->normF();
	
	return $result; 
	
}

function hitungNilaiSudut($matrix){
	
	$MTX = new Matrix($matrix);
	print_r ($MTX->svd2());
	$s = array_slice($MTX->svd2(),0,2);
	$s = array_pad($s, 2, 0);
	$SVD = new Matrix(array($s));
	// print_r($SVD);
	//if ($SVD->n<2) $SVD->A[1][2]=0;
	// echo "Halo";echo $SVD->toHTML();
	
	
	
	return $SVD; 
	
}

function hitungNilaiPad($matrix_ref, $matrix_test){
	$MTX_ref = new Matrix($matrix_ref);
	$MTX_test = new Matrix($matrix_test);

	$s_ref = $MTX_ref->svd2();
	$s_test = $MTX_test->svd2();

	if(count($s_ref) > count($s_test)){
		$s_test = array_pad($s_test, count($s_ref), 0);
	} else if (count($s_ref) < count($s_test)){
		$s_ref = array_pad($s_ref, count($s_test), 0);
	}

	$SVD_ref = new Matrix(array($s_ref));
	$SVD_test = new Matrix(array($s_test));

	$nilaiRef = $SVD_ref->normF();
	$nilaiTest = $SVD_test->normF();
	echo "<h5>Rincian Nilai Pad</h5>";
	echo "Nilai Ref x Nilai Test: ".$nilaiRef * $nilaiTest; echo "<br>";

	$hasil = $SVD_ref->arrayTimes($SVD_test);
	$dot = array_sum($hasil->A[0]);
	echo "Hasil Dot Kedua Array: ".$dot; echo "<br>";

	$nilaiFinal = $dot / ($nilaiRef * $nilaiTest);
	echo "Nilai Final Kedua Array (cos teta): ".$nilaiFinal; echo "<br>";
	
	$sudutRad = acos($nilaiFinal); // in radians
	$sudutDeg = $sudutRad * 180 / M_PI;

	return $sudutDeg;

}

function hitungNilaiSlice($matrix_ref, $matrix_test){
	$MTX_ref = new Matrix($matrix_ref);
	$MTX_test = new Matrix($matrix_test);

	$s_ref = $MTX_ref->svd2();
	$s_test = $MTX_test->svd2();

	if(count($s_ref) > count($s_test)){
		$s_ref = array_slice($s_ref, 0, count($s_test));
	} else if (count($s_ref) < count($s_test)){
		$s_test = array_slice($s_test, 0, count($s_ref));
	}

	$SVD_ref = new Matrix(array($s_ref));
	$SVD_test = new Matrix(array($s_test));

	$nilaiRef = $SVD_ref->normF();
	$nilaiTest = $SVD_test->normF();
	// echo ($nilaiRef); echo "<br>";
	// echo ($nilaiTest); echo "<br>";

	echo "<h5>Rincian Nilai Slice</h5>";
	echo "Nilai Ref x Nilai Test: ".$nilaiRef * $nilaiTest; echo "<br>";

	$hasil = $SVD_ref->arrayTimes($SVD_test);
	$dot = array_sum($hasil->A[0]);
	echo "Hasil Dot Kedua Array: ".$dot; echo "<br>";

	$nilaiFinal = $dot / ($nilaiRef * $nilaiTest);
	echo "Nilai Final Kedua Array (cos teta): ".$nilaiFinal; echo "<br>";

	$sudutRad = acos($nilaiFinal); // in radians
	// echo (acos(1)); echo "<br>";
	$sudutDeg = $sudutRad * 180 / M_PI;

	return $sudutDeg;

}
?>
