<?php
ini_set("display_errors", true);
$file_db = new PDO('sqlite:../db/database.sqlite');
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
<html>
<head>
<style type="text/css" title="currentStyle">
@import "../js/datatables-1-9-4/media/css/demo_page.css";

@import "../js/datatables-1-9-4/media/css/demo_table.css";
</style>
<link href="../css/style.css" rel="stylesheet">
<link href="../css/bootstrap.min.css" rel="stylesheet">
<link href="../css/bootstrap-responsive.min.css" rel="stylesheet">
<style>
body {
	padding-top: 60px;
	/* 60px to make the container go all the way to the bottom of the topbar */
}
</style>
</head>
<body>

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<button type="button" class="btn btn-navbar" data-toggle="collapse"
					data-target=".nav-collapse">
					<span class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="brand" href="../">Tooltime</a>
				<div class="nav-collapse collapse">
					<ul class="nav">
						<li><a href="../">Home</a></li>
						<li class="active"><a href="./">Admin</a></li>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</div>

	<div class="container">
		<div id="DBadmin">
		<h1>Database Admin</h1>
		<a href="dbadmin/index.php?dbsel=3">Database Administration</a>
		</div>
		<div id="users">
			<h1>Users</h1>
			<a href="#addUser" role="button" class="btn" data-toggle="modal">Add
				User</a>
			<table cellpadding="0" cellspacing="0" border="0" class="display"
				id="users_table" width="100%">
				<thead>
					<tr>
						<th>ID</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>RFID</th>
						<th>Delete
						
						</td>
					</tr>
				</thead>
				<tbody>
				<?php
				$result = $file_db->query('SELECT id, first_name, last_name, rfid FROM user');
				foreach ($result as $row) {
					print "<tr id='".$row['id']."'><td class='exclude'>".$row['id']."</td><td>".$row['first_name']."</td><td>".$row['last_name']."</td><td>".$row['rfid']."</td><td class='exclude'><button data-id='".$row['id']."' class='btn-delete btn btn-mini' type='button'>Delete</button></td></tr>";
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<th>ID</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>RFID</th>
						<th>Delete
						
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div id="addUser" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-hidden="true">&times;</button>
				<h3>Add User</h3>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" id="frmAddUser">
					<div class="control-group">
						<label class="control-label" for="inputFirstName">First Name</label>
						<div class="controls">
							<input type="text" id="inputFirstName" placeholder="First Name"
								name="first_name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="inputLastName">Last Name</label>
						<div class="controls">
							<input type="text" id="inputLastName" placeholder="Last Name"
								name="last_name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="inputRFID">RFID</label>
						<div class="controls">
							<input type="text" id="inputRFID" placeholder="RFID" name="rfid">
						</div>
					</div>
					<input type="hidden" name="action" value="create" />
				</form>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn btn-primary" id="btnAddUser">Save changes</a>
			</div>
		</div>
	</div>
	<script type="text/javascript" language="javascript"
		src="../js/jquery.min.js"></script>
	<script type="text/javascript" language="javascript"
		src="../js/jquery.jeditable.mini.js"></script>
	<script type="text/javascript" language="javascript"
		src="../js/datatables-1-9-4/media/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="../js/admin.js"></script>
	<script src="../js/bootstrap.min.js"></script>
</body>
</html>
