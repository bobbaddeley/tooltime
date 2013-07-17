<?php
$goodnames = array("bob","joe", "chris", "monty");
$goodlist = array("0100B38111","3B00C09A0C","84000711FB", "840007184C");
//echo in_array($_REQUEST['tag'],$goodlist)?"y":"n";

$file_db = new PDO('sqlite:database.sqlite');
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$result = $file_db->query('SELECT id FROM user where rfid="'.$_REQUEST['tag'].'"');
$row = $result->fetch(PDO::FETCH_ASSOC);
if ($row) {
	echo "y";
} else {
	echo "n";
}

file_put_contents("login.txt", time().",".$_REQUEST['tag'].",".(in_array($_REQUEST['tag'], $goodlist)?"y":"n")."\n", FILE_APPEND);
