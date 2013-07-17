<?php
$action = $_REQUEST['action'];
switch($action){
	case 'update':
		try{
			$file_db = new PDO('sqlite:db/database.sqlite');
			// Set errormode to exceptions
			$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			switch($_REQUEST['column']){
				case "1":
					$stmt = $file_db->prepare("UPDATE user SET first_name=:first_name WHERE id=:user_id");
					$stmt->bindParam(':first_name', $_REQUEST['value']);
					$stmt->bindParam(':user_id', $_REQUEST['row_id']);
					break;
				case "2":
					$stmt = $file_db->prepare("UPDATE user SET last_name=:last_name WHERE id=:user_id");
					$stmt->bindParam(':last_name', $_REQUEST['value']);
					$stmt->bindParam(':user_id', $_REQUEST['row_id']);
					break;
				case "3":
					$stmt = $file_db->prepare("UPDATE user SET rfid=:rfid WHERE id=:user_id");
					$stmt->bindParam(':rfid', $_REQUEST['value']);
					$stmt->bindParam(':user_id', $_REQUEST['row_id']);
					break;
			}
			$stmt->execute();
		}
		catch(PDOException $e){
			// Print PDOException message
			echo $e->getMessage();
		}
		echo $_REQUEST['value'];
		break;
		
	case 'update_material':
		try{
			$file_db = new PDO('sqlite:db/database.sqlite');
			// Set errormode to exceptions
			$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $file_db->prepare("UPDATE machine_time SET material=:material WHERE id=:id");
			$stmt->bindParam(':material', $_REQUEST['value']);
			$stmt->bindParam(':id', $_REQUEST['row_id']);
			$stmt->execute();
		}
		catch(PDOException $e){
			// Print PDOException message
			echo $e->getMessage();
		}
		echo $_REQUEST['value'];
		break;
	case 'create':
		try{
			$first_name = $_REQUEST['first_name'];
			$last_name = $_REQUEST['last_name'];
			$rfid = $_REQUEST['rfid'];
			$file_db = new PDO('sqlite:db/database.sqlite');
			// Set errormode to exceptions
			$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $file_db->prepare("INSERT INTO user(first_name, last_name, rfid ) VALUES (:first_name,:last_name,:rfid)");
			$stmt->bindParam(':first_name', $first_name);
			$stmt->bindParam(':last_name', $last_name);
			$stmt->bindParam(':rfid', $rfid);
			$stmt->execute();
		}
		catch(PDOException $e){
			// Print PDOException message
			echo $e->getMessage();
		}
		echo $_REQUEST['value'];
		break;
	case 'delete':
		try{
			$id= $_REQUEST['id'];
			$file_db = new PDO('sqlite:db/database.sqlite');
			// Set errormode to exceptions
			$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $file_db->prepare("DELETE FROM user WHERE id = :id");
			$stmt->bindParam(':id', $id);
			$stmt->execute();
		}
		catch(PDOException $e){
			// Print PDOException message
			echo $e->getMessage();
		}
		break;
}

