<?php
if ($_REQUEST['action']!="import") {
	exit;
} else {
	$data = file_get_contents("data.txt");
	$split_data = explode("\n", $data);
	try{
		$file_db = new PDO('sqlite:db/database.sqlite');
		// Set errormode to exceptions
		$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $file_db->prepare("INSERT INTO machine_time (start_time,duration,user_id,machine_id) VALUES (:start_time,:duration,:user_id,:machine_id)");
		$stmt->bindParam(':start_time', $time);
		$stmt->bindParam(':duration', $duration);
		$stmt->bindParam(':user_id', $user);
		$stmt->bindParam(':machine_id', $machine);
		foreach ($split_data as $data_row) {
			if (substr($data_row, 0, 5)=="Time:") {
				$split_row = preg_split("/[,:]/", $data_row);
				$time = $split_row[1];
				$machine = $split_row[3];
				$result = $file_db->query('SELECT id FROM user where rfid="'.$split_row[5].'"');
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$user = $row['id'];
				$duration = $split_row[7];
				$stmt->execute();
			}
		}
	}
	catch(PDOException $e){
		// Print PDOException message
		echo $e->getMessage();
	}
}
