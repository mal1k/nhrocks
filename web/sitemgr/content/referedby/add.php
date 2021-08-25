<?php
require('db.php');
include ('header.php');
$status = "";
if(isset($_POST['new']) && $_POST['new']==1){
    $trn_date = date("Y-m-d H:i:s");
    $name =$_REQUEST['name'];
    $submittedby = $_SESSION["username"];
    $organization = $_REQUEST['organization'];
    $link = $_REQUEST['link'];
    $ins_query="insert into ReferedBy (`date`,`name`,`submittedby`,`organization`,`link`)values
    ('$trn_date','$name','$submittedby', '$organization', '$link')";
    mysqli_query($con,$ins_query)
    or die(mysql_error());
    $status = "New Record Inserted Successfully.
    </br></br><a href='view.php'>View Inserted Record</a>";
}
?>
<?php include ('sidebar.php'); ?>
    <!DOCTYPE html>
    <html>

        <main class="wrapper togglesidebar container-fluid" id="view-content-list">
        <div class="form">
            <div class="control-bar">
                        <a class="btn btn-sm btn-primary" id="add-categories"   href="index.php" tabindex="44"><i class="icon-cross8"></i> Show Records</a>
                    </div>
            <div>
                <h1>Insert New Refered</h1>
                <form name="form" method="post" action="">
                    <input type="hidden" name="new" value="1" />
                    <p>
                        <input class="form-control input-lg" type="text" name="name" placeholder="Enter Name" required />
                    </p>
                    <p>
                        <input class="form-control input-lg" type="text" name="organization" placeholder="Enter Organization" required />
                    </p>
                    <p>
                        <input class="form-control input-lg" type="text" name="link" placeholder="Enter Link" required />
                    </p>
                    <p>
                        <input class="btn btn-sm btn-primary" name="submit" type="submit" value="Save" />
                    </p>
                </form>
                <p style="color:#FF0000;">
                    <?php echo $status; ?>
                </p>
            </div>
        </div>
    
        </main>    