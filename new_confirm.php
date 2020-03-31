<?php
	include 'session.php';
	include 'config.php';

	$TABLE = $SAMPLE_TABLE;
	
	$flag = 0;

	$sample_name = preg_replace('/\s/', '', htmlspecialchars($_GET["sample_name"]));
	$grower = htmlspecialchars($_GET["grower"]);
	$date = htmlspecialchars($_GET["date"]);
	$sister = htmlspecialchars($_GET["sister"]);
	$material = htmlspecialchars($_GET["material"]);
	$xratio = htmlspecialchars($_GET["xratio"]);
	$substrate = htmlspecialchars($_GET["substrate"]);
	$substrate_name = preg_replace('/\s/', '', htmlspecialchars($_GET["substrate_name"]));
	$termination = htmlspecialchars($_GET["termination"]);
	$thickness = htmlspecialchars($_GET["thickness"]);
	$roughness = htmlspecialchars($_GET["roughness"]);
	$pressure = htmlspecialchars($_GET["pressure"]);
	$post_pressure = htmlspecialchars($_GET["post_pressure"]);
	$method = htmlspecialchars($_GET["method"]);
	$temp = htmlspecialchars($_GET["temp"]);
	$freq = htmlspecialchars($_GET["freq"]);
	$fluence = htmlspecialchars($_GET["fluence"]);
	$pulses = htmlspecialchars($_GET["pulses"]);
	$cool_rate = htmlspecialchars($_GET["cool_rate"]);
	$cap = htmlspecialchars($_GET["cap"]);
	$cap_material = htmlspecialchars($_GET["cap_material"]);
	$comment = addslashes(htmlspecialchars($_GET["comment"]));
	$available = htmlspecialchars($_GET["available"]);
	$lattice_param_a = htmlspecialchars($_GET["lattice_param_a"]);
	$lattice_param_b = htmlspecialchars($_GET["lattice_param_b"]);
	$lattice_param_c = htmlspecialchars($_GET["lattice_param_c"]);
	
	$query = "INSERT INTO $TABLE VALUES (
	'',
	'$sample_name',
	'$grower',
	'$date',
	'$sister',
	'$material',
	'$xratio',
	'$substrate',
	'$substrate_name',
	'$termination',
	'$thickness',
	'$roughness',
	'$pressure',
	'$post_pressure',
	'$method',
	'$temp',
	'$freq',
	'$fluence',
	'$pulses',
	'$cool_rate',
	'$cap',
	'$cap_material',
	'$comment',
	'$available',
	'$lattice_param_a',
	'$lattice_param_b',
	'$lattice_param_c'
	)";
	
	$mysqli = mysqli_connect($host, $user, $pass, $db);
	if(mysqli_query($mysqli, $query)){
		$flag=1;
		$id = mysqli_insert_id($mysqli);
	}
?>
<!DOCTYPE html>
<!--

-->
<html>
<head>
<meta charset="UTF-8" >
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="w3.css">
<title>Holcomb Group DB</title>
</head>
<body class="w3-content w3-amber">
	<div class="w3-cell-row">
		<div class="w3-container w3-cell">
			<p><h3>Holcomb Group DB - New Sample Confirmation</h3></p>
		</div>
		<div class="w3-container w3-cell w3-right-align">
			<p><a href="logout.php" class="w3-button w3-red w3-round-large">Logout</a></p>
		</div>
	</div>
	
	<?php readfile("menu.html"); ?>

	<?php
		if($flag){
			echo '
	<div class="w3-container w3-green">
		<p>'.$sample_name.' inserted successfully.</p>
	</div>
	<div class="w3-panel w3-border w3-border-black">
		<p><a href="upload_data.php?id='.$id.'&sample_name='.$sample_name.'" class="w3-button w3-blue w3-round-large">Upload Data</a></p>
	</div>
			';
		} else{
			echo "
	<div class=\"w3-container w3-red\">
		<p>There was a problem inserting ".$sample_name.".</p>
	</div>
			";
		}
	?>

</body>
</html>
