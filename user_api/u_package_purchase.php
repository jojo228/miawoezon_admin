<?php
require dirname(dirname(__FILE__)) . '/include/reconfig.php';
require dirname(dirname(__FILE__)) . '/include/estate.php';
header('Content-Type: application/json'); // Correct MIME type for JSON

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate that all required fields are present
if (
    !isset($data['uid']) ||
    !isset($data['plan_id']) ||
    !isset($data['transaction_id']) ||
    !isset($data['pname'])
) {
    $returnArr = array(
        "ResponseCode" => "400",
        "Result" => "false",
        "ResponseMsg" => "Missing required fields!"
    );
} else {
    // Extract and sanitize input data
    $uid = intval($data['uid']);
    $plan_id = intval($data['plan_id']);
    $transaction_id = $data['transaction_id']; // Can be 0 or a valid transaction ID
    $pname = htmlspecialchars($data['pname']); // Prevent XSS

    // Additional validation
    if ($uid <= 0 || $plan_id <= 0) {
        $returnArr = array(
            "ResponseCode" => "400",
            "Result" => "false",
            "ResponseMsg" => "Invalid user ID or plan ID."
        );
    } else {
        // Fetch the plan details from the database
        $fetch = $rstate->query("SELECT * FROM tbl_package WHERE id = $plan_id")->fetch_assoc();

        if (!$fetch) {
            $returnArr = array(
                "ResponseCode" => "400",
                "Result" => "false",
                "ResponseMsg" => "Invalid plan selected."
            );
        } else {
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
            $h = new Estate();
            $check = $h->restateinsertdata_Api($field_values, $data_values, $table);

            // Update user subscription
            $table = "tbl_user";
            $field = array(
                'start_date' => $current_date,
                'end_date' => $till_date,
                'pack_id' => $plan_id,
                'is_subscribe' => '1'
            );
            $where = "WHERE id = $uid";
            $h = new Estate();
            $check = $h->restateupdateData_Api($field, $table, $where);

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
            $h = new Estate();
            $check = $h->restateinsertdata_Api($field_values, $data_values, $table);

            // Update property status
            $table = "tbl_property";
            $field = "status=1";
            $where = "WHERE add_user_id = $uid";
            $h = new Estate();
            $check = $h->restateupdateData_single($field, $table, $where);

            // Success response
            $returnArr = array(
                "ResponseCode" => "200",
                "Result" => "true",
                "ResponseMsg" => "Package Purchase Successfully!"
            );
        }
    }
}

echo json_encode($returnArr);
?>
