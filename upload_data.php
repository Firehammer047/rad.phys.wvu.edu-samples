<?php
	
	include 'session.php';
	include 'config.php';

	$mysqli = mysqli_connect($host, $user, $pass, $db);
	
	$uploaddir = $UPLOAD_DIR;
	$file_flag = 0;
	$flag = 0;

	$id = htmlspecialchars($_POST["id"]);
	$sample_name = htmlspecialchars($_POST["sample_name"]);
	$data_type = htmlspecialchars($_POST["data_type"]);
	$exp_date = htmlspecialchars($_POST["exp_date"]);
	$comment = addslashes(htmlspecialchars($_POST["comment"]));

	$xrd_instrument = htmlspecialchars($_POST["xrd_instrument"]);
	$xrd_mono = htmlspecialchars($_POST["xrd_mono"]);
	$xrd_peak = htmlspecialchars($_POST["xrd_peak"]);
	$xrd_layers = htmlspecialchars($_POST["xrd_layers"]);
	$xrd_t1 = htmlspecialchars($_POST["xrd_t1"]);
	$xrd_r1 = htmlspecialchars($_POST["xrd_r1"]);
	$xrd_t2 = htmlspecialchars($_POST["xrd_t2"]);
	$xrd_r2 = htmlspecialchars($_POST["xrd_r2"]);
	$xrd_t3 = htmlspecialchars($_POST["xrd_t3"]);
	$xrd_r3 = htmlspecialchars($_POST["xrd_r3"]);
	
	$m_cool_H = htmlspecialchars($_POST["m_cool_H"]);
	$m_measure_H = htmlspecialchars($_POST["m_measure_H"]);
	$m_temp = htmlspecialchars($_POST["m_temp"]);

	$afm_type = htmlspecialchars($_POST["afm_type"]);
	$afm_size = htmlspecialchars($_POST["afm_size"]);
	$afm_rough = htmlspecialchars($_POST["afm_rough"]);
	$afm_phase = htmlspecialchars($_POST["afm_phase"]);
	$afm_freq = htmlspecialchars($_POST["afm_freq"]);
	$afm_force = htmlspecialchars($_POST["afm_force"]);
	$afm_location = htmlspecialchars($_POST["afm_location"]);
	
	$original_name = basename($_FILES['data_file']['name']);
	$original_ext = substr($original_name, -3);
	$file_size = $_FILES['data_file']['size'];
	
	if($_POST["id"] == ""){
		$id = htmlspecialchars($_GET["id"]);
		$sample_name = htmlspecialchars($_GET["sample_name"]);
	}
	
	// WE HAVE A FILE
	if($file_size > 0){
		$query = "SELECT * from data_types WHERE id=".$data_type;
		$res = mysqli_query($mysqli, $query);
		while ($row = $res->fetch_assoc()) {
			$type = preg_replace('/\s/', '_', $row['type']);
			$subtype = preg_replace('/\s/', '_', $row['subtype']);
		}
		$now = time();
		$file_prefix = $sample_name."-".$type;
		if($subtype != ""){
			$file_prefix .= "_".$subtype;
		}
		
		if(in_array($data_type,$AFM_ARR) || in_array($data_type,$RHEED_IMG_ARR)){
			$new_file_name = $file_prefix."-".$now.".".$original_ext;
		}
		else{
			$new_file_name = $file_prefix."-".$now.".dat";
		}
		
		$uploadfile = $uploaddir.$new_file_name;
		
		if (move_uploaded_file($_FILES['data_file']['tmp_name'], $uploadfile)) {
			$file_flag=1;
			
			$query = "INSERT INTO $DATA_TABLE VALUES (
			'',
			'$id',
			'$data_type',
			'$exp_date',
			'$now',
			'$new_file_name',
			'$comment')";
			if(mysqli_query($mysqli, $query)){
				$flag=1;
			}
			// Add xrd details to xrd table
			if(in_array($data_type,$XRAY_ARR)){
				$query = "INSERT INTO $XRD_DETAIL_TABLE VALUES (
				LAST_INSERT_ID(),
				'$xrd_instrument',
				'$xrd_mono',
				'$xrd_peak',
				'$xrd_layers',
				'$xrd_t1',
				'$xrd_t2',
				'$xrd_t3',
				'$xrd_r1',
				'$xrd_r2',
				'$xrd_r3')";
				mysqli_query($mysqli, $query);
			}
			// Add mag details to mag table
			if(in_array($data_type,$MAG_ARR)){
				$query = "INSERT INTO $M_DETAIL_TABLE VALUES (
				LAST_INSERT_ID(),
				'$m_cool_H',
				'$m_measure_H',
				'$m_temp')";
				mysqli_query($mysqli, $query);
			}
			// Add afm details to afm table
			if(in_array($data_type,$AFM_IMG_ARR)){
				$query = "INSERT INTO $AFM_DETAIL_TABLE VALUES (
				LAST_INSERT_ID(),
				'$afm_type',
				'$afm_size',
				'$afm_rough',
				'$afm_phase',
				'$afm_freq',
				'$afm_force',
				'$afm_location')";
				mysqli_query($mysqli, $query);
			}
		}
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
<style>
a:link {
	text-decoration: none;
}
</style>
</head>
<body class="w3-content w3-amber">
	<div class="w3-cell-row">
		<div class="w3-container w3-cell">
			<p><h3>Holcomb Group DB - Upload Data</h3></p>
		</div>
		<div class="w3-container w3-cell w3-right-align">
			<p><a href="logout.php" class="w3-button w3-red w3-round-large">Logout</a></p>
		</div>
	</div>
	
	<?php readfile("menu.html"); ?>
		
	<div class="w3-panel w3-border w3-border-black w3-hover-blue">
		<h2><b>
		<a href="show_sample.php?id=<?php echo "$id"; ?>">
		<?php echo "$sample_name"; ?>
		</a>
		</b></h2>
	</div>
<script>
function in_list(val, list){
	for(var i=0; i < list.length; i++){
		if(val == list[i]){
			return true;
		}
	}
	return false;
}
function validate(){
	
	var mydate = document.getElementById("idexpdate");
	var pattern = /\d\d\d\d-\d\d-\d\d/;
	if(!pattern.test(mydate.value)){
		alert("Date must be in YYYY-MM-DD format");
		mydate.focus();
		return false;
	}
	
	var AFM_list = [11];
	var MAG_list = [7,8];
	var XRAY_list = [1,2,3,4,5];

	var mydatatype = document.getElementById("iddatatype");
	
	if(in_list(mydatatype.value,AFM_list)){
		myafmsize = document.getElementById('idafmsize');
		if(myafmsize.value.length == 0){
			alert("Please fill out AFM details");
			document.getElementById('idafmdetail').style.display='block';
			myafmsize.focus();
			return false;
		}
		myafmtype = document.getElementById('idafmtype');
		if(myafmtype.value == "Height"){
			myafmrough = document.getElementById('idafmrough');
			if(myafmrough.value.length == 0){
				alert("Please fill out AFM details");
				myafmrough.focus();
				return false;
			}
		}
		if(myafmtype.value == "Phase"){
			myafmphase = document.getElementById('idafmphase');
			if(myafmphase.value.length == 0){
				alert("Please fill out AFM details");
				myafmphase.focus();
				return false;
			}
		}
	}


	if(in_list(mydatatype.value,MAG_list)){
		mymcoolh = document.getElementById('idmcoolh');
		if(mymcoolh.value.length == 0){
			alert("Please fill out Magnetic details");
			document.getElementById('idmdetail').style.display='block';
			return false;
		}
	}
	
	if(in_list(mydatatype.value,XRAY_list)){
		document.getElementById('idxrddetail').style.display='block';
		
		if(mydatatype.value == 5){
			myxrdpeak = document.getElementById('idxrdpeak');
			if(myxrdpeak.value.length == 0){
				alert("Please fill out XRD RSM details");
				document.getElementById('idrsmdetail').style.display='block';
				myxrdpeak.focus();
				return false;
			}
		}
		if(mydatatype.value == 2){
			myxrdlayers = document.getElementById('idxrdlayers');
			if(myxrdlayers.value.length == 0){
				alert("Please fill out XRR LA details");
				document.getElementById('idladetail').style.display='block';
				myxrdlayers.focus();
				return false;
			}
		}
	
	}
	
	return true;
}
</script>


<form enctype="multipart/form-data" action="upload_data.php" method="POST" onsubmit="return validate()">
	<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
	<input type="hidden" name="id" value="<?php echo $id; ?>" />
	<input type="hidden" name="sample_name" value="<?php echo $sample_name; ?>" />

<div class="w3-row-padding">
<div class="w3-third">
	
	<div class="w3-panel w3-border w3-border-black">
		<p><b>Data type</b></p>
		<p>
			<select name="data_type" id="iddatatype">
			<?php
			$query = "SELECT * from data_types ORDER BY type";
			$res = mysqli_query($mysqli, $query);
			while ($row = $res->fetch_assoc()) {
				$type_id = $row['id'];
				$type = $row['type'];
				$subtype = $row['subtype'];
				
				echo '<option value="'.$type_id.'">';
				echo $type;
				if($subtype != ""){ echo " $subtype";}
				echo '</option>';
			}
			?>
			</select>
		</p>
	</div>
	<div class="w3-panel w3-border w3-border-black">
		<p><b>Data file</b></p>
		<p><input type="file" name="data_file"></p>
	</div>
	<div class="w3-panel w3-border w3-border-black">
		<p><b>Date of experiment</b></p>
		<p><input type="text" name="exp_date" id="idexpdate"><br><b>(YYYY-MM-DD)</b></p>
	</div>
	<div class="w3-panel w3-border w3-border-black">
		<p><b>Comment</b></p>
		<p><input type="text" name="comment"></p>
	</div>
</div>

<div class="w3-rest">
<!-- AFM details -->	
	<div class="w3-panel w3-border w3-border-black w3-hover-blue" >
		<p onclick="document.getElementById('idafmdetail').style.display='block';"><b>AFM IMAGE DETAILS</b></p>
	</div>
	<div id="idafmdetail" class="w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Image Type</th>
				<th>Location</th>
				<th>FOV</th>
			</tr>
			<tr class="w3-amber">
				<td>
				<select name="afm_type" id="idafmtype">
					<option value="Height">Height</option>
					<option value="Phase">Phase</option>
					<option value="Linescan">Linescan</option>
					<option value="Amplitude">Amplitude</option>
					<option value="Z-position">Z-position</option>
				</select>
				</td>
				<td>
				<select name="afm_location">
					<option value="">Don't know</option>
					<option value="Center">Center</option>
					<option value="Not Center">Not Center</option>
				</select>
				</td>
				<td><input type="text" name="afm_size" size="5" id="idafmsize"> um (micrometers)</td>
			</tr>
			<tr class="w3-amber">
				<th>Roughness</th>
				<th>Delta Phase</th>
				<th>Tip Freq</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="afm_rough" size="5" id="idafmrough"> nm</td>
				<td><input type="text" name="afm_phase" size="5" id="idafmphase"> degrees</td>
				<td><input type="text" name="afm_freq" size="5"> kHz</td>
			</tr>
			<tr class="w3-amber">
				<th>Force Constant</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="afm_force" size="5"> N/m</td>
			</tr>
		</table>
	</div>
<!-- -->	
<!-- Mag details -->	
	<div class="w3-panel w3-border w3-border-black w3-hover-blue" >
		<p onclick="document.getElementById('idmdetail').style.display='block';"><b>MAGNETIC DATA DETAILS</b></p>
	</div>
	<div id="idmdetail" class="w3-panel w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Cooling Field</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="m_cool_H" id="idmcoolh"> Oe</td>
			</tr>
		</table>
		<table class="w3-table">
			<tr class="w3-amber">
				<td><input type="button" class="w3-button w3-blue w3-round-large" value="MvsH"
				onclick="document.getElementById('idmvshdetail').style.display='block';
				document.getElementById('idmvstdetail').style.display='none'"
				/></td>
				<td><input type="button" class="w3-button w3-blue w3-round-large" value="MvsT"
				onclick="document.getElementById('idmvstdetail').style.display='block';
				document.getElementById('idmvshdetail').style.display='none'"
				/></td>
			</tr>
		</table>
		</p>
	</div>
	<div id="idmvshdetail" class="w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Temp</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="m_temp"> K</td>
			</tr>
		</table>
	</div>
	<div id="idmvstdetail" class="w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Measurement Field</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="m_measure_H"> Oe</td>
			</tr>
		</table>
	</div>
<!-- -->	
<!-- XRD details -->	
	<div class="w3-panel w3-border w3-border-black w3-hover-blue" >
		<p onclick="document.getElementById('idxrddetail').style.display='block';"><b>XRAY DATA DETAILS</b></p>
	</div>
	<div id="idxrddetail" class="w3-panel w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Instrument</th>
				<th>Monochrometer</th>
			</tr>
			<tr class="w3-amber">
				<td>
				<select name="xrd_instrument">
					<option value="">Don't know</option>
					<option value="Bruker">Bruker</option>
					<option value="Rigaku">Rigaku</option>
				</select>
				</td>
				<td>
				<select name="xrd_mono">
					<option value="-1">Don't know</option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
				</td>
			</tr>
			<tr class="w3-amber">
				<td><input type="button" class="w3-button w3-blue w3-round-large" value="XRD RSM"
				onclick="document.getElementById('idrsmdetail').style.display='block';
				document.getElementById('idladetail').style.display='none'"
				/></td>
				<td><input type="button" class="w3-button w3-blue w3-round-large" value="XRR LA"
				onclick="document.getElementById('idladetail').style.display='block';
				document.getElementById('idrsmdetail').style.display='none'"
				/></td>
			</tr>
		</table>
		</p>
	</div>
	
	<div id="idrsmdetail" class="w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Peak</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="xrd_peak" id="idxrdpeak"><br>
				(103 or 002 typically)</td>
			</tr>
		</table>
	</div>
	<div id="idladetail" class="w3-border w3-border-black" style="display:none">
		<table class="w3-table">
			<tr class="w3-amber">
				<th>Layers</th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="xrd_layers" id="idxrdlayers"><br>
				(1, 2 or 3)</td>
			</tr>
			<tr class="w3-amber">
				<th>Thickness 1</th>
				<th>Thickness 2</th>
				<th>Thickness 3</th>
				<th></th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="xrd_t1"> nm</td>
				<td><input type="text" name="xrd_t2"> nm</td>
				<td><input type="text" name="xrd_t3"> nm</td>
				<td></td>
			</tr>
			<tr class="w3-amber">
				<th>Roughness 1</th>
				<th>Roughness 2</th>
				<th>Roughness 3</th>
				<th></th>
			</tr>
			<tr class="w3-amber">
				<td><input type="text" name="xrd_r1"> nm</td>
				<td><input type="text" name="xrd_r2"> nm</td>
				<td><input type="text" name="xrd_r3"> nm</td>
				<td></td>
			</tr>
		</table>
	</div>
<!-- -->	
</div>
</div>

	<div class="w3-panel w3-border w3-border-black">
		<p><input type="submit" class="w3-button w3-blue w3-round-large" value="Upload"/></p>
	</div>
</form>

	<div class="w3-border w3-border-black">
	<table class="w3-table-all">
		<tr class="w3-amber">
			<th>MEASUREMENT TYPE</th>
			<th>EXPERIMENT DATE</th>
			<th>FILE</th>
			<th>COMMENT</th>
		</tr>

	<?php
		$query = "SELECT filename,exp_date,comment,type,subtype from $DATA_TABLE INNER JOIN data_types ON $DATA_TABLE.data_type=data_types.id WHERE $DATA_TABLE.sample_id=".$id;
		$res = mysqli_query($mysqli, $query);
		while ($row = $res->fetch_assoc()) {
			$filename = $row['filename'];
			$exp_date = $row['exp_date'];
			$comment = $row['comment'];
			$type = $row['type'];
			$subtype = $row['subtype'];
		
		echo '<tr class="w3-hover-blue">';
		echo '<td>';
		echo "$type";
		echo " $subtype";
		echo '</td><td>';
		echo "$exp_date";
		echo '</td><td>';
		echo '<a href="data/'.$filename.'">';
		echo "$filename";
		echo "</a>";
		echo '</td><td>';
		echo "$comment";
		echo '</td>';
		echo '</tr>';
		}
	?>
	</table>
	</div>
</body>
</html>

