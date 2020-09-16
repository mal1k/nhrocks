<?php

include("../conf/loadconfig.inc.php");

/**
 * @param string $table
 * @param string $email
 *
 * @return null|string
 */
function findByEmail($table, $email)
{
    $ch = curl_init("https://api.airtable.com/v0/appL7BljlwkiI6zKH/" . $table . "?filterByFormula=%7BEmail%7D%3D'" . $email . "'");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer keyliqKng8eLukP2r'
    ));

    $result = curl_exec($ch);
    curl_close($ch);

    try {
        $res = json_decode($result, true);
        return $res['records'][0]['id'] ?? null;
    } catch (Exception $e) {
        return false;
    }
}

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
            $tables = ['New%20Visitor%20Signups', 'New%20Sponsor%20Signups'];
            $inTable = '';

            foreach ($tables as $table) {
                $rowId = findByEmail($table, $email);
                if($rowId){
                    $inTable = $table;
                    break;
                }
            }

            if($rowId){
                $data = [
                    'records' =>
                        [
                            [
                                'id' => $rowId,
                                'fields' =>
                                    [
                                        'Local' => 'True',
                                    ],
                            ],
                        ],
                ];

                $ch = curl_init("https://api.airtable.com/v0/appL7BljlwkiI6zKH/" . $inTable);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: Bearer keyliqKng8eLukP2r'
                ));

                $result = curl_exec($ch);
                curl_close($ch);
            }
        }


    } catch (\Throwable $t) {
        var_dump($t);
        return;
    }

    header("Location: /profile/");
    return;
}

header("Location: /profile/");
