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
	$json_buffer = json_decode($buffer,true);
	$session_key = $json_buffer['session_key'];
}

//Session-Key in Cookie speichern
setcookie("session_key", $session_key, time()+300);

//POST-Request für Unit-Delegation
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

//POST-Request für Area-Delegation
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

include 'fetch.php';

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

//Nutzer und Areas werden alphabetisch nach Namen sortiert
usort($array_member, 'cmp');
usort($array_area, 'cmp');

//Fehlermeldungen
$json_buffer = json_decode($buffer,true);
$error = $json_buffer['error'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>lqfb-delegation-interface</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Einfaches Interface, um Delegationen der LiquidFeedback-Instanz der Piratenpartei Österreichs zu bearbeiten.">
    <meta name="author" content="Bernhard 'burnoutberni' Hayden">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
				background-color: #4c2582;
        padding-top: 60px;
        padding-bottom: 40px;
      }
			footer {
				color: white;
			}
    </style>
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">

    <!-- Touch icons
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">-->
  </head>

  <body>

    <div class="container">
      <div class="row">
        <div class="span8">
<?
if($error != "") {
	echo "<div class=\"alert alert-error\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>".$error."</div>";
}
if($session_key == ""){echo "
<div class=\"well\">
	<h1>lqfb-delegation-interface</h1>
	<p>
		Die Piratenpartei Österreichs verwendet <a href=\"https://lqfb.piratenpartei.at\">LiquidFeedback</a>, um ihr Programm zu erweitern und zu verändern sowie um Partei-interne Regelwerke zu überarbeiten. Eine der Funktionen von LiquidFeedback ist die Möglichkeit der Delegation. Der Nutzer kann jederzeit sein Stimmgewicht für einzelne Themen, Themenbereiche oder Gliederungen auf eine andere Person übertragen. Diese Delegationen können wiederum jederzeit verändert oder zurückgezogen werden.
	</p>
	<p>Hier kannst du deine Delegationen für die LiquidFeedback-Instanz der Piratenpartei Österreichs einfach und schnell erstellen, löschen und ändern. Dafür musst du dich mit deinem API-Schlüssel anmelden.</p>
	<p>
		<form class=\"form-inline\" action=\"index.php\" method=\"post\">
			<input name=\"api_key\" class=\"input\" type=\"text\" placeholder=\"API-Schlüssel eingeben\">
			<button type=\"submit\" class=\"btn\">Anmelden</button>
		</form>
	</p>
</div>
";
} else {echo "
<div class=\"well\">
	<h1>lqfb-delegation-interface</h1>
	<p>
		Hier kannst du deine Delegationen ansehen, bearbeiten und löschen sowie neue Delegationen hinzufügen:
	</p>
	<a class=\"btn btn-success btn-large\" href=\"#add\" onclick=\"$('#step1').show();$('#step2unit').hide();$('#step2area').hide();$('#step3').hide();$('#select_deleg').attr('selected',true);$('#select_deleg_unit').attr('selected',true);$('#select_deleg_area').attr('selected',true);$('#select_deleg_member').attr('selected',true);\" data-toggle=\"modal\">Delegation hinzufügen</a>
</div>
<div class=\"well\" id=\"delegation_show\">
	<table class=\"table table-hover\">
		<thead>
			<tr>
				<th>Delegierter Bereich</th>
				<th colspan=\"2\">Delegiert an</th>
			</tr>
		</thead>
		<tbody>";
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
			$delegation_output_unit[$i][] = $array_delegation_unit[$i][0];
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
			$delegation_output_area[$i][] = $array_delegation_area[$i][0];
		}
	}
}

//Unit-Array in Tabelle ausgeben
for ($i = 0; $i < count($delegation_output_unit); $i++) {
	echo "<tr><td>" . $delegation_output_unit[$i][0] . "</td><td>" . $delegation_output_unit[$i][1] . "</td><td><a onclick=\"$('#change_unit').text('" . $delegation_output_unit[$i][0] . "');$('#change_user').text('" . $delegation_output_unit[$i][1] . "');$('#change_deleg_unit').attr('value','" . $delegation_output_unit[$i][2] . "');$('#change_deleg_area').attr('value','');\" href=\"#change\" data-toggle=\"modal\"><i class=\"icon-wrench\"></i></a></td><td><a onclick=\"$('#delete_unit').text('" . $delegation_output_unit[$i][0] . "');$('#delete_user').text('" . $delegation_output_unit[$i][1] . "');$('#delete_deleg_unit').attr('value','" . $delegation_output_unit[$i][2] . "');$('#delete_deleg_area').attr('value','');\" href=\"#delete\" data-toggle=\"modal\"><i class=\"icon-trash\"></i></a></td></tr>";
}

//Area-Array in Tabelle ausgeben
for ($i = 0; $i < count($delegation_output_area); $i++) {
	echo "<tr><td>" . $delegation_output_area[$i][0] . "</td><td>" . $delegation_output_area[$i][1] . "</td><td><a onclick=\"$('#change_unit').text('" . $delegation_output_area[$i][0] . "');$('#change_user').text('" . $delegation_output_area[$i][1] . "');$('#change_deleg_area').attr('value','" . $delegation_output_area[$i][2] . "');$('#change_deleg_unit').attr('value','');\" href=\"#change\" data-toggle=\"modal\"><i class=\"icon-wrench\"></i></a></td><td><a onclick=\"$('#delete_unit').text('" . $delegation_output_area[$i][0] . "');$('#delete_user').text('" . $delegation_output_area[$i][1] . "');$('#delete_deleg_area').attr('value','" . $delegation_output_area[$i][2] . "');$('#delete_deleg_unit').attr('value','');\" href=\"#delete\" data-toggle=\"modal\"><i class=\"icon-trash\"></i></a></td></tr>";
}

//"Fehlermeldung"
if($delegation_output_unit[0][0] == "" && $delegation_output_area[0][0] == ""){
	echo "<tr><td>Bisher keine Delegationen angelegt!</td><td></td><td></td><td></td></tr>";
}
echo "</tbody></table></div><!--/.well -->";
}
?>

<!-- Modal change -->
<div id="change" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ModalChange" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="ModalChange">Delegation ändern</h3>
	</div>
	<div class="modal-body">
		<p>Du delegierst derzeit den Bereich "<span id="change_unit"></span>" an den Nutzer "<span id="change_user"></span>". Auf wen willst du in Zukunft delegieren?</p>
		<form action="index.php" method="post">
		<input type="hidden" name="deleg_unit" id="change_deleg_unit" value="">
		<input type="hidden" name="deleg_area" id="change_deleg_area" value="">
		<select name="deleg_member" id="deleg_member">
			<option value="" id="select_deleg_member">Wähle einen Nutzer</option>
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
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Abbrechen</button>
		<button class="btn btn-primary" type="submit">Speichern</button>
		</form>
	</div>
</div>

<!-- Modal delete -->
<div id="delete" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ModalDelete" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="ModalDelete">Delegation löschen</h3>
	</div>
	<div class="modal-body">
		<p>Bist du dir sicher, dass du die Delegation für den Bereich "<span id="delete_unit"></span>" an den Nutzer "<span id="delete_user"></span>" löschen willst?</p>
		<form action="index.php" method="post">
		<input type="hidden" name="deleg_unit" id="delete_deleg_unit" value="">
		<input type="hidden" name="deleg_area" id="delete_deleg_area" value="">
		<input type="hidden" name="deleg_member" value="!!delete">
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Nein, abbrechen</button>
		<button class="btn btn-primary" type="submit">Ja, löschen</button>
		</form>
	</div>
</div>

<!-- Modal add -->
<div id="add" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ModalAdd" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="ModalAdd">Delegation hinzufügen</h3>
	</div>
	<div class="modal-body">
		<div id="step1">
			<h4>Schritt 1.</h4>
			<p>Was willst du delegieren?</p>
			<form action="index.php" method="post">
				<select name="deleg" id="deleg" onchange="$(document.getElementById('deleg').value).show();$('#step1').hide();">
					<option value="" id="select_deleg">Wähle bitte aus:</option>
					<option value="#step2unit">Gesamte Gliederung (z.B. Bundesland) delegieren</option>
					<option value="#step2area">Einzelne Themenbereiche delegieren</option>
				</select>
		</div>
		<div id="step2unit">
			<h4>Schritt 2.</h4>
			<p>Welche Gliederung willst du delegieren?</p>
			<select name="deleg_unit" id="deleg_unit" onchange="$('#step3').show();$('#step2unit').hide();">
				<option value="" id="select_deleg_unit">Wähle eine Gliederung</option>
<?
//Alle Units auflisten
for ($i = 0; $i < count($array_unit); $i++) {
	for ($e = 0; $e < count($array_privilege); $e++) {
		if($array_unit[$i][0] == $array_privilege[$e]) {
			echo "<option value=\"" . $array_unit[$i][0] . "\">" . $array_unit[$i][1] . "</option>";
		}
	}
}
?>
			</select>
		</div>
		<div id="step2area">
			<h4>Schritt 2.</h4>
			<p>Welchen Themenbereich willst du delegieren?</p>
			<select name="deleg_area" id="deleg_area" onchange="$('#step3').show();$('#step2area').hide();">
				<option value="" id="select_deleg_area">Wähle einen Themenbereich</option>
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
			<select name="deleg_member" id="deleg_member">
				<option value="" id="select_deleg_member">Wähle einen Nutzer</option>
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
		<button class="btn" data-dismiss="modal" aria-hidden="true">Abbrechen</button>
		<button class="btn btn-primary" type="submit">Änderungen speichern</button>
		</form>
	</div>
</div>
        </div><!--/span-->
				<? if($session_key != "") {
					include 'sidebar.php';
				}?>
      </div><!--/row-->

      <footer>
        <p>Eine kleine Spielerei von Bernhard <a href="http://wiki.piratenpartei.at/wiki/Benutzer:Burnoutberni">'burnoutberni'</a> Hayden.</p>
				<p>Datenquelle: <a href="https://lqfb.piratenpartei.at">https://lqfb.piratenpartei.at</a> &bull; <a href="https://lfapi.piratenpartei.at">https://lfapi.piratenpartei.at</a></p>
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

