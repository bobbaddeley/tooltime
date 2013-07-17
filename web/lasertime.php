<?php
$data = $_REQUEST['data'];
$tag= $_REQUEST['tag'];
$machine=$_REQUEST['machine'];
$jobtime = $_REQUEST['time'];
$snap = $_REQUEST['snap'];
$billing = $_REQUEST['billing'];
$data = explode(",", $data);
$time = time();
$duration = trim(substr($data[0], 2));
$id = substr($data[1], 2, 10);
file_put_contents("data.txt", "Time:".$time.",MachineID:".$machine.",RFID:".$tag.",Billing:".$billing.",Snap:".$snap.",Duration:".$duration."\n", FILE_APPEND);

try{
	$file_db = new PDO('sqlite:database.sqlite');
	// Set errormode to exceptions
	$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $file_db->prepare("INSERT INTO machine_time (start_time,duration,user_id,machine_id,billing,snap) VALUES (:start_time,:duration,:user_id,:machine_id,:billing,:snap)");
	$stmt->bindParam(':start_time', $time);
	$stmt->bindParam(':duration', $duration);
	$stmt->bindParam(':user_id', $user);
	$stmt->bindParam(':machine_id', $machine);
	$stmt->bindParam(':billing', $billing);
	$stmt->bindParam(':snap', $snap);
	$result = $file_db->query('SELECT id FROM user where rfid="'.$tag.'"');
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$user = $row['id'];
	$stmt->execute();
}
catch(PDOException $e){
	// Print PDOException message
	echo $e->getMessage();
}