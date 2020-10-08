<?php


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);

    // SAVE to AirTable
    $data = [
        "fields" => [
            'FirstName' => $input['first_name'] ?? '',
            'LastName' => $input['last_name'] ?? '',
            'Email' => $input['email'] ?? '',
        ]
    ];

    $data_json = json_encode($data);

    $ch = curl_init('https://api.airtable.com/v0/appL7BljlwkiI6zKH/New%20Visitor%20Signups?api_key=keyliqKng8eLukP2r');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $result = curl_exec($ch);
    curl_close($ch);

    echo \json_encode(["success" => true, 'data' => $input]);
    return;
}

http_response_code(400);
return \json_encode(["success" => false]);
