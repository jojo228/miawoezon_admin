<?php 
require 'reconfig.php';
$GLOBALS['rstate'] = $rstate;
class Estate {
 

	function restatelogin($username,$password,$tblname) {
		if($tblname == 'admin')
		{
		$q = "select * from ".$tblname." where username='".$username."' and password='".$password."'";
	return $GLOBALS['rstate']->query($q)->num_rows;
		}
		else if($tblname == 'restate_details')
		{
			$q = "select * from ".$tblname." where email='".$username."' and password='".$password."'";
	return $GLOBALS['rstate']->query($q)->num_rows;
		}
		else 
		{
			$q = "select * from ".$tblname." where email='".$username."' and password='".$password."' and status=1";
	return $GLOBALS['rstate']->query($q)->num_rows;
		}
	}
	
	function restateinsertdata($field,$data,$table){

    $field_values= implode(',',$field);
    $data_values=implode("','",$data);

    $sql = "INSERT INTO $table($field_values)VALUES('$data_values')";
    $result=$GLOBALS['rstate']->query($sql);
  return $result;
  }
  
  

  
 
  
  function restateinsertdata_id($field,$data,$table){

    $field_values= implode(',',$field);
    $data_values=implode("','",$data);

    $sql = "INSERT INTO $table($field_values)VALUES('$data_values')";
    $result=$GLOBALS['rstate']->query($sql);
  return $GLOBALS['rstate']->insert_id;
  }
  
  function restateinsertdata_Api($field,$data,$table){

    $field_values= implode(',',$field);
    $data_values=implode("','",$data);

    $sql = "INSERT INTO $table($field_values)VALUES('$data_values')";
    $result=$GLOBALS['rstate']->query($sql);
  return $result;
  }
  
  function restateinsertdata_Api_Id($field,$data,$table){

    $field_values= implode(',',$field);
    $data_values=implode("','",$data);

    $sql = "INSERT INTO $table($field_values)VALUES('$data_values')";
    $result=$GLOBALS['rstate']->query($sql);
  return $GLOBALS['rstate']->insert_id;
  }
  
  function restateupdateData($field,$table,$where){
$cols = array();

    foreach($field as $key=>$val) {
        if($val != NULL) // check if value is not null then only add that colunm to array
        {
			
           $cols[] = "$key = '$val'"; 
			
        }
    }
    $sql = "UPDATE $table SET " . implode(', ', $cols) . " $where";
$result=$GLOBALS['rstate']->query($sql);
    return $result;
  }
  
  
  
  function restateupdateData_Api($field, $table, $where, $params) {
    // Construct the SQL query to update multiple fields
    $cols = array();
    
    // Iterate through the fields and prepare the update query
    foreach($field as $key => $val) {
        if ($val !== NULL) { // Ensure the value is not null
            $cols[] = "$key = ?";
        }
    }
    
    // Create the full SQL query
    $sql = "UPDATE $table SET " . implode(', ', $cols) . " $where";
    
    // Prepare the SQL statement
    $stmt = $GLOBALS['rstate']->prepare($sql);
    
    // Check if the statement preparation was successful
    if (!$stmt) {
        return false; // Return false if preparation fails
    }
    
    // Determine parameter types (s = string, i = integer, etc.)
    $types = "";
    $values = array();
    
    foreach ($field as $val) {
        if (is_int($val)) {
            $types .= "i"; // Integer
        } elseif (is_float($val)) {
            $types .= "d"; // Double/float
        } else {
            $types .= "s"; // String
        }
        $values[] = $val;
    }
    
    // Merge params from where clause
    $types .= str_repeat('i', count($params)); // Assuming `params` are integers
    $values = array_merge($values, $params);
    
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$values);
    
    // Execute the SQL query
    $result = $stmt->execute();
    
    // Close the statement
    $stmt->close();
    
    // Return the result of the query
    return $result;
}

  
  
  
  
  function restateupdateDatanull_Api($field,$table,$where){
$cols = array();

    foreach($field as $key=>$val) {
        if($val != NULL) // check if value is not null then only add that colunm to array
        {
           $cols[] = "$key = '$val'"; 
        }
		else 
		{
			$cols[] = "$key = NULL"; 
		}
    }
	
 $sql = "UPDATE $table SET " . implode(', ', $cols) . " $where";
$result=$GLOBALS['rstate']->query($sql);
    return $result;
  }
  
  
  
  function restateupdateData_single($field,$table,$where){
$query = "UPDATE $table SET $field";

$sql =  $query.' '.$where;
$result=$GLOBALS['rstate']->query($sql);
  return $result;
  }
  
  function restaterestateDeleteData($where,$table){

    $sql = "Delete From $table $where";
    $result=$GLOBALS['rstate']->query($sql);
  return $result;
  }
  
  function restateDeleteData_Api($where,$table){

    $sql = "Delete From $table $where";
    $result=$GLOBALS['rstate']->query($sql);
  return $result;
  }
 
}
?>