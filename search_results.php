<?php

include 'session.php';
include 'config.php';

$sample_name = htmlspecialchars($_GET["sample_name"]);
$grower = htmlspecialchars($_GET["grower"]);
$material = htmlspecialchars($_GET["material"]);
$substrate = htmlspecialchars($_GET["substrate"]);
$substrate_name = htmlspecialchars($_GET["substrate_name"]);
$termination = htmlspecialchars($_GET["termination"]);
$available = htmlspecialchars($_GET["available"]);
$temp = htmlspecialchars($_GET["temp"]);
$min_temp = htmlspecialchars($_GET["min_temp"]);
$max_temp = htmlspecialchars($_GET["max_temp"]);
$growth_pressure = htmlspecialchars($_GET["growth_pressure"]);
$min_pressure = htmlspecialchars($_GET["min_pressure"]);
$max_pressure = htmlspecialchars($_GET["max_pressure"]);
$post_pressure = htmlspecialchars($_GET["post_pressure"]);
$min_post_pressure = htmlspecialchars($_GET["min_post_pressure"]);
$max_post_pressure = htmlspecialchars($_GET["max_post_pressure"]);
$thick = htmlspecialchars($_GET["thick"]);
$min_thick = htmlspecialchars($_GET["min_thick"]);
$max_thick = htmlspecialchars($_GET["max_thick"]);
$date = htmlspecialchars($_GET["date"]);
$min_date = htmlspecialchars($_GET["min_date"]);
$max_date = htmlspecialchars($_GET["max_date"]);
$min_magnetism = htmlspecialchars($_GET["min_magnetism"]);
$max_magnetism = htmlspecialchars($_GET["max_magnetism"]);

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
	<style>
		a:link {
			text-decoration: none;
		}
	</style>
</head>
<body class="w3-content w3-amber">
	<div class="w3-cell-row">
		<div class="w3-container w3-cell">
			<p><h3>Holcomb Group DB - Search Results</h3></p>
		</div>
		<div class="w3-container w3-cell w3-right-align">
			<p><a href="logout.php" class="w3-button w3-red w3-round-large">Logout</a></p>
		</div>
	</div>
	
	<?php readfile("menu.html"); ?>

<form enctype="multipart/form-data" action="graph_multi_data.php" method="GET">
	<div class="w3-border w3-border-black">
	<table class="w3-table-all">
		<tr class="w3-amber">
			<th>SAMPLE</th>
			<th>SUBSTRATE</th>
			<th>THICKNESS</th>
			<th>PRESSURE</th>
			<th>TEMP</th>
			<?php if($min_magnetism > 0 || $max_magnetism > 0){
				echo "<th>Ms</th>";
			}?>
			<th>DATA</th>
			<th class="w3-center">GRAPH</th>
		</tr>

	<?php
		
		$where = "1";
		if($sample_name != ""){
			$where .= " AND sample_name LIKE '%$sample_name%'";
		}
		if($grower != ""){
			$where .= " AND grower LIKE '$grower'";
		}
		if($substrate != ""){
			$where .= " AND substrate LIKE '$substrate'";
		}
		if($substrate_name != ""){
			$where .= " AND substrate_name LIKE '$substrate_name'";
		}
		if($termination != -1){
			$where .= " AND termination=$termination";
		}
		if($available != ""){
			$where .= " AND available=$available";
		}
		if($temp != ""){
			$where .= " AND temp=$temp";
		}
		if($growth_pressure != ""){
			$where .= " AND pressure=$growth_pressure";
		}
		if($post_pressure != ""){
			$where .= " AND post_pressure=$post_pressure";
		}
		if($min_pressure != ""){
			$where .= " AND pressure>=$min_pressure";
		}
		if($max_pressure != ""){
			$where .= " AND pressure<=$max_pressure";
		}
		if($min_post_pressure != ""){
			$where .= " AND post_pressure>=$min_post_pressure";
		}
		if($max_post_pressure != ""){
			$where .= " AND post_pressure<=$max_post_pressure";
		}
		if($min_temp != ""){
			$where .= " AND temp>=$min_temp";
		}
		if($max_temp != ""){
			$where .= " AND temp<=$max_temp";
		}
		if($thick != ""){
			$where .= " AND (thickness>=($thick - 1) AND thickness<=($thick + 1))";
		}
		if($min_thick != ""){
			$where .= " AND thickness>=$min_thick";
		}
		if($max_thick != ""){
			$where .= " AND thickness<=$max_thick";
		}
		if($date != ""){
			$where .= " AND date='$date'";
		}
		if($min_date != ""){
			$where .= " AND date>='$min_date'";
		}
		if($max_date != ""){
			$where .= " AND date<='$max_date'";
		}
		
		$mysqli = mysqli_connect($host, $user, $pass, $db);
		$res = mysqli_query($mysqli, "SELECT * FROM $SAMPLE_TABLE WHERE ".$where." ORDER BY date");
		while ($row = $res->fetch_assoc()) {
			$id = $row['id'];
			$sample_name = $row['sample_name'];
			$substrate = $row['substrate'];
			$thickness = $row['thickness'];
			$pressure = $row['pressure'];
			$temp = $row['temp'];
			$available = $row['available'];

			$print_result = 1;

			$magnetism = NULL;
			if(is_numeric($min_magnetism) || is_numeric($max_magnetism)){
				
				$res2 = mysqli_query($mysqli,

				"SELECT $DATA_TABLE.filename 
				FROM $DATA_TABLE INNER JOIN $M_DETAIL_TABLE ON ($DATA_TABLE.id=$M_DETAIL_TABLE.file_id AND $M_DETAIL_TABLE.measure_H=1000 AND $M_DETAIL_TABLE.cool_H>0)
				WHERE ($DATA_TABLE.data_type=8 AND $DATA_TABLE.sample_id=$id)");

				while ($row2 = $res2->fetch_assoc()) {
					$filename = $row2['filename'];

					$T = 0;
					$M = 0;
					$T_max = 0;
					$M_max = 0;
					
					$fh = fopen($DATA_DIR.$filename,"r");
					
					$line = fgets($fh);
					preg_match('/(,)/',$line,$matches);

					if($matches[0]==","){ # data is formatted the wrong way (csv instead of tab)
						fclose($fh);
						continue;
					}
					
					$fields = explode("\t",$line);
					$T = trim($fields[0]);
					$M = trim($fields[1]);
					
					$data_flag = 0;
					
					while(!feof($fh)){
						if(is_numeric($T)){
							if($T > 4.999 && $T < 5.999){
								if(is_numeric($M)){
									if($M > $M_max){
										$data_flag=1;
										$T_max = $T;
										$M_max = $M;
									}
								}
							}
						}
						$line = fgets($fh);
						$fields = explode("\t",$line);
						$T = trim($fields[0]);
						$M = trim($fields[1]);
					}
					
					fclose($fh);

					if($data_flag == 0){ continue; } # couldn't find any good data around T = 5K
					if($M_max < 1){ continue; } # M is not in emu/cc
					$magnetism = number_format($M_max,1,'.','');
				}
			}
			if(is_numeric($min_magnetism) || is_numeric($max_magnetism)){
				if($magnetism == NULL){
					$print_result = 0;
				}
			}
			if(is_numeric($min_magnetism) && $magnetism < $min_magnetism){
				$print_result = 0;
			}
			if(is_numeric($max_magnetism) && $magnetism > $max_magnetism){
				$print_result = 0;
			}
			if($print_result){
				echo '<tr class="w3-hover-blue">';
				echo '<td>';
				echo '<a href="show_sample.php?id='.$id.'">';
				echo $sample_name;
				echo '</a>';
				echo '</td><td>';
				echo $substrate;
				echo '</td><td>';
				echo "$thickness nm";
				echo '</td><td>';
				echo "$pressure mTorr";
				echo '</td><td>';
				echo "$temp C";
				echo '</td><td>';
				if($min_magnetism > 0 || $max_magnetism > 0){
					echo "$magnetism emu/cc";
					echo '</td><td>';
				}
				$xray = 0;
				$mag = 0;
				$rheed = 0;
				$afm = 0;
				$res2 = mysqli_query($mysqli, "SELECT data_type FROM data_files WHERE sample_id=$id");
				while ($row2 = $res2->fetch_assoc()) {
					$data_type = $row2['data_type'];
					if(in_array($data_type,$XRAY_ARR)){
						$xray = 1;
					}
					if(in_array($data_type,$MAG_ARR)){
						$mag = 1;
					}
					if(in_array($data_type,$RHEED_ARR)){
						$rheed = 1;
					}
					if(in_array($data_type,$AFM_ARR)){
						$afm = 1;
					}
				}
				if($xray){
					echo '<a href="show_data.php?id='.$id.'&sample_name='.$sample_name.'"><img src="icons/'.$XRR_ICON.'"> </a>';
				}
				if($mag){
					echo '<a href="show_data.php?id='.$id.'&sample_name='.$sample_name.'"><img src="icons/'.$MAG_ICON.'"> </a>';
				}
				if($rheed){
					echo '<a href="show_data.php?id='.$id.'&sample_name='.$sample_name.'"><img src="icons/'.$RHEED_ICON.'"> </a>';
				}
				if($afm){
					echo '<a href="show_data.php?id='.$id.'&sample_name='.$sample_name.'"><img src="icons/'.$AFM_ICON.'"> </a>';
				}
				echo '</td><td class="w3-center">';
				if($xray + $mag + $rheed > 0){
					echo '<input type="checkbox" name="graph_ids[]" value="'.$id.'">';
				}
				echo '</td>';
				echo '</tr>';
			}
		}
	?>
	</table>
	</div>
	
	<div class="w3-panel w3-border w3-border-black">
		<p class="w3-right-align"><input type="submit" class="w3-button w3-blue w3-round-large" value="Graph"/></p>
	</div>

</form>


</body>
</html>
