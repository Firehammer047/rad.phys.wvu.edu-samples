<?php

include 'session.php';
include 'config.php';

$sample_ids = $_GET['sample_ids'];
$x_var = $_GET['x_var'];
$y_var = $_GET['y_var'];
$x_units = "";
$y_units = "";
if($x_var == "Pressure"){$x_units="mTorr";}
if($x_var == "Roughness"){$x_units="nm";}
if($x_var == "Thickness"){$x_units="nm";}
if($y_var == "Pressure"){$y_units="mTorr";}
if($y_var == "Roughness"){$y_units="nm";}
if($y_var == "MfromH"){$y_units="emu/cc";}
if($y_var == "MfromT"){$y_units="emu/cc";}


$mysqli = mysqli_connect($host, $user, $pass, $db);

$data_file = "temp/advanced_graph_data.txt";
$fp = fopen($data_file,'w');
fwrite($fp, "$x_var\t$y_var\t$x_units\t$y_units\n");
$i=0;
foreach($sample_ids as $v){
	$x=0;
	$y=0;
	$query = "SELECT sample_name FROM $SAMPLE_TABLE WHERE id=$v";	
	$res = mysqli_query($mysqli, $query);
	if ($row = $res->fetch_assoc()) {
		$sample = $row['sample_name'];
	}
#-----X values----	
	if($x_var == "Pressure"){
		$query = "SELECT pressure as x_var FROM $SAMPLE_TABLE WHERE id=$v";	
	}
	if($x_var == "Roughness"){
		$query = "SELECT roughness as x_var FROM $SAMPLE_TABLE WHERE id=$v";	
	}
	if($x_var == "Thickness"){
		$query = "SELECT thickness as x_var FROM $SAMPLE_TABLE WHERE id=$v";	
	}
	$res = mysqli_query($mysqli, $query);
	if ($row = $res->fetch_assoc()) {
		$x = $row['x_var'];
	}
#-----Y values----	
	if($y_var == "Roughness"){
		$query = "SELECT roughness as y_var FROM $SAMPLE_TABLE WHERE id=$v";	
		$res = mysqli_query($mysqli, $query);
		if ($row = $res->fetch_assoc()) {
			$y = $row['y_var'];
		}
		fwrite($fp, "$sample\t$x\t$y\n");
	}
	if($y_var == "Pressure"){
		$query = "SELECT pressure as y_var FROM $SAMPLE_TABLE WHERE id=$v";	
		$res = mysqli_query($mysqli, $query);
		if ($row = $res->fetch_assoc()) {
			$y = $row['y_var'];
		}
		fwrite($fp, "$sample\t$x\t$y\n");
	}
	if($y_var == "MfromH"){
		$query = "SELECT $DATA_TABLE.filename,$M_DETAIL_TABLE.temp AS M_temp
		FROM $DATA_TABLE INNER JOIN $M_DETAIL_TABLE ON ($DATA_TABLE.id=$M_DETAIL_TABLE.file_id AND $M_DETAIL_TABLE.temp<20)
		WHERE ($DATA_TABLE.data_type=7 AND $DATA_TABLE.sample_id=$v)
		";	
		$res = mysqli_query($mysqli, $query);
		while ($row = $res->fetch_assoc()) {
			$filename = $row['filename'];
			
			$H = 0;	
			$M = 0;
			$Ms = 0;
			
			$fh = fopen("data/".$filename,"r");
			$line = fgets($fh);
			while(!feof($fh)){
				$fields = explode("\t",$line);
				$H = trim($fields[0]);
				$M = trim($fields[1]);
				if(is_numeric($H)){
					if($H > 1990 AND $H < 2010){
						if(is_numeric($M)){
							$Ms = $M;
						}
					}
				}
				$line = fgets($fh);
			}
			#$Ms = number_format($Ms,1);
			fclose($fh);
			$y = $Ms;
			fwrite($fp, "$sample\t$x\t$y\n");
		}
	}
	if($y_var == "MfromT"){
		$query = "SELECT $DATA_TABLE.filename FROM $DATA_TABLE INNER JOIN $M_DETAIL_TABLE 
		ON ($DATA_TABLE.id=$M_DETAIL_TABLE.file_id AND $M_DETAIL_TABLE.measure_H=1000)
		WHERE ($DATA_TABLE.data_type=8 AND $DATA_TABLE.sample_id=$v)";	
		$res = mysqli_query($mysqli, $query);
		while ($row = $res->fetch_assoc()) {
			$filename = $row['filename'];
			
			$T = 0;	
			$M = 0;
			
			$fh = fopen("data/".$filename,"r");
			$line = fgets($fh);
			
			preg_match('/(,)/',$line,$matches);
			if($matches[0]==","){
				fclose($fh);
			    continue;
			}
			
			$fields = explode("\t",$line);
			$T = trim($fields[0]);
			$M = trim($fields[1]);
			
			if($T<20.0){
				while($T<4.9){
					$line = fgets($fh);
					$fields = explode("\t",$line);
					$T = trim($fields[0]);
					$M = trim($fields[1]);
				}
				while(!(is_numeric($M))){
					$line = fgets($fh);
					$fields = explode("\t",$line);
					$T = trim($fields[0]);
					$M = trim($fields[1]);
				}
				$line = fgets($fh);
			}
			fclose($fh);
			$y = $M;
			fwrite($fp, "$sample\t$x\t$y\n");
		}
	}

	$i++;
}
fclose($fp);
$filemtime = filemtime($data_file);
$out = shell_exec("./make_advanced_graph.py");

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
<link rel="stylesheet" href="https://cdn.pydata.org/bokeh/release/bokeh-0.12.11.min.css" type="text/css" />
<script type="text/javascript" src="https://cdn.pydata.org/bokeh/release/bokeh-0.12.11.min.js"></script>
<?php readfile("temp/script.html"); ?>
</head>
<body class="w3-content w3-amber">
	<div class="w3-cell-row">
		<div class="w3-container w3-cell">
			<p><h3>Holcomb Group DB - Graph Data: Advanced</h3></p>
		</div>
		<div class="w3-container w3-cell w3-right-align">
			<p><a href="logout.php" class="w3-button w3-red w3-round-large">Logout</a></p>
		</div>
	</div>
	
	<?php readfile("menu.html"); ?>
		
<form enctype="multipart/form-data" action="graph_advanced_data-bokeh.php" method="GET">
	<div class="w3-border w3-border-black">
	<table class="w3-table">
		<tr class="w3-blue">
			<th>Y Axis</th>
			<th>X Axis</th>
			<th></th>
		</tr>
		<tr class="w3-amber">
			<td>
			<select name="y_var">
				<option value="Roughness" <?php if($y_var == "Roughness"){echo "selected";}?>>Roughness</option>
				<option value="Pressure" <?php if($y_var == "Pressure"){echo "selected";}?>>Growth Pressure</option>
				<option value="MfromT" <?php if($y_var == "MfromT"){echo "selected";}?>>M from MvsT</option>
				<option value="MfromH" <?php if($y_var == "MfromH"){echo "selected";}?>>M from MvsH</option>
			</select>
			</td>
			<td>
			<select name="x_var">
				<option value="Thickness" <?php if($x_var == "Thickness"){echo "selected";}?>>Thickness</option>
				<option value="Pressure" <?php if($x_var == "Pressure"){echo "selected";}?>>Growth Pressure</option>
				<option value="Roughness" <?php if($x_var == "Roughness"){echo "selected";}?>>Roughness</option>
			</select>
			</td>
			<td>
				<input type="submit" class="w3-button w3-blue w3-round-large" value="Graph"/>
			</td>
		</tr>
		<tr class="w3-amber">
			<td colspan="2">
				<?php readfile("temp/graph.html"); ?>
			</td>
			<td>
				<select name="sample_ids[]" multiple size="30">
				<?php
					$where = 1;
					$res = mysqli_query($mysqli, "SELECT id, sample_name FROM $SAMPLE_TABLE WHERE $where ORDER BY sample_name");
					while ($row = $res->fetch_assoc()) {
						$id = $row['id'];
						$sample_name = $row['sample_name'];
						echo '<option value="'.$id.'"';
						if(in_array($id, $sample_ids)){echo " selected";}
						echo '>'.$sample_name.'</option>';
					}
				?>
				</select>
			</td>
		</tr>
	</table>
	</div>
</form>
</body>
</html>
