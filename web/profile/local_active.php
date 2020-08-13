<?php
header('Content-type: application/json');

include("../conf/loadconfig.inc.php");

try {
    if (isset($_GET["account_id"])) {

        $localsCardHolderObj = new LocalsCardHolder($_GET["account_id"]);

        $isLocalCardHolder = $localsCardHolderObj->getNumber('account_id') > 0;
        $isLocalCardActive = $isLocalCardHolder && (int) $localsCardHolderObj->getNumber('active') === 1;

        $output = [
            'success' => true,
            'active' => $isLocalCardActive
        ];

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => true,
            'active' => false
        ]);
    }
} catch (\Throwable $t) {
    var_dump($t);
    return;
}

