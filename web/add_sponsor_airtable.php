<?php

if ($_SERVER["REQUEST_METHOD"] == "PATCH") {
    $input = json_decode(file_get_contents('php://input'), true);

    // SAVE to AirTable
    $data = [
        "fields" => [
            'FirstName' => $input['first_name'] ?? '',
            'LastName' => $input['last_name'] ?? '',
            'Email' => $input['email'] ?? '',
            'Listing' => $input['title'] ?? '',
        ]
    ];

    setting_get('add_sponsor_url', $add_sponsor_url);

    if(empty($add_sponsor_url)){
        echo \json_encode(["success" => false, 'data' => ['error' => 'add_sponsor_url not found']]);
        return;
    }

    addSponsor($data, $add_sponsor_url);

    echo \json_encode(["success" => true, 'data' => $data]);
    return;
}

/**
 * @param array $data
 * @param string $add_sponsor_url
 */
function addSponsor(array $data, $add_sponsor_url): void
{
    $data_json = json_encode($data);

    $ch = curl_init($add_sponsor_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $result = curl_exec($ch);
    curl_close($ch);
}
