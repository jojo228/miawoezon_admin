<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require dirname(dirname(__FILE__)) . '/include/reconfig.php';
require dirname(dirname(__FILE__)) . '/include/estate.php';

// Set Content-Type header
header('Content-Type: application/json');

// Initialize the return array
$returnArr = array();

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    $returnArr = array(
        "ResponseCode" => "400",
        "Result" => "false",
        "ResponseMsg" => "Invalid JSON format!"
    );
    echo json_encode($returnArr);
    exit();
}

// Validate that all required fields are present
$required_fields = ['uid', 'plan_id', 'transaction_id', 'pname'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    $returnArr = array(
        "ResponseCode" => "400",
        "Result" => "false",
        "ResponseMsg" => "Missing required fields: " . implode(', ', $missing_fields) . "!"
    );
    echo json_encode($returnArr);
    exit();
}

// Extract and sanitize input data
$uid = intval($data['uid']);
$plan_id = intval($data['plan_id']);
$transaction_id = $data['transaction_id']; // Can be 0 or a valid transaction ID
$pname = htmlspecialchars($data['pname'], ENT_QUOTES, 'UTF-8'); // Prevent XSS

// Additional validation
if ($uid <= 0 || $plan_id <= 0) {
    $returnArr = array(
        "ResponseCode" => "400",
        "Result" => "false",
        "ResponseMsg" => "Invalid user ID or plan ID."
    );
    echo json_encode($returnArr);
    exit();
}

// Initialize Estate class
$h = new Estate();

// Fetch the plan details from the database using prepared statements
$stmt = $GLOBALS['rstate']->prepare("SELECT * FROM tbl_package WHERE id = ?");
if (!$stmt) {
    $returnArr = array(
        "ResponseCode" => "500",
        "Result" => "false",
        "ResponseMsg" => "Database query preparation failed."
    );
    echo json_encode($returnArr);
    exit();
}

$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $returnArr = array(
        "ResponseCode" => "400",
        "Result" => "false",
        "ResponseMsg" => "Invalid plan selected."
    );
    echo json_encode($returnArr);
    exit();
}

$fetch = $result->fetch_assoc();
$stmt->close();

// Calculate dates
$datetime = date("Y-m-d H:i:s");
$current_date = date("Y-m-d");
$till_date = date("Y-m-d", strtotime("+ " . intval($fetch['day']) . " day"));

// Prepare notification
$title = "Package Purchase Successfully";
$description = "{$fetch['title']} Package Purchase From {$current_date} To {$till_date}. Payment Gateway Name: {$pname} Transaction Id: {$transaction_id}";

// Insert notification
$table = "tbl_notification";
$field_values = array("uid", "datetime", "title", "description");
$data_values = array($uid, $datetime, $title, $description);
$check = $h->restateinsertdata_Api($field_values, $data_values, $table);

if (!$check) {
    $returnArr = array(
        "ResponseCode" => "500",
        "Result" => "false",
        "ResponseMsg" => "Failed to insert notification."
    );
    echo json_encode($returnArr);
    exit();
}

// Update user subscription
$table = "tbl_user";
$field = array(
    'start_date' => $current_date,
    'end_date' => $till_date,
    'pack_id' => $plan_id,
    'is_subscribe' => '1'
);
$where = "id = ?";
$check = $h->restateupdateData_Api($field, $table, $where, array($uid));

if (!$check) {
    $returnArr = array(
        "ResponseCode" => "500",
        "Result" => "false",
        "ResponseMsg" => "Failed to update user subscription."
    );
    echo json_encode($returnArr);
    exit();
}

// Insert into purchase history
$table = "plan_purchase_history";
$field_values = array(
    "uid",
    "plan_id",
    "p_name",
    "t_date",
    "amount",
    "day",
    "plan_title",
    "plan_description",
    "expire_date",
    "start_date",
    "trans_id",
    "plan_image"
);
$data_values = array(
    $uid,
    $plan_id,
    $pname,
    $datetime,
    $fetch['price'],
    $fetch['day'],
    $fetch['title'],
    $fetch['description'],
    $till_date,
    $current_date,
    $transaction_id,
    $fetch['image']
);
$check = $h->restateinsertdata_Api($field_values, $data_values, $table);

if (!$check) {
    $returnArr = array(
        "ResponseCode" => "500",
        "Result" => "false",
        "ResponseMsg" => "Failed to insert purchase history."
    );
    echo json_encode($returnArr);
    exit();
}

// Update property status
$table = "tbl_property";
$field = "status = 1";
$where = "id = ?";
$check = $h->restateupdateData_single($field, $table, $where, array($uid));

if (!$check) {
    $returnArr = array(
        "ResponseCode" => "500",
        "Result" => "false",
        "ResponseMsg" => "Failed to update property status."
    );
    echo json_encode($returnArr);
    exit();
}

// Success response
$returnArr = array(
    "ResponseCode" => "200",
    "Result" => "true",
    "ResponseMsg" => "Package Purchase Successfully!"
);

echo json_encode($returnArr);
?>
