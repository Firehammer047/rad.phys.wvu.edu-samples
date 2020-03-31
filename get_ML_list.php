<?php
// ML code v2 July 2019
include '../session.php';
include '../config.php';
?>


<!DOCTYPE html>
<!--

-->
<html>
<head>
<meta charset="UTF-8" >
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../w3.css">
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
			<p><h3>Holcomb Group DB - Machine Learning</h3></p>
		</div>
		<div class="w3-container w3-cell w3-right-align">
			<p><a href="../logout.php" class="w3-button w3-red w3-round-large">Logout</a></p>
		</div>
	</div>

	<?php readfile("../menu.html"); ?>

	<div class="w3-panel w3-border w3-border-black w3-center w3-blue">
	<p>
	MvsT DATAFILE: <a href="ML_data_MvsT.txt" target="_blank">ML_data_MvsT.txt</a>
	</p>
	<p>
	MvsH DATAFILE: <a href="ML_data_MvsH.txt" target="_blank">ML_data_MvsH.txt</a>
	</p>
	<p>
	MvsH FILES USED: <a href="ML_MvsH_files.txt" target="_blank">ML_MvsH_files.txt</a>
	</p>
	</div>
	
		
	<div class="w3-panel w3-border w3-border-black w3-blue">
	<p>
	<b>MvsT Data</b>
	</p>
	</div>
	
	<div class="w3-border w3-border-black">
	<table class="w3-table-all">
		<tr class="w3-blue">
			<th>NAME</th>
			<th>THICK</th>
			<th>P_GROWTH</th>
			<th>P_COOL</th>
			<th>TEMP</th>
			<th>FREQ</th>
			<th>TERM</th>
			<th>H</th>
			<th>T</th>
			<th>M</th>
		</tr>
<?php
$fhout = fopen("ML_data_MvsT.txt","w");
$sample_array = array();

// ***************************************************
// Getting list of samples with Ms from MvT and H=1000Oe
// ***************************************************

$mysqli = mysqli_connect($host, $user, $pass, $db);
$res = mysqli_query($mysqli,
"SELECT $SAMPLE_TABLE.sample_name,$SAMPLE_TABLE.thickness,
$SAMPLE_TABLE.pressure,$SAMPLE_TABLE.post_pressure,$SAMPLE_TABLE.temp,
$SAMPLE_TABLE.freq,$SAMPLE_TABLE.termination,$DATA_TABLE.filename,$M_DETAIL_TABLE.measure_H,$M_DETAIL_TABLE.cool_H 
FROM $DATA_TABLE INNER JOIN $SAMPLE_TABLE ON $DATA_TABLE.sample_id=$SAMPLE_TABLE.id 
INNER JOIN $M_DETAIL_TABLE ON ($DATA_TABLE.id=$M_DETAIL_TABLE.file_id AND $M_DETAIL_TABLE.measure_H=1000 AND $M_DETAIL_TABLE.cool_H>0)
WHERE $DATA_TABLE.data_type=8 ORDER BY $SAMPLE_TABLE.sample_name");
	while ($row = $res->fetch_assoc()) {
		$sample = $row['sample_name'];
		$thickness = $row['thickness'];
		$pressure = $row['pressure'];
		$post_pressure = $row['post_pressure'];
		$temp = $row['temp'];
		$freq = $row['freq'];
		$termination = $row['termination'];
		$filename = $row['filename'];
		$H = $row['measure_H'];

		$sample_array[$sample]["name"] = $sample;
		$sample_array[$sample]["thickness"] = $thickness;
		$sample_array[$sample]["pressure"] = $pressure;
		$sample_array[$sample]["post_pressure"] = $post_pressure;
		$sample_array[$sample]["temp"] = $temp;
		$sample_array[$sample]["freq"] = $freq;
		$sample_array[$sample]["termination"] = $termination;
		$sample_array[$sample]["H"] = $H;

		$T = 0;
		$M = 0;
		$T_max = 0;
		$M_max = 0;
		
		$fh = fopen("../data/".$filename,"r");
		
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

		if($sample_array[$sample]["count"]){
			$sample_array[$sample]["count"] += 1;
		}
		else{
			$sample_array[$sample]["count"] = 1;
		}
		if($sample_array[$sample]["M_total"]){
			$sample_array[$sample]["M_total"] += $M_max;
			$sample_array[$sample]["T_total"] += $T_max;
		}
		else{
			$sample_array[$sample]["M_total"] = $M_max;
			$sample_array[$sample]["T_total"] = $T_max;
		}
	}
	foreach($sample_array as $sample_key){
		if($sample_key["count"] > 0){
			$sample_name = $sample_key["name"];
			$thickness 	= $sample_key["thickness"];
			$pressure 	=  $sample_key["pressure"];
			$post_pressure =  $sample_key["post_pressure"];
			$temp 		=  $sample_key["temp"];
			$freq 		=  $sample_key["freq"];
			$termination =  $sample_key["termination"];
			$H 			=  $sample_key["H"];
			$avg_T = $sample_key["T_total"] / $sample_key["count"];
			$avg_M = $sample_key["M_total"] / $sample_key["count"];
			$avg_T = number_format($avg_T,1,'.','');
			$avg_M = number_format($avg_M,1,'.','');
			$asterisk="";
			if($sample_key["count"] > 1){
				$asterisk="*";
			}
			echo "<tr>";
			echo '<td class="w3-amber">'.$sample_name.'</td>';
			echo '<td class="w3-amber">'.$thickness.'</td>';
			echo '<td class="w3-amber">'.$pressure.'</td>';
			echo '<td class="w3-amber">'.$post_pressure.'</td>';
			echo '<td class="w3-amber">'.$temp.'</td>';
			echo '<td class="w3-amber">'.$freq.'</td>';
			echo '<td class="w3-amber">'.$termination.'</td>';
			echo '<td class="w3-amber">'.$H.'</td>';
			echo '<td class="w3-amber">'.$avg_T.'</td>';
			echo '<td class="w3-amber">'.$avg_M." ".$asterisk.'</td>';
			echo "</tr>";
			$outline = "$sample_name\t$thickness\t$pressure\t$post_pressure\t$temp\t$freq\t$termination\t$H\t$avg_T\t$avg_M\t$asterisk\n";
			fwrite($fhout,$outline);
		}
	}
	fclose($fhout);
?>
	</table>
	</div>
	
	<div class="w3-panel w3-border w3-border-black w3-blue">
	<p>
	<b>MvsH Data</b>
	</p>
	</div>
	
	<div class="w3-border w3-border-black">
	<table class="w3-table-all">
		<tr class="w3-blue">
			<th>NAME</th>
			<th>THICK</th>
			<th>P_GROWTH</th>
			<th>P_COOL</th>
			<th>TEMP</th>
			<th>FREQ</th>
			<th>TERM</th>
			<th>TEMP</th>
			<th>H</th>
			<th>Ms</th>
			<th>Hc</th>
			<th>Mi</th>
			<th>Mr</th>
		</tr>
<?php

$fhout = fopen("ML_data_MvsH.txt","w");
$fhout2 = fopen("ML_MvsH_files.txt","w");

unset($sample_array);
$sample_array = array();

// ***************************************************
// Getting list of samples with Ms from MvsH and T~5K
// ***************************************************

$mysqli = mysqli_connect($host, $user, $pass, $db);
$query = "SELECT $SAMPLE_TABLE.sample_name,$SAMPLE_TABLE.thickness,
$SAMPLE_TABLE.pressure,$SAMPLE_TABLE.post_pressure,$SAMPLE_TABLE.temp,
$SAMPLE_TABLE.freq,$SAMPLE_TABLE.termination,$DATA_TABLE.filename,$M_DETAIL_TABLE.temp AS M_temp 
FROM $DATA_TABLE INNER JOIN $SAMPLE_TABLE ON $DATA_TABLE.sample_id=$SAMPLE_TABLE.id 
INNER JOIN $M_DETAIL_TABLE ON ($DATA_TABLE.id=$M_DETAIL_TABLE.file_id AND $M_DETAIL_TABLE.temp<20)
WHERE $DATA_TABLE.data_type=7 ORDER BY $SAMPLE_TABLE.sample_name, $M_DETAIL_TABLE.temp";
$res = mysqli_query($mysqli,$query);
	while($row = $res->fetch_assoc()) {
		$sample = $row['sample_name'];
		$thickness = $row['thickness'];
		$pressure = $row['pressure'];
		$post_pressure = $row['post_pressure'];
		$temp = $row['temp'];
		$freq = $row['freq'];
		$termination = $row['termination'];
		$filename = $row['filename'];
		$M_temp = $row['M_temp'];

		$sample_array[$sample]["name"] = $sample;
		$sample_array[$sample]["thickness"] = $thickness;
		$sample_array[$sample]["pressure"] = $pressure;
		$sample_array[$sample]["post_pressure"] = $post_pressure;
		$sample_array[$sample]["temp"] = $temp;
		$sample_array[$sample]["freq"] = $freq;
		$sample_array[$sample]["termination"] = $termination;

		$H = 0;
		$M = 0;
		$H_max = 0;
		$Ms = 0;
		
// ********************
//  Opening data file
// ********************
		
		$fh = fopen("../data/".$filename,"r");
		
		$line = fgets($fh);
		preg_match('/(,)/',$line,$matches);

		if($matches[0]==","){ # data is formatted the wrong way (csv instead of tab)
			#echo "#############  CLOSING $filename  bad format  >>>>>>>>>>>>>>>>>>>>>>>> \n";
			fclose($fh);
			continue;
		}
		
		$fields = explode("\t",$line);
		$H = trim($fields[0]);
		$M = trim($fields[1]);
		
		$data_flag = 0;
		$data_complete_flag = 0;
		
		$leg1_flag = 1;
		$leg2_flag = 0;
		$leg3_flag = 0;
		$leg4_flag = 0;
		
		$H_abs_max = -99999;
		$H_abs_min = 99999;
		$last_M = $M;
		$last_H = $H;
		$m = 0; // Slope for interpolation of coercive field (Hc)
		$Hc_left = 0;
		$Hc_right = 0;
		$M_init = 0;
		$M_init_flag = 0;
		$Mr = 0;
		$Mr_flag = 0;

		while(!feof($fh)){
			if(!$M_init_flag){ // Get very first M (near H = 0)
				if(is_numeric($M)){
					$M_init = $M;
					$M_init_flag = 1;
				}
			}
			if(is_numeric($H)){
				if(is_numeric($M)){
					if($H > 950 && $H < 1050){
						if($M > $Ms){
							$Ms = $M;
							$H_max = $H;
							$data_flag=1;
						}
					}
					if($leg1_flag){
						if($H > $H_abs_max){
							$H_abs_max = $H;
						}
						else{
							if($H > 950){
								$leg1_flag = 0;
								$leg2_flag = 1;
							}
						}
					}
					if($leg2_flag){
						if($H < 0){
							if(!$Mr_flag){
								// Find remnant M with slope
								$m = ($last_M - $M)/($last_H - $H);
								$Mr = $M - $m*$H;
								$Mr_flag = 1;
							}
						}
						if($M < 0){
							// Find slope now that we crossed x-axis
							$m = ($last_M - $M)/($last_H - $H);
							$Hc_left = -($M/$m) + $H;
							$leg2_flag = 0;
							$leg3_flag = 1;
						}
					}
					if($leg3_flag){
						if($H < $H_abs_min){
							$H_abs_min = $H;
						}
						else{
							$leg3_flag = 0;
							$leg4_flag = 1;
						}
					}
					if($leg4_flag){
						if($M > 0){
							// Find slope now that we crossed x-axis
							$m = ($last_M - $M)/($last_H - $H);
							$Hc_right = -($M/$m) + $H;
							$leg4_flag = 0;
							$data_complete_flag = 1;
						}
					}
				}
			}
			$last_M = $M;
			$last_H = $H;

			$line = fgets($fh);
			$fields = explode("\t",$line);
			$H = trim($fields[0]);
			$M = trim($fields[1]);
		}
		
		fclose($fh);

		if($data_flag == 0){ continue; } # couldn't find any good data around H = 1000 Oe
		if($Ms < 1){ continue; } # M is not in emu/cc
		if($data_complete_flag == 0){ continue; } # Data is missing
		
		if($sample_array[$sample]["count"]){
			$sample_array[$sample]["count"] += 1;
		}
		else{
			$sample_array[$sample]["count"] = 1;
		}
		if($sample_array[$sample]["M_total"]){
			$sample_array[$sample]["M_total"] += $Ms;
			$sample_array[$sample]["H_total"] += $H_max;
			$sample_array[$sample]["M_temp_total"] += $M_temp;
			$sample_array[$sample]["Hc_total"] += $Hc_right - $Hc_left;
			$sample_array[$sample]["M_init_total"] += $M_init;
			$sample_array[$sample]["Mr_total"] += $Mr;
		}
		else{
			$sample_array[$sample]["M_total"] = $Ms;
			$sample_array[$sample]["H_total"] = $H_max;
			$sample_array[$sample]["M_temp_total"] = $M_temp;
			$sample_array[$sample]["Hc_total"] = $Hc_right - $Hc_left;
			$sample_array[$sample]["M_init_total"] = $M_init;
			$sample_array[$sample]["Mr_total"] = $Mr;
		}
		fwrite($fhout2,"$filename \n");
	}
	fclose($fhout2);
	foreach($sample_array as $sample_key){
		if($sample_key["count"] > 0){
			$sample_name = $sample_key["name"];
			$thickness 	= $sample_key["thickness"];
			$pressure 	=  $sample_key["pressure"];
			$post_pressure =  $sample_key["post_pressure"];
			$temp 		=  $sample_key["temp"];
			$freq 		=  $sample_key["freq"];
			$termination =  $sample_key["termination"];
			$avg_H = $sample_key["H_total"] / $sample_key["count"];
			$avg_M = $sample_key["M_total"] / $sample_key["count"];
			$avg_M_temp =  $sample_key["M_temp_total"] / $sample_key["count"];
			$avg_Hc = $sample_key["Hc_total"] / $sample_key["count"];
			$avg_M_init = $sample_key["M_init_total"] / $sample_key["count"];
			$avg_Mr = $sample_key["Mr_total"] / $sample_key["count"];

			$avg_H = number_format($avg_H,1,'.','');
			$avg_M = number_format($avg_M,1,'.','');
			$avg_Hc = number_format($avg_Hc,1,'.','');
			$avg_M_init = number_format($avg_M_init,1,'.','');
			$avg_Mr = number_format($avg_Mr,1,'.','');
			
			$asterisk="";
			if($sample_key["count"] > 1){
				$asterisk="*";
			}
			echo "<tr>";
			echo '<td class="w3-amber">'.$sample_name.'</td>';
			echo '<td class="w3-amber">'.$thickness.'</td>';
			echo '<td class="w3-amber">'.$pressure.'</td>';
			echo '<td class="w3-amber">'.$post_pressure.'</td>';
			echo '<td class="w3-amber">'.$temp.'</td>';
			echo '<td class="w3-amber">'.$freq.'</td>';
			echo '<td class="w3-amber">'.$termination.'</td>';
			echo '<td class="w3-amber">'.$avg_M_temp.'</td>';
			echo '<td class="w3-amber">'.$avg_H.'</td>';
			echo '<td class="w3-amber">'.$avg_M." ".$asterisk.'</td>';
			echo '<td class="w3-amber">'.$avg_Hc.'</td>';
			echo '<td class="w3-amber">'.$avg_M_init.'</td>';
			echo '<td class="w3-amber">'.$avg_Mr.'</td>';
			echo "</tr>";
			$outline = "$sample_name\t$thickness\t$pressure\t$post_pressure\t$temp\t$freq\t$termination\t$M_temp\t$avg_H\t$avg_M\t$avg_Hc\t$avg_M_init\t$avg_Mr\t$asterisk\n";
			fwrite($fhout,$outline);
		}
	}
	fclose($fhout);
?>
	</table>
	</div>
	<div>
		<?php //echo $query ?>
	</div>
</body>
</html>
