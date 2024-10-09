<?php
require dirname(dirname(__FILE__)) . '/include/reconfig.php';
require dirname(dirname(__FILE__)) . '/include/estate.php';
header('Content-type: text/json');
$data = json_decode(file_get_contents('php://input'), true);

if ($data['uid'] == '' or $data['plan_id'] == '' or $data['transaction_id'] == '' or $data['pname'] == '') {
    $returnArr = array(
        "ResponseCode" => "401",
        "Result" => "false",
        "ResponseMsg" => "Something Went Wrong!"
    );
} else {
	$returnArr = array(
        "ResponseCode" => "200",
        "Result" => "true",
        "ResponseMsg" => "It works!"
    );
}
echo json_encode($returnArr);
?>