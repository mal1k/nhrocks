<?php
require('db.php');
include ('header.php');
$id=$_REQUEST['id'];
$query = "SELECT * from ReferedBy where id='".$id."'"; 
$result = mysqli_query($con, $query) or die ( mysqli_error());
$row = mysqli_fetch_assoc($result);
?>
<?php include ('sidebar.php'); ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <title>Update Record</title>
    </head>

    <body>
        <div class="form">
            | <a href="insert.php">Insert New Record</a>
            <h1>Update Record</h1>
            <?php
$status = "";
if(isset($_POST['new']) && $_POST['new']==1)
{
$id=$_REQUEST['id'];
$trn_date = date("Y-m-d H:i:s");
$name =$_REQUEST['name'];
$submittedby = $_SESSION["username"];
$organization = $_REQUEST['organization'];
$link = $_REQUEST['link'];
$update="update ReferedBy set date='".$trn_date."',
name='".$name."', submittedby='".$submittedby."', organization='".$organization."', link='".$link."'
where id='".$id."'";
mysqli_query($con, $update) or die(mysqli_error());
$status = "Record Updated Successfully. </br></br>
<a href='view.php'>View Updated Record</a>";
echo '<p style="color:#FF0000;">'.$status.'</p>';
}else {
?>
                <div>
                    <form name="form" method="post" action="">
                        <input type="hidden" name="new" value="1" />
                        <input name="id" type="hidden" value="<?php echo $row['id'];?>" />
                        <p>
                            <input type="text" name="name" placeholder="Enter Name" required value="<?php echo $row['name'];?>" />
                        </p>
                        <p>
                            <input type="text" name="organization" placeholder="Enter Organization" required value="<?php echo $row['organization'];?>" />
                        </p>
                        <p>
                            <input type="text" name="link" placeholder="Enter Link" required value="<?php echo $row['link'];?>" />
                        </p>
                        <p>
                            <input name="submit" type="submit" value="Update" />
                        </p>
                    </form>
                    <?php } ?>
                </div>
        </div>
    </body>

    </html>
