<!DOCTYPE HTML>
<html>
<head></head>
<body>
	<form method="POST" action="testpaper.php">
		<table>
			<tr>
				<td>Versi 1.2</td>
			</tr>
			<tr>
				<td>id paper test : </td>
				<td><input type="text" name="idtest"></td>
			</tr>
			<tr>
				<td>id paper ref :</td>
				<td><input type="text" name="idref"></td>
			</tr>
			<tr>
				<td><input value="reset" type="button" onclick="window.location.href='testpaper.php'; " ></td>
				<td><button name="ok" type="submit">ok</button></td>
			</tr>
		</table>
	</form>
</body>
</html>
<?php
	include_once "lib/adodb.inc.php";
	include_once "lib/connect.php";
	include_once "lib/strlib.php";
	include_once "lib/Matrix.php";
	include_once "lib/lsa.php";

	if (isset($_POST["idtest"])){
		// 1. ambil seluruh paragraf di paper test
		$id_test = $_POST["idtest"];
		$sql = "SELECT  idtext, testedtext from tested where idtext >= $id_test AND idtext < ($id_test+100)";
		$query = $db->Execute($sql);


		// 2. simpan seluruh paragraf test pada array
		$array_test = array();
		while (!$query->EOF){
			$array_test[$query->fields["idtext"]] = $query->fields["testedtext"];
			$query->MoveNext();
		}
		print("<br>1. Paragraf Test<br><br>");print_r($array_test);
		

		// 3. ambil seluruh paragraf di paper ref
		$id_ref = $_POST["idref"];
		$sql = "SELECT  idref, reftext from reference where idref >= $id_ref AND idref < ($id_ref+100)";
		$query = $db->Execute($sql);


		// 4. simpan seluruh paragraf ref pada array
		$array_ref = array();
		while (!$query->EOF){
			$array_ref[$query->fields["idref"]] = $query->fields["reftext"];
			$query->MoveNext();
		}
		print("<br><br>2. Paragraf Referensi<br><br>");print_r($array_ref);


		// 5. preprocess seluruh paragraf ref
		foreach ($array_ref as $key => $value) {
			// merapikan paragraf ref
			$value = cleanText($value);

			//sampai loop foreach ini habis: menghilangkan stopwords dari paragraf ref
			$sql = "SELECT  words from stopwords where idstopwords = 'ende'";
			$query = $db->Execute($sql);
			$stopwords = $query->fields["words"];

			//2 baris kebawah: buat stopwords
			$stopwords = preg_replace("/[^A-Za-z]/", " ",$stopwords);
			$stopwords = preg_split('/ /', $stopwords, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($stopwords as $words) {
				$words = " " . $words . " ";
				$value = str_replace($words, " ", $value);
			}
			$array_ref[$key] = $value;		
		}
		//$array_ref: paragraf referensi -> dipecah per kata -> stopwords dibuang
		print("<br><br>3. Referensi di clean + buang stopwords<br><br>");print_r($array_ref);	

		
		foreach ($array_test as $key => $value) {

			echo "<br><center><h2><u>ID Paragraf Test: ".$key."</u></h2></center>";
			// 6. preprocess paragraf test
			// merapikan paragraf test
			$value = cleanText($value);

			// menghilangkan stopwords dari paragraf test
			$sql = "SELECT  words from stopwords where idstopwords = 'idde'";
			$query = $db->Execute($sql);
			$stopwords = $query->fields["words"];
			$stopwords = preg_replace("/[^A-Za-z]/", " ",$stopwords);
			$stopwords = preg_split('/ /', $stopwords, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($stopwords as $words) {
				$words = " " . $words . " ";
				$value = str_replace($words, " ", $value);
			}
			// print("<br>");print($key);
			// print("<br>");print($value);

			// potong paragraf test per kata
			$value = preg_split('/ /', $value, -1, PREG_SPLIT_NO_EMPTY);
			// print("<br>");print($key);
			print("<br><br>4. Test di clean + buang stopwords<br><br>");print_r($value); 

			// 7. translasikan kumpulan kata dr test
			$translated_test = array();
			foreach ($value as $words) {
				$sql = "SELECT d FROM id WHERE s LIKE '{$words}'";
				$result = $db->Execute($sql);
				$trans = $result->fields["d"];
				if (!is_null($trans)) {
					$trans = preg_replace("/[^A-Za-z]/", " ",$trans);
					$trans = preg_split('/ /', $trans, -1, PREG_SPLIT_NO_EMPTY);
					foreach($trans as $enwords) {
						$sql = "SELECT s FROM en WHERE d LIKE '%{$words}%' AND s LIKE '{$enwords}'";
						$result = $db->Execute($sql);
						$var = $result->fields["s"];
						if(!is_null($var)) {
							array_push($translated_test, $var);			
						} 
					}
				} else array_push($translated_test, $words);
			}
			//$translated_test: paragraf test -> dipecah jd kata2 -> dibuang stopwordsnya -> ditranslate
			print("<br><br>5. Test di translate<br><br>");print_r($translated_test);
			print("<br>");
			$idptest = $key;

			// cek paragraf test ke masing-masing paragraf ref
			foreach ($array_ref as $idpref => $pref) {
				echo "<br><center><h3>ID Paragraf Referensi: ".$idpref."</h3></center>";
				// 8. potong paragraf ref per kata
				$pref = preg_split('/ /', $pref, -1, PREG_SPLIT_NO_EMPTY);


				// 9. keywords dari paragraf ref, pastikan tidak ada yg double
				$keywords = array_unique($pref);


				// 10. hitung nilai LSA paragraf ref dan test
				$bobot = array();
				print_r("<br>Kata Referensi (clean, tanpa stopwords):<br><br>");				
				print_r($pref);
				print_r("<br><br>Kata Test (clean, tanpa stopwords, translated)<br><br>");
				print_r($translated_test);
				print_r("<br><br>Keywords: (dari kata referensi yg ud clean dan tanpa stopwords)<br><br>");				
				print_r($keywords);
				print_r("<br>");				
				echo "<br><h4>LSA Referensi:".$idpref."</h4>";
				$ref_matrix = lsaMatrixGenerator(1, 1, $pref, $keywords, $bobot);
				$nilai_ref = hitungNilai($ref_matrix);
				echo "<br>FrobN matriks referensi: ".$nilai_ref;
				echo "<br><h4>LSA Test:".$key."</h4>";
				$test_matrix = lsaMatrixGenerator(1, 1, $translated_test, $keywords, $bobot);
				$nilai_test = hitungNilai($test_matrix);
				echo "<br>FrobN matriks test: ".$nilai_test;
				$nilaiFinal = $nilai_test/$nilai_ref*100;
				echo "<br><br>FrobN test/ref (Final): ".$nilaiFinal;
				$nilai_slice = hitungNilaiSlice($ref_matrix, $test_matrix);
				echo "<br>Nilai Slice teta: ".$nilai_slice;
				$nilai_pad = hitungNilaiPad($ref_matrix, $test_matrix);
				echo "<br>Nilai Pad teta: ".$nilai_pad;

				echo "<br><br><br>";
				$sqlscore = "insert into tbscore (idptest,idpref,frobn,slice,pad) values ($idptest,$idpref,$nilaiFinal,$nilai_slice,$nilai_pad)";
				$rsscore = $db->Execute($sqlscore);

				// print("<br>");print($idptest); print(", "); print($idpref);
				// print("<br>");print($nilaiFinal);				
				// print("<br>");print($nilai_slice);				
				// print("<br>");print($nilai_pad);
			}
		}
	}

	function cleanText($text){
		$text = strtolower($text);
		$text = preg_replace("/[^A-Za-z]/", " ",$text);
		$text = " " . $text . " ";

		return $text;
	}
?>