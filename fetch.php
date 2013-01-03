<?
//URL des API-Servers
$base_url = "https://lfapi.piratenpartei.at/";

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

//Name des derzeitigen Nutzers wird gesucht (und hoffentlich gefunden ;) )
for ($i = 0; $i < count($array_member); $i++) {
	if($array_member[$i][0] == $current_member_id){
		$current_member_name = $array_member[$i][1];
	}
}

//Bild des derzeitigen Nutzers
$url_image = $base_url . "member_image?session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_image = file_get_contents($url_image);
$json_image = json_decode($string_image,true);
$current_member_image = $json_image['result'][0]['data'];

//Mitgliedschaften in den Units werden aus der API gezogen, JSON => array
$url_privilege = $base_url . "privilege?session_key=" . $session_key . "&member_id=" . $current_member_id . "&voting_right=true";
$string_privilege = file_get_contents($url_privilege);
$json_privilege = json_decode($string_privilege,true);
for ($i = 0; $i < count($json_privilege['result']); $i++) {
	$array_privilege[$i] = $json_privilege['result'][$i]['unit_id'];
}
$string_privilege_result = implode(",", $array_privilege);

//Alle Units werden aus der API gezogen, JSON => array
$url_unit = $base_url . "unit?session_key=" . $session_key;
$string_unit = file_get_contents($url_unit);
$json_unit = json_decode($string_unit,true);
for ($i = 0; $i < count($json_unit['result']); $i++) {
	$array_unit[$i][0] = $json_unit['result'][$i]['id'];
	$array_unit[$i][1] = $json_unit['result'][$i]['name'];
}

//Areas der Units mit Voting-Privilege werden aus der API gezogen, JSON => array
$url_area = $base_url . "area?session_key=" . $session_key . "&unit_id=" . $string_privilege_result;
$string_area = file_get_contents($url_area);
$json_area = json_decode($string_area,true);
for ($i = 0; $i < count($json_area['result']); $i++) {
	for ($e = 0; $e < count($array_unit); $e++) {
		if($json_area['result'][$i]['unit_id'] == $array_unit[$e][0]) {
			$array_area[$i][0] = $json_area['result'][$i]['id'];
			$array_area[$i][1] = $array_unit[$e][1] . " / " . $json_area['result'][$i]['name'];
		}
	}
}

//Delegierte Units werden aus der API gezogen, JSON => array
$url_delegation_unit = $base_url . "delegation?scope=unit&direction=out&session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_delegation_unit = file_get_contents($url_delegation_unit);
$json_delegation_unit = json_decode($string_delegation_unit,true);
for ($i = 0; $i < count($json_delegation_unit['result']); $i++) {
	$array_delegation_unit[$i][0] = $json_delegation_unit['result'][$i]['unit_id'];
	$array_delegation_unit[$i][1] = $json_delegation_unit['result'][$i]['trustee_id'];
}

//Delegierte Areas aus den Units mit Voting-Privilege werden aus der API gezogen, JSON => array
$url_delegation_area = $base_url . "delegation?scope=area&direction=out&unit_id=" . $string_privilege_result . "&session_key=" . $session_key . "&member_id=" . $current_member_id;
$string_delegation_area = file_get_contents($url_delegation_area);
$json_delegation_area = json_decode($string_delegation_area,true);
for ($i = 0; $i < count($json_delegation_area['result']); $i++) {
	$array_delegation_area[$i][0] = $json_delegation_area['result'][$i]['area_id'];
	$array_delegation_area[$i][1] = $json_delegation_area['result'][$i]['trustee_id'];
}
?>
