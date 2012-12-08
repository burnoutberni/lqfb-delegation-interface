<?
//URL des API-Servers
$base_url = "https://lfapi.piratenpartei.at/";

//API-Key aus Form
$api_key = $_POST["api_key"];

//Logout => Löscht Session-Key aus Cookie und $session_key
$logout = $_GET["logout"];
if($logout == "true"){
	setcookie("session_key", "");
	$session_key = "";
} else{
	$session_key = $_COOKIE["session_key"];
}

//Delegationen aus myModal
$deleg_unit = $_POST["deleg_unit"];
$deleg_area = $_POST["deleg_area"];
$deleg_member = $_POST["deleg_member"];

//Neuen Session-Key erzeugen - POST /session
if($session_key == "" && $api_key != ""){
	$ch = curl_init();
	$post_key = "key=".$api_key;
	$post_url = $base_url . "session";
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_key);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	$buffer = curl_exec($ch);
	$session_post = explode("\"session_key\":\"",$buffer);
	$session_post1 = explode("\",\"status\":",$session_post[1]);
	$session_key = $session_post1[0];
}

//Session-Key in Cookie speichern
setcookie("session_key", $session_key, time()+300);

if($deleg_unit != "" && $deleg_member != ""){
	if($deleg_member == "!!delete"){
		$trustee_id = "&delete=true";
	} else{
		$trustee_id = "&trustee_id=".urlencode($deleg_member);
	}
	$ch = curl_init();
	$post_key = "unit_id=".urlencode($deleg_unit).$trustee_id.'&session_key='.urlencode($session_key);
	$post_url = $base_url . "delegation";
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_key);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	$buffer = curl_exec($ch);
}

if($deleg_area != "" && $deleg_member != ""){
	if($deleg_member == "!!delete"){
		$trustee_id = "&delete=true";
	} else{
		$trustee_id = "&trustee_id=".urlencode($deleg_member);
	}
	$ch = curl_init();
	$post_key = "area_id=".urlencode($deleg_area).$trustee_id.'&session_key='.urlencode($session_key);
	$post_url = $base_url . "delegation";
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_key);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	$buffer = curl_exec($ch);
}

//User-Liste werden aus der API gezogen, JSON => array
$url_member = $base_url . "member?limit=1000&session_key=" . $session_key;
$string_member = file_get_contents($url_member);
$json_member = json_decode($string_member,true);
for ($i = 0; $i < count($json_member['result']); $i++) {
	$array_member[$i][0] = $json_member['result'][$i]['id'];
	$array_member[$i][1] = $json_member['result'][$i]['name'];
}

//Session-Infos werden aus der API gezogen
//ID des derzeitigen Nutzers: JSON => string
$url_info = $base_url . "info?session_key=" . $session_key;
$string_info = file_get_contents($url_info);
$json_info = json_decode($string_info,true);
$current_member_id = $json_info['current_member_id'];

//Alle Units werden aus der API gezogen, JSON => array
$url_unit = $base_url . "unit?session_key=" . $session_key;
$string_unit = file_get_contents($url_unit);
$json_unit = json_decode($string_unit,true);
for ($i = 0; $i < count($json_unit['result']); $i++) {
	$array_unit[$i][0] = $json_unit['result'][$i]['id'];
	$array_unit[$i][1] = $json_unit['result'][$i]['name'];
}

//Areas der Unit1 werden aus der API gezogen, JSON => array
$url_area = $base_url . "area?session_key=" . $session_key . "&unit_id=1";
$string_area = file_get_contents($url_area);
$json_area = json_decode($string_area,true);
for ($i = 0; $i < count($json_area['result']); $i++) {
	$array_area[$i][0] = $json_area['result'][$i]['id'];
	$array_area[$i][1] = "Bundesweite Themen / " . $json_area['result'][$i]['name'];
}

//Delegierte Units werden aus der API gezogen, JSON => array
$url_delegation_unit = $base_url . "delegation?scope=unit&direction=out&session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_delegation_unit = file_get_contents($url_delegation_unit);
$json_delegation_unit = json_decode($string_delegation_unit,true);
for ($i = 0; $i < count($json_delegation_unit['result']); $i++) {
	$array_delegation_unit[$i][0] = $json_delegation_unit['result'][$i]['unit_id'];
	$array_delegation_unit[$i][1] = $json_delegation_unit['result'][$i]['trustee_id'];
}

//Delegierte Areas aus Unit1 werden aus der API gezogen, JSON => array
$url_delegation_area = $base_url . "delegation?scope=area&direction=out&unit_id=1&session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_delegation_area = file_get_contents($url_delegation_area);
$json_delegation_area = json_decode($string_delegation_area,true);
for ($i = 0; $i < count($json_delegation_area['result']); $i++) {
	$array_delegation_area[$i][0] = $json_delegation_area['result'][$i]['area_id'];
	$array_delegation_area[$i][1] = $json_delegation_area['result'][$i]['trustee_id'];
}

//Custom Sortierfunktion 2
function cmp($a,$b){
    //get which string is less or 0 if both are the same
    $cmp = strcasecmp($a[1], $b[1]);
    //if the strings are the same, check name
    if($cmp == 0){
        //compare the name
        $cmp = strcasecmp($a[0], $b[0]);
    }
    return $cmp;
}

//Nutzer werden alphabetisch nach Namen sortiert
usort($array_member, 'cmp');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>LiquidFeedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Testing Interface powered by LiquidFeedback-APIs">
    <meta name="author" content="Bernhard Hayden">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
				background-color: #4c2582;
				background-image: url('https://github.com/zutrinken/Piraten-Theme/blob/master/images/logo.png?raw=true');
				background-attachment: fixed;
				background-repeat: no-repeat;
				background-position: 80% 5%;
        padding-top: 60px;
        padding-bottom: 40px;
      }
			footer {
				color: white;
			}
			#step2unit, #step2area , #step3 {
				display: none;
			}
    </style>
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Fav and touch icons
    <link rel="shortcut icon" href="ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">-->
  </head>

  <body>

    <div class="container">
      <div class="row">
        <div class="span8">
<div class="alert alert-error"><?echo $buffer;?></div>
					<?if($session_key == ""){echo "
<div class=\"well\">
	<h1>lqfb-delegation-interface</h1>
	<p>Hier kannst du deine Delegationen für die LiquidFeedback-Instanz der Piratenpartei Österreichs einfach und schnell erstellen, löschen und ändern. Dafür musst du dich mit deinem API-Schlüssel anmelden.</p>
	<p>
		<form class=\"form-inline\" action=\"index.php\" method=\"post\">
			<input name=\"api_key\" class=\"input\" type=\"text\" placeholder=\"API-Schlüssel eingeben\">
			<button type=\"submit\" class=\"btn\">Anmelden</button>
		</form>
	</p>
</div>
";
} else {echo "<div class=\"well\"><h2>Delegationen</h2><p>Hier siehst du deine bisherigen Delegationen:</p><table class=\"table table-hover\"><thead><tr><th>Delegierter Bereich</th><th>Delegiert an</th></tr></thead><tbody>";
//Unit-Delegationen aus array in anderes array
for ($i = 0; $i < count($array_delegation_unit); $i++) {
	for ($e = 0; $e < count($array_unit); $e++) {
		if($array_delegation_unit[$i][0] == $array_unit[$e][0]){
			$delegation_output_unit[$i][] = $array_unit[$e][1];
		}
	}
	for ($o = 0; $o < count($array_member); $o++) {
		if($array_delegation_unit[$i][1] == $array_member[$o][0]){
			$delegation_output_unit[$i][] = $array_member[$o][1];
		}
	}
}

//Area-Delegationen aus array in anderes array
for ($i = 0; $i < count($array_delegation_area); $i++) {
	for ($e = 0; $e < count($array_area); $e++) {
		if($array_delegation_area[$i][0] == $array_area[$e][0]){
			$delegation_output_area[$i][] = $array_area[$e][1];
		}
	}
	for ($o = 0; $o < count($array_member); $o++) {
		if($array_delegation_area[$i][1] == $array_member[$o][0]){
			$delegation_output_area[$i][] = $array_member[$o][1];
		}
	}
}

//Unit-Array in Tabelle ausgeben
for ($i = 0; $i < count($delegation_output_unit); $i++) {
	echo "<tr><td>" . $delegation_output_unit[$i][0] . "</td><td>" . $delegation_output_unit[$i][1] . "</td></tr>";
}

//Area-Array in Tabelle ausgeben
for ($i = 0; $i < count($delegation_output_area); $i++) {
	echo "<tr><td>" . $delegation_output_area[$i][0] . "</td><td>" . $delegation_output_area[$i][1] . "</td></tr>";
}

//"Fehlermeldung"
if($delegation_output_unit[0][0] == "" && $delegation_output_area[0][0] == ""){
	echo "<tr><td>Bisher keine Delegationen angelegt!</td><td></td></tr>";
}
echo "</tbody></table><p>Du kannst auch weitere Delegationen hinzufügen und deine bisherigen Delegationen ändern oder löschen:</p><p><a class=\"btn\" href=\"#change\" data-toggle=\"modal\">Delegationen ändern & hinzufügen & entfernen</a></p><div><a href=\"index.php?logout=true\">Abmelden</a></div></div><!--/.well -->";
}
?>


<!-- Modal -->
<div id="change" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Delegationen</h3>
	</div>
	<div class="modal-body">
		<div id="step1">
			<h4>Schritt 1.</h4>
			<p>Was willst du delegieren?</p>
			<form action="index.php" method="post">
				<select name="deleg" id="deleg" onchange="$(document.getElementById('deleg').value).show();$('#step1').hide();">
					<option value="">Wähle bitte aus:</option>
					<option value="#step2unit">Gesamte Gliederung (z.B. Bundesland) delegieren</option>
					<option value="#step2area">Einzelne Themenbereiche delegieren</option>
				</select>
		</div>
		<div id="step2unit">
			<h4>Schritt 2.</h4>
			<p>Welche Gliederung willst du delegieren?</p>
			<select name="deleg_unit" onchange="$('#step3').show();$('#step2unit').hide();">
				<option value="">Wähle eine Gliederung</option>
<?
//Alle Units auflisten
for ($i = 0; $i < count($array_unit); $i++) {
	echo "<option value=\"" . $array_unit[$i][0] . "\">" . $array_unit[$i][1] . "</option>";
}
?>
			</select>
		</div>
		<div id="step2area">
			<h4>Schritt 2.</h4>
			<p>Welchen Themenbereich willst du delegieren?</p>
			<select name="deleg_area" onchange="$('#step3').show();$('#step2area').hide();">
				<option value="">Wähle einen Themenbereich</option>
<?
//Alle Units auflisten
for ($i = 0; $i < count($array_area); $i++) {
	echo "<option value=\"" . $array_area[$i][0] . "\">" . $array_area[$i][1] . "</option>";
}
?>
			</select>
		</div>
		<div id="step3">
			<h4>Schritt 3.</h4>
			<p>An wen willst du diese Gliederung delegieren?</p>
			<select name="deleg_member">
				<option value="">Wähle einen Nutzer</option>
				<option value="!!delete">--Delegation aufheben--</option>
<?
//Alle Nutzer auflisten
for ($i = 0; $i < count($array_member); $i++) {
	if($array_member[$i][1] != ""){
		echo "<option value=\"" . $array_member[$i][0] . "\">" . $array_member[$i][1] . "</option>";
	}
}
?>
			</select>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true" onclick="$('#step1').show();$('#step2unit').hide();$('#step2area').hide();$('#step3').hide();">Abbrechen</button>
		<button class="btn btn-primary" type="submit">Änderungen speichern</button>
		</form>
	</div>
</div>
        </div><!--/span-->
      </div><!--/row-->

      <footer>
        <p>Eine kleine Spielerei von Bernhard <a href="http://wiki.piratenpartei.at/wiki/Benutzer:Burnoutberni">'burnoutberni'</a> Hayden.</p>
				<p>Datenquelle: <a href="http://lqfb.piratenpartei.at">http://lqfb.piratenpartei.at</a></p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap-transition.js"></script>
    <script src="js/bootstrap-alert.js"></script>
    <script src="js/bootstrap-modal.js"></script>
    <script src="js/bootstrap-dropdown.js"></script>
    <script src="js/bootstrap-scrollspy.js"></script>
    <script src="js/bootstrap-tab.js"></script>
    <script src="js/bootstrap-tooltip.js"></script>
    <script src="js/bootstrap-popover.js"></script>
    <script src="js/bootstrap-button.js"></script>
    <script src="js/bootstrap-collapse.js"></script>
    <script src="js/bootstrap-carousel.js"></script>
    <script src="js/bootstrap-typeahead.js"></script>
		<script>  
			$(function ()  
				{ $("#my_ini").popover();
			});  
		</script>
  </body>
</html>

