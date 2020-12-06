<?php

include("../conf/loadconfig.inc.php");
include("./update_airtable.php");

if (isset($_GET["account_id"]) && isset($_GET["stripe_session_id"])) {

    try {
        $localsCardHolderObj = new LocalsCardHolder($_GET["account_id"]);

        if (!$localsCardHolderObj->account_id) {
            $localsCardHolderObj = new LocalsCardHolder([
                'account_id' => $_GET["account_id"],
                'session_id' => $_GET["stripe_session_id"],
                'entered' => gmdate("Y-m-d"),
                'active' => true,
            ]);
            $localsCardHolderObj->Save();
        } else {
            $localsCardHolderObj->active = true;
            $localsCardHolderObj->session_id = $_GET["stripe_session_id"];
            $localsCardHolderObj->entered = gmdate("Y-m-d");
            $localsCardHolderObj->Update();
        }

        $account = new Account((int)$_GET["account_id"]);

        if($account->id){
            $email = $account->username;
            $email = urlencode($email);
            updateByEmail($email);
        }


    } catch (\Throwable $t) {
        var_dump($t);
        return;
    }

    header("Location: /profile/");
    return;
}

header("Location: /profile/");
