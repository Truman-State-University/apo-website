<?php
require_once ('session.php');
require_once ('mysql_access.php');
?>
<!doctype html>
<html>
<head>
    <?php require 'head.php';?>
</head>

<body class="slide" data-type="background" data-speed="5">
    <nav id="nav" role="navigation"></nav>
    <div id="header"></div>
    <div class="row">
        <div class="large-6 medium-9 small-12 columns">
           future service sign up!
           <div class="row">
           $id = $_SESSION['sessionID'];
$position = $_SESSION['sessionposition'];
if($position != "Webmaster" && $position != "VP of Regular Service"){
	//echo("this page is under construction. Please notify the webmaster of any issues");
}
if (!isset($_SESSION['sessionID'])) {
	echo "<p>You need to login before you can see this page.</p>"; 
}else{

function refresh(){
	echo("<meta http-equiv=\"REFRESH\" content=\"0;url=http://apo.truman.edu/service_dashboard.php\">");
}


function register($detail,$occurrence){
	$id = $_SESSION['sessionID'];
	
	$sql = "SELECT length FROM service_details WHERE detail_id = $detail";
	$result = mysql_query($sql);
	while($r = mysql_fetch_array($result)){
		$length = $r['length'];
	}

	$sql = "INSERT INTO service_attendance (detail_id, user_id, occurrence_id, length) 
			VALUES ($detail,$id,$occurrence,'$length')";
	$result = mysql_query($sql);
	if(!$result){
		echo("something went wrong".mysql_error()."<br/>".$sql."<br/>Perhaps someone else signed up for the event.");
	}else{
		refresh();
	}
}

function remove($detail){
	$id = $_SESSION['sessionID'];

	$sql = "DELETE FROM service_attendance WHERE detail_id = $detail AND user_id =  $id AND processed = 0";
	$result = mysql_query($sql);
	if(!$result){
		echo(mysql_error());
	}else{
		refresh();
	}
	
}

function option($occurrence_id){
				$return = "";
					$sql = "SELECT drive FROM service_attendance WHERE occurrence_id = $occurrence_id AND user_id = $id";
					$result = mysql_query($sql);
					while($p = mysql_fetch_array($result)){
						$driveCount = $p['drive'];
					}
					for($u = 0; $u <= 6; $u++){
						if($u==$driveCount){
							$s = "selected";
						}else{
							$s = "";
						}
					$return .= "<option value=$u $s>$u</option>";
				}	
				return $return;
			}
			
function displayListing(){
$id = $_SESSION['sessionID'];
echo "<table border=0 class=\"displayListingTable2\">";
echo "<tr class=\"displayListing2\"><td>event name</td><td>date</td><td></td><td>start</td><td>end</td><td>current</td><td>limit</td><td>hours</td><td></td></tr>";
$sql = "SELECT d.detail_id, d.event_id, d.DOW,
		o.start, o.end, o.length, o.max, e.P_Id,
		e.name, l.user_id, o.theDate, o.occurrence_id,
		c.firstname, c.lastname
		FROM service_details AS d
		JOIN service_leaders AS l
		ON l.detail_id = d.detail_id
		JOIN service_occurrence AS o
		ON o.detail_id = d.detail_id
		JOIN service_events AS e
		ON e.P_Id = d.event_id
		JOIN contact_information AS c
		ON c.id = l.user_id
		WHERE o.active = 1 
		ORDER BY o.theDate, o.start";
$resultO = mysql_query($sql);
	if(!$resultO){
		die("error");
	}else{
		$v = 1;
		while($r = mysql_fetch_array($resultO)){
			$user_id = $r['id'];
			$detail_id = $r['detail_id'];
			$event_id = $r['event_id'];
			$firstname = $r['firstname'];
			$lastname = $r['lastname'];
			$DOW = $r['DOW'];
			$start = $r['start'];
			$end = $r['end'];
			$length = $r['length'];
			$max = $r['max'];
			$name = $r['name'];
			$theDate = $r['theDate'];
			$occurrence_id = $r['occurrence_id'];
			
			$theDate = date('M-d', strtotime($theDate));	

			$sql = "SELECT COUNT(*) AS count FROM service_attendance WHERE detail_id = $detail_id AND occurrence_id = $occurrence_id";
			$result2 = mysql_query($sql);
			while($r = mysql_fetch_array($result2)){
				$count = $r['count'];
			}

			$sql = "SELECT COUNT(*) AS count FROM service_attendance WHERE detail_id = $detail_id AND user_id = $id AND occurrence_id = $occurrence_id";
			$result2 = mysql_query($sql);
			while($r2 = mysql_fetch_array($result2)){
				$num_rows = $r2['count'];
			}
			
			if(($count < $max)||($num_rows == 1)||($max==-1)){
				if($num_rows == 0){
					$m = 1;
					//$message = "<a href=\"http://apo.truman.edu/service_dashboard.php?d=$detail_id&o=$occurrence_id\">sign-up</a>";
					$message = "";
				}else{
					$m = 2;
					//$message = "<a href=\"http://apo.truman.edu/service_dashboard.php?r=$detail_id&o=$occurrence_id\">remove</a>";	
					$message;
				}
			}else{
				$message = "full";
				$drive = "";
			}
			
			if($start > 12){
				$startstr = substr($start, 0,2);
				$startstr -= 12;
				$start = $startstr.substr($start,2,-3)."pm";
			}else{		
				$start = substr($start,0,-3)."am";
			}
			if($end > 12){
				$endstr = substr($end, 0,2);
				$endstr -= 12;
				$end = $endstr.substr($end,2,-3)."pm";
			}else{
				$end = substr($end,0,-3)."am";
			}
			/*
			*/
			$drive = "";
			
			$sql = "SELECT drive FROM service_attendance WHERE occurrence_id = $occurrence_id AND user_id = $id";
					$result = mysql_query($sql);
					while($v = mysql_fetch_array($result)){
						$driveCount = $v['drive'];
			}
			$optionC = "";	
				
			if($m==2){					
				$optionC = option($occurrence_id);
				$drive = 
				"<tr class=\"trNEW\"><td></td><td colspan=\"8\"><form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
				How many seats do you have in your car?
				<select name=\"driveCount\">".$optionC."</select>
				<input type='hidden' name=\"occ\" value =".$occurrence_id." />
				<input type='submit' name=\"Drive\" value='submit'/>
				</form>
				You answered: ".$driveCount."</td></tr>
				";
			}
			if($m!=2){
				$drive = "";
			}

			echo "<tr class=\"trNEW\"><td>$name</td><td>$DOW</td><td>$theDate</td><td>$start</td><td>$end</td><td>$count</td><td>$max</td><td>$length $v $ma</td><td>{$message}</td></tr>";
			echo "<tr><td>Project Leader: </td><td>";
			$sqlPLData = "SELECT d.detail_id, l.*, c.firstname, c.lastname, c.phone FROM service_details AS d JOIN service_leaders AS l ON l.detail_id = d.detail_id JOIN contact_information AS c ON c.id = l.user_id WHERE d.detail_id = $detail_id ORDER BY c.firstname, c.lastname";
			$resultPLData = mysql_query($sqlPLData);
			while($row = mysql_fetch_array($resultPLData)){
				$fname = $row['firstname'];
				$lname = $row['lastname'];
				$phone = $row['phone'];
				echo "$fname $lname";
			}
			echo "</td><td></td><td>";
			$sqlPLData = "SELECT d.detail_id, l.*, c.firstname, c.lastname, c.phone FROM service_details AS d JOIN service_leaders AS l ON l.detail_id = d.detail_id JOIN contact_information AS c ON c.id = l.user_id WHERE d.detail_id = $detail_id ORDER BY c.firstname, c.lastname";
			$resultPLData = mysql_query($sqlPLData);
			while($row = mysql_fetch_array($resultPLData)){
				$fname = $row['firstname'];
				$lname = $row['lastname'];
				$phone = $row['phone'];
				echo "$phone";
			}
			echo "</tr>";
			echo "<tr><td>Volunteers: </td><td>";
			$sqlUserData = "SELECT s.*, c.firstname, c.lastname FROM service_attendance AS s JOIN contact_information AS c ON c.id = s.user_id WHERE occurrence_id = $occurrence_id ORDER BY c.firstname, c.lastname";
			$resultUserData = mysql_query($sqlUserData);
			while($rw = mysql_fetch_array($resultUserData)){
				$fn = $rw['firstname'];
				$ln = $rw['lastname'];
				$dr = $rw['drive'];
				if($dr > 0){$dr = "(".$dr." seats)";}else{ $dr = "";}
				echo "$fn $ln {$dr}<br/>";
			}
			echo"</td>";
			
			echo "<td></td><td>";
			$sqlUserData = "SELECT s.*, c.firstname, c.lastname, phone FROM service_attendance AS s JOIN contact_information AS c ON c.id = s.user_id WHERE occurrence_id = $occurrence_id ORDER BY c.firstname, c.lastname";
			$resultUserData = mysql_query($sqlUserData);
			while($rw = mysql_fetch_array($resultUserData)){
				$fn = $rw['firstname'];
				$ln = $rw['lastname'];
				$dr = $rw['drive'];
				$ph = $rw['phone'];
				if($ph == ""){ $ph = "- - - - - - - - -";}
				echo "$ph<br/>";
			}
			echo"</td></tr>";
			
			echo"$drive";
			
			$m = 0;
		}
	}
echo "</table>";
$sql = "SELECT d.event_id, d.DOW, d.start, d.end, d.length, e.name, o.theDate
FROM service_details AS d
JOIN service_events AS e
ON d.event_id = e.P_Id
JOIN service_attendance AS a
ON a.detail_id = d.detail_id
JOIN service_occurrence AS o
ON o.detail_id = d.detail_id
WHERE a.processed = 0 AND a.occurrence_id = o.occurrence_id AND a.user_id = $id AND NOW() > o.theDate
ORDER BY a.occurrence_id ASC";
$result = mysql_query($sql);

if(mysql_num_rows($result)!=0){
echo "<hr /><h2>Pending Events</h2>";
echo "<table border=0 class=\"displayListingTable\">";
echo "<tr class=\"displayListing\"><td>date</td><td></td><td>name</td><td>hours</td></tr>";
while($r = mysql_fetch_array($result)){
			$event_id = $r['event_id'];
			$DOW = $r['DOW'];
			$start = $r['start'];
			$end = $r['end'];
			$length = $r['length'];
			$name = $r['name'];
			$theDate = $r['theDate'];
			echo "<tr><td>$DOW</td><td>$theDate</td><td>$name</td><td>$length</td></tr>";

}
echo "</table>";
}
}
echo "<h1>Service Sign-Ups</h1>";
echo "<h2>Online sign-ups have been disabled.</h2>";
if(isset($_GET['d'])){
	register($_GET['d'],$_GET['o']);
}elseif(isset($_GET['r'])){
	remove($_GET['r'],$_GET['o']);
}else{
	if(isset($_POST['Drive'])){
		$occ = $_POST['occ'];
		$driveNum = $_POST['driveCount'];
		$sql = "UPDATE service_attendance SET drive = $driveNum WHERE occurrence_id = $occ AND user_id = $id";
		$result = mysql_query($sql);
	}
	displayListing();
}

}
echo("</div>");

    </div>
    <div id="footer"><?php include 'footer.php';?></div>
</body>
</html>
