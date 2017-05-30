<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "plague_test";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$key='tanaman';
$time_start = microtime(true);
$sql = "SELECT D FROM id WHERE S LIKE '{$key}'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $trans = preg_replace("/[^A-Za-z]/", " ",$row['D']);
		$trans = preg_split('/ /', $trans, -1, PREG_SPLIT_NO_EMPTY);
		foreach($trans as $index => $value) {
			$sql = "SELECT S FROM en WHERE D LIKE '%{$key}%' AND S LIKE '{$value}'";
			//echo $sql;
			$res = $conn->query($sql);
			if ($res->num_rows > 0) {
				// output data of each row
				while($row1 = $res->fetch_assoc()) {
					echo $row1['S'].'<br>';
				}
			}
				
			
		}
		//print_r($result);
    }
} else {
    echo "0 results";
}

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "$time seconds";
$conn->close();
?>