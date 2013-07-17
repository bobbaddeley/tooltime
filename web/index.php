<?php
ini_set("display_errors", true);
$file_db = new PDO('sqlite:db/database.sqlite');
// Set errormode to exceptions
$file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>
<html>
<head>
<style type="text/css" title="currentStyle">
@import "js/datatables-1-9-4/media/css/demo_page.css";

@import "js/datatables-1-9-4/media/css/demo_table.css";
</style>
<link href="css/style.css" rel="stylesheet">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
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
				<a class="brand" href="./">Tooltime</a>
				<div class="nav-collapse collapse">
					<ul class="nav">
						<li class="active"><a href="./">Home</a></li>
						<li><a href="./admin/">Admin</a></li>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</div>

	<div class="container">
		<div id="total_usage">
			<h1>Total Usage</h1>
			<p>This includes usage by unknown users</p>
			<?php
			$result = $file_db->query('SELECT sum(duration) as duration FROM machine_time where duration<7200');
			foreach ($result as $row) {
				print floor($row['duration']/3600)." hours, ".floor($row['duration']%3600/60)." minutes, ".($row['duration']%60)." seconds";
			}
			?>
		</div>
		<div id="sum_by_user">
			<h1>Total Usage By User</h1>
			<p>This does not include usage by unknown users</p>
			<table cellpadding="0" cellspacing="0" border="0" class="display"
				id="user_sum_table" width="100%">
				<thead>
					<tr>
						<th>User</th>
						<th>Duration</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$result = $file_db->query('SELECT first_name, last_name, sum(duration) as duration FROM machine_time JOIN user on user.id = machine_time.user_id where duration<7200 group by user_id');
				foreach ($result as $row) {
					print "<tr><td>".$row['first_name']." ".$row['last_name']."</td><td>".floor($row['duration']/3600)." hours, ".floor($row['duration']%3600/60)." minutes, ".($row['duration']%60)." seconds</td></tr>";
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<th>User</th>
						<th>Duration</th>
					</tr>
				</tfoot>
			</table>
		</div>

		<div id="all">
			<h1>Individual Records</h1>
			<p>This does not include usage by unknown users. In the Search bar,
				you can type a person's name to filter.</p>
			<table cellpadding="0" cellspacing="0" border="0" class="display"
				id="records_table" width="100%">
				<thead>
					<tr>
						<th>Time</th>
						<th>Duration</th>
						<th>User</th>
						<th class="editable">Material</th>
						<th>Billing</th>
						<th>Paid</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$result = $file_db->query('SELECT machine_time.id, first_name, last_name, start_time, duration, snap, billing, material, paid FROM machine_time JOIN user on user.id = machine_time.user_id where duration<7200');
				foreach ($result as $row) {
					print "<tr data-id='".$row['id']."'><td>";
					if (strlen($row['snap'])>0) {
						print "<a href='snaps/".$row['snap']."'>";
					}
					print date("Y-m-d H:i:s", $row['start_time']);
					if (strlen($row['snap'])>0) {
						print "</a>";
					}
					print "</td>
					<td>".$row['duration']."</td>
					<td>".$row['first_name']." ".$row['last_name']."</td>
					<td class='editable'>".($row['material']?$row['material']:"unknown")."</td>
					<td>".$row['billing']."</td>
					<td><input type='checkbox' disabled ".($row['paid']==1?"checked":"")." /></td>
					</tr>";
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<th>Time</th>
						<th>Duration</th>
						<th>User</th>
						<th class="editable">Material</th>
						<th>Billing</th>
						<th>Paid</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<script type="text/javascript" language="javascript"
		src="js/jquery.min.js"></script>
	<script type="text/javascript" language="javascript"
		src="js/datatables-1-9-4/media/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript"
		src="js/jquery.jeditable.mini.js"></script>
	<script type="text/javascript" language="javascript" src="js/script.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript"
		 src="js/flot-flot-f0e78fc/jquery.flot.js"></script>
</body>
</html>
