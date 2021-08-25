<?php
require('db.php');
include ('header.php');
    $id=$_REQUEST['id'];
    $query = "DELETE FROM ReferedBy WHERE id=$id"; 
    $result = mysqli_query($con,$query) or die ( mysqli_error());

exit("<meta http-equiv='refresh' content='0; url= index.php'>");

?>
<?php include ('sidebar.php'); ?>