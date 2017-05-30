<!DOCTYPE HTML>
<html>
<head></head>
<body>
	<form method="POST" action=test.php>
		<table>
			<tr>
				<td>Versi 1</td>
			</tr>
			<tr>
				<td>id tested :</td>
				<td><input type="text" name="idtested"></td>
			</tr>
			<tr>
				<td>id referensi dari :</td>
				<td><input type="text" name="idrefawal"></td>
			</tr>
			<tr>
				<td>sampai :</td>
				<td><input type="text" name="idrefakhir"></td>
			</tr>
			<tr>
				<td><Input type = 'Checkbox' Name ='stopwords' value= 'nostopwords'>Hapus stopwords</td>
				<td><Input type = 'Checkbox' Name ='keywords' value= 'integratedKey'>Gabung keywords</td>
			</tr>
			<tr>
				<td><input value="reset" type="button" onclick="window.location.href='test.php'; " ></td>
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

	if (isset($_POST["idtested"])) {
		print($_POST["idtested"]);
		print("<br>");
		print($_POST['stopwords']);
		print("<br>");
		print($_POST['keywords']);
		print("<br>");

		// 1. ambil paragraf tested
		$id_tested = $_POST["idtested"];

		$sql = "SELECT  idtext, testedtext from tested where idtext = $id_tested";
		$query = $db->Execute($sql);
		$text_test = $query->fields["testedtext"];


		// 2. merapikan paragraf test		
		$text_test = cleanText($text_test);



		if($_POST['stopwords'] == 'nostopwords'){
		// 2a. hapus stopwords
			$sql = "SELECT  words from stopwords where idstopwords = 'idde'";
			$query = $db->Execute($sql);
			$stopwords = $query->fields["words"];
			$stopwords = preg_replace("/[^A-Za-z]/", " ",$stopwords);
			$stopwords = preg_split('/ /', $stopwords, -1, PREG_SPLIT_NO_EMPTY);

			foreach ($stopwords as $key) {
				$key = " " . $key . " ";
				$text_test = str_replace($key, " ", $text_test);
			}
		}// 2b

		print($text_test);
		print_r("<br>");


		// 3. potong paragraf text per kata
		$text_test = preg_split('/ /', $text_test, -1, PREG_SPLIT_NO_EMPTY);

		// print_r($text_test);
		// print_r("<br>");


		// 4. translasikan kumpulan kata dr test

		// $time_start = microtime(true);

		$translated_test = array();
		foreach ($text_test as $key) {
			$sql = "SELECT d FROM id WHERE s LIKE '{$key}'";
			$result = $db->Execute($sql);
			$trans = $result->fields["d"];
			if (!is_null($trans)) {
				$trans = preg_replace("/[^A-Za-z]/", " ",$trans);
				$trans = preg_split('/ /', $trans, -1, PREG_SPLIT_NO_EMPTY);
				foreach($trans as $value) {
					$sql = "SELECT s FROM en WHERE d LIKE '%{$key}%' AND s LIKE '{$value}'";
					$result = $db->Execute($sql);
					$var = $result->fields["s"];
					if(!is_null($var)) {
						array_push($translated_test, $var);			
					} 
				}
			} else array_push($translated_test, $key);
		}

		// edit translasi
		
		//	

		// $time_end = microtime(true);
		// $time = $time_end - $time_start;
		// echo "$time seconds";

		// print_r($translated_test);
		$gabung = implode(" ", $translated_test);
		print($gabung); // lastname,email,phone
		print("<br>");

		for ($i = $_POST["idrefawal"]; $i <= $_POST["idrefakhir"]; $i++) {

			print_r($i); 	
			print_r("<br>");

			// 5. ambil paragraf referensi
			$id_referensi = $i;

			$sql = "SELECT  idref, reftext from reference where idref = $id_referensi";
			$query = $db->Execute($sql);
			$text_ref = $query->fields["reftext"];


			// 6. merapikan paragraf referensi	
			$text_ref = cleanText($text_ref);
			

			if($_POST['stopwords'] == 'nostopwords'){
			// 6a. hapus stopwords
				$sql = "SELECT  words from stopwords where idstopwords = 'ende'";
				$query = $db->Execute($sql);
				$stopwords = $query->fields["words"];
				$stopwords = preg_replace("/[^A-Za-z]/", " ",$stopwords);
				$stopwords = preg_split('/ /', $stopwords, -1, PREG_SPLIT_NO_EMPTY);

				foreach ($stopwords as $key) {
					$key = " " . $key . " ";
					$text_ref = str_replace($key, " ", $text_ref);
				}
			}// 6b

			print($text_ref);
			print_r("<br>");

			// 7. potong paragraf referensi per kata
			$text_ref = preg_split('/ /', $text_ref, -1, PREG_SPLIT_NO_EMPTY);

			// print_r($text_ref);
			// print_r("<br>");


			// keywords, pastikan tidak ada yang double
			if($_POST['keywords'] == 'integratedKey'){
				// 8a. keywords gabungan
				$keywords = array_merge($text_ref, $translated_test);
				$keywords = array_unique($keywords);
			} else {
				// 8b. keywords hanya dari referensi
				$keywords = array_unique($text_ref);
			}

			print_r($keywords);
			// $gabung2 = implode(" ", $keywords);
			// print($gabung2); // lastname,email,phone
			print("<br>");
			// sorting keywords		
			// sort($keywords, SORT_NATURAL | SORT_FLAG_CASE);


			// 9. hitung nilai LSA text referensi dan test
			$bobot = array();

			$ref_matrix = lsaMatrixGenerator(1, 1, $text_ref, $keywords, $bobot);
			$nilai_ref = hitungNilai($ref_matrix);
			// $nilaiRef = $nilai_ref->normF();


			$test_matrix = lsaMatrixGenerator(1, 1, $translated_test, $keywords, $bobot);
			$nilai_test = hitungNilai($test_matrix);

			$nilaiFinal = $nilai_test/$nilai_ref*100;

			print($nilaiFinal);
			print("<br>");
			$nilai_slice = hitungNilaiSlice($ref_matrix, $test_matrix);
			print($nilai_slice);
			print("<br>");
			$nilai_pad = hitungNilaiPad($ref_matrix, $test_matrix);
			print($nilai_pad);
			print("<br>");
		}
	}

	function cleanText($text){
		$text = strtolower($text);
		$text = preg_replace("/[^A-Za-z]/", " ",$text);
		$text = " " . $text . " ";

		return $text;
	}

?>
