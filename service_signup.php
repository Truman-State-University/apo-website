﻿<?php
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

<?php
if (!isset($_SESSION['sessionID'])) {
  echo "<div class=\"row\"><div class=\"small-12 columns\"><p>You need to login before you can see this page.</p></div></div>";
}else{

$id = $_SESSION['sessionID'];

$sql = "UPDATE service_occurrence SET active = 0 WHERE theDate < DATE(NOW())";
$result = $db->query($sql);

$datew = date('w');
$dateG = date('G');
if($datew == 6 && $dateG >= 13){//Friday
  $sql = "UPDATE service_occurrence SET active = 1 WHERE active = 3";
  $sql2 = "UPDATE service_occurrence SET active = 2 WHERE active = 4";
  $result = $db->query($sql);
  $result = $db->query($sql2);
}

$sql = "SELECT COUNT(*) AS count FROM service_attendance WHERE processed = 1";
$result = $db->query($sql);
while($r = mysqli_fetch_array($result)){
  $count = $r['count'];
}
if($count > 0){
  require_once('service_hours_logger.php');
}

function refresh($occurrence){
  echo("<meta http-equiv=\"REFRESH\" content=\"0;url=service_signup.php#$occurrence\">");
}


function register($detail,$occurrence){
  include('mysql_access.php');
  $id = $_SESSION['sessionID'];

  $sql = "SELECT length FROM service_details WHERE detail_id = $detail";
  $result = $db->query($sql);
  while($r = mysqli_fetch_array($result)){
    $length = $r['length'];
  }

  $sql = "INSERT INTO service_attendance (detail_id, user_id, occurrence_id, length)
      VALUES ($detail,$id,$occurrence,'$length')";
  $result = $db->query($sql);
  if(!$result){
    echo("something went wrong".mysqli_error()."<br/>".$sql."<br/>Perhaps someone else signed up for the event.");
  }else{
    refresh($occurrence);
  }
}

function remove($detail, $occurrence){
  include('mysql_access.php');
  $id = $_SESSION['sessionID'];

  $sql = "DELETE FROM service_attendance WHERE detail_id = $detail AND user_id =  $id AND processed = 0";
  $result = $db->query($sql);
  if(!$result){
    echo(mysqli_error());
  }else{
    refresh($occurrence);
  }

}

function option($occurrence_id, $driveCount){
        include('mysql_access.php');
        $return = "";
          for($u = 0; $u <= 6; $u++){
            if($u==$driveCount){
              $s = "selected=\"selected\"";
            }else{
              $s = "";
            }
          $return .= "<option value=$u $s>$u</option>";
        }
        return $return;
      }

function displayListing(){
include('mysql_access.php');
$id = $_SESSION['sessionID'];
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
$result = $db->query($sql);

if(mysqli_num_rows($result)!=0){
echo "<div class=\"row\">";
echo "<div class=\"small-12 columns\">";
echo "<h2>Currently Attending:</h2>";
echo "</div></div>";
while($r = mysqli_fetch_array($result)){
      $event_id = $r['event_id'];
      $DOW = $r['DOW'];
      $start = $r['start'];
      $end = $r['end'];
      $length = $r['length'];
      $name = $r['name'];
      $theDate = $r['theDate'];
      echo "<div class=\"row\">";
      echo "<div class=\"small-2 columns\">$DOW</div><div class=\"small-2 columns\">$theDate</div><div class=\"small-2 columns\">$start</div><div class=\"small-4 columns end\">$name</div>";
      echo "</div>";

}
}

//echo "<table border=0 class=\"displayListingTable2\">";
//echo "<tr class=\"displayListing2\"><td>event name</td><td>date</td><td></td><td>start</td><td>end</td><td>current</td><td>limit</td><td>hours</td><td></td></tr>";
//new frontend attempt here
/*echo <<<END
<div class="row">
<div class="small-2 columns">Event name</div>
<div class="small-3 columns">Date</div>
<div class="small-1 columns">Start</div>
<div class="small-1 columns">End</div>
<div class="small-1 columns">Current</div>
<div class="small-1 columns">Limit</div>
<div class="small-1 columns">Hours</div>
<div class="small-2 columns">Event name</div>
</div>
END;
*/
echo <<<END
  <div class="row">
    <div class="small-12 columns">
      <h1>Service Signup</h1>
    </div>
  </div>
  <div class="row">
    <ul class="large-block-grid-2 small-block-grid-1">
END;

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
$resultO = $db->query($sql);
  if(!$resultO){
    die("error 0");
  }else{
    $v = 1;
    while($r = mysqli_fetch_array($resultO)){
      //$user_id = $r['id'];
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
      $result2 = $db->query($sql);
      while($r = mysqli_fetch_array($result2)){
        $count = $r['count'];
      }

      $sql = "SELECT COUNT(*) AS count FROM service_attendance WHERE detail_id = $detail_id AND user_id = $id AND occurrence_id = $occurrence_id";
      $result2 = $db->query($sql);
      while($r2 = mysqli_fetch_array($result2)){
        $num_rows = $r2['count'];
      }

      if(($count < $max)||($num_rows == 1)||($max==-1)){
        if($num_rows == 0){
          $m = 1;
          $message = "<a href=\"service_signup.php?d=$detail_id&o=$occurrence_id\" class=\"button expand success\">Sign up</a>";
          //$message = "";
        }else{
          $m = 2;
          $message = "<a href=\"service_signup.php?r=$detail_id&o=$occurrence_id\" class=\"button expand\">Remove</a>";
          //$message;
        }
      }else{
        $message = "<a href=\"#\" class=\"button alert expand\" onclick=\"return false;\">Full</a>";
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
          $result = $db->query($sql);
          while($v = mysqli_fetch_array($result)){
            $driveCount = $v['drive'];
      }
      $optionC = "";

      if($m==2){
        $optionC = option($occurrence_id, $driveCount);
        /*$drive =
        "<tr class=\"trNEW\"><td></td><td colspan=\"8\"><form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
        How many seats do you have in your car?
        <select name=\"driveCount\">".$optionC."</select>
        <input type='hidden' name=\"occ\" value =".$occurrence_id." />
        <input type='submit' name=\"Drive\" value='submit'/>
        </form>
        You answered: ".$driveCount."</td></tr>
        ";*/
        $drive =
        "<div class=\"row\">
        <div class=\"small-4 columns\">
        <form method=\"post\" action=\"$_SERVER[PHP_SELF]#$occurrence_id\">
        Seats available:
        </div>
        <div class=\"small-4 columns\">
        <select name=\"driveCount\">".$optionC."</select>
        </div>
        <input type='hidden' name=\"occ\" value =".$occurrence_id." />
        <div class=\"small-4 columns\">
        <input type='submit' name=\"Drive\" value='Change' class=\"button medium\"/>
        </div>
        </form>
        </div>
        ";
      }
      if($m!=2){
        $drive = "";
      }

      //echo "<tr class=\"trNEW\"><td>$name</td><td>$DOW</td><td>$theDate</td><td>$start</td><td>$end</td><td>$count</td><td>$max</td><td>$length $v $ma</td><td>{$message}</td></tr>";
      //echo "<tr><td>Project Leader: </td><td>";
      echo "<li>";
      echo "<div class=\"small-12 columns\">";
      echo "<div class=\"row\"><div class=\"small-12 columns\"><h2 id=\"$occurrence_id\">$name</h2></div></div>";
      echo "<div class=\"row\"><div class=\"small-4 columns\"><b>Date</b><br>$DOW<br>$theDate</div><div class=\"small-2 columns\"><b>Start</b><br>$start</div><div class=\"small-2 columns\"><b>End</b><br>$end</div><div class=\"small-2 columns text-center\"><b>Hours</b><br>$length $v</div><div class=\"small-2 columns text-center\"><b>Spots</b><br>$max</div></div>";
      echo "<div class=\"row\"><div class=\"small-8 columns\">Project Leader: ";
      $sqlPLData = "SELECT d.detail_id, l.*, c.firstname, c.lastname, c.phone FROM service_details AS d JOIN service_leaders AS l ON l.detail_id = d.detail_id JOIN contact_information AS c ON c.id = l.user_id WHERE d.detail_id = $detail_id ORDER BY c.firstname, c.lastname";
      $resultPLData = $db->query($sqlPLData);
      while($row = mysqli_fetch_array($resultPLData)){
        $fname = $row['firstname'];
        $lname = $row['lastname'];
        $phone = $row['phone'];
        echo "$fname $lname";
      }
      //echo "</td><td></td><td>";
      echo "</div><div class=\"small-4 columns\">";
      $sqlPLData = "SELECT d.detail_id, l.*, c.firstname, c.lastname, c.phone FROM service_details AS d JOIN service_leaders AS l ON l.detail_id = d.detail_id JOIN contact_information AS c ON c.id = l.user_id WHERE d.detail_id = $detail_id ORDER BY c.firstname, c.lastname";
      $resultPLData = $db->query($sqlPLData);
      while($row = mysqli_fetch_array($resultPLData)){
        $fname = $row['firstname'];
        $lname = $row['lastname'];
        $phone = $row['phone'];
        echo "$phone";
      }
      //echo "</tr>";
      //echo "<tr><td>Volunteers: </td><td>";
      echo "</div></div>";
      echo "<div class=\"row\"><div class=\"medium-5 small-6 medium-offset-2 small-offset-1 columns\">";
      $sqlUserData = "SELECT s.*, c.firstname, c.lastname FROM service_attendance AS s JOIN contact_information AS c ON c.id = s.user_id WHERE occurrence_id = $occurrence_id ORDER BY c.firstname, c.lastname";
      $resultUserData = $db->query($sqlUserData);
      while($rw = mysqli_fetch_array($resultUserData)){
        $fn = $rw['firstname'];
        $ln = $rw['lastname'];
        $dr = $rw['drive'];
        if($dr > 0){$dr = "(".$dr.")";}else{ $dr = "";}
        echo "$fn $ln {$dr}<br/>";
      }
      //echo "</td>";
      echo "</div>";

      //echo "<td></td><td>";
      echo "<div class=\"small-5 columns\">";
      $sqlUserData = "SELECT s.*, c.firstname, c.lastname, phone FROM service_attendance AS s JOIN contact_information AS c ON c.id = s.user_id WHERE occurrence_id = $occurrence_id ORDER BY c.firstname, c.lastname";
      $resultUserData = $db->query($sqlUserData);
      while($rw = mysqli_fetch_array($resultUserData)){
        $fn = $rw['firstname'];
        $ln = $rw['lastname'];
        $dr = $rw['drive'];
        $ph = $rw['phone'];
        if($ph == ""){ $ph = "- - - - - - - - -";}
        echo "$ph <br>";
      }
      //echo "</td></tr>";
      echo "</div></div>";
      echo "<div class=\"row\"><div class=\"small-8 small-centered columns\">$drive</div></div>";
      echo "<div class=\"row\"><div class=\"small-12 columns\">{$message}</div></div>";
      echo "</div>";
      echo "</li>";
      $m = 0;
    }
  }
//echo "</table>";
echo "</ul></div>";
}

if(isset($_GET['d'])){
  register($_GET['d'],$_GET['o']);
}elseif(isset($_GET['r'])){
  remove($_GET['r'],$_GET['o']);
}else{
  if(isset($_POST['Drive'])){
    $occ = $_POST['occ'];
    $driveNum = $_POST['driveCount'];
    $sql = "UPDATE service_attendance SET drive = $driveNum WHERE occurrence_id = $occ AND user_id = $id";
    $result = $db->query($sql);
  }
  displayListing();
}

}
echo("</div>");
?>
    </div>
    <div id="footer"><?php include 'footer.php';?></div>
</body>
</html>
