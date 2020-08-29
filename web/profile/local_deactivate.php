<?php

header('Content-type: application/json');

include("../conf/loadconfig.inc.php");

$dbObj = db_getDBObject(DEFAULT_DB, true);
$sql = "SELECT account_id as account_id FROM Locals_Card_Holders WHERE Locals_Card_Holders.active = 1 AND Locals_Card_Holders.entered < DATE_SUB(NOW(),INTERVAL 1 YEAR)";
$result = $dbObj->query($sql);
$expired = [];
$deactivated = false;

if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $expired[] = $row['account_id'];
    }

    if(!empty($expired)) {
        $sql = "UPDATE Locals_Card_Holders SET active = 0 WHERE Locals_Card_Holders.active = 1 AND Locals_Card_Holders.entered < DATE_SUB(NOW(),INTERVAL 1 YEAR)";
        $deactivated = $dbObj->query($sql);
    }
}

echo \json_encode(['expired' => $expired, 'deactivated' => $deactivated]);



