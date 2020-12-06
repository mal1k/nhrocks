<?php

include("../conf/loadconfig.inc.php");

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["email"])) {
    $data = updateByEmail($_GET["email"]);

    if(empty($data)){
        echo \json_encode(["success" => false, 'data' => $data]);
        return;
    }

    echo \json_encode(["success" => true, 'data' => $data]);
    return;
}

/**
 * @param string $table
 * @param string $email
 *
 * @return null|string
 */
function findByEmail($table, $email)
{
    setting_get('airtable_base_url', $airtable_base_url);
    setting_get('airtable_key', $airtable_key);

    if(empty($airtable_base_url) || empty($airtable_key)){
        return false;
    }

    $airtable_base_url = rtrim($airtable_base_url, '/') . '/';

    $ch = curl_init($airtable_base_url . $table . "?filterByFormula=%7BEmail%7D%3D'" . $email . "'");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $airtable_key
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

/**
 * @param string $email
 */
function updateByEmail($email)
{
    setting_get('update_visitor_url', $update_visitor_url);
    setting_get('update_sponsor_url', $update_sponsor_url);

    if(empty($update_visitor_url) || empty($update_sponsor_url)){
        return [];
    }

    $tables = [
        'New%20Visitor%20Signups' => $update_visitor_url,
        'New%20Sponsor%20Signups' => $update_sponsor_url,
    ];
    $inTable = '';

    foreach (array_keys($tables) as $table) {
        $rowId = findByEmail($table, $email);
        if ($rowId) {
            $inTable = $table;
            break;
        }
    }

    if ($rowId) {
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

        $ch = curl_init($tables[$inTable]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_exec($ch);
        curl_close($ch);

        $data['zapier'] = $tables[$inTable];

        return $data;
    }
}
