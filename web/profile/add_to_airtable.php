<?php

include '../conf/loadconfig.inc.php';

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


    setting_get('add_visitor_url', $add_visitor_url);

    if(empty($add_visitor_url)){
        echo \json_encode(["success" => false, 'data' => ['error' => 'add_visitor_url not found']]);
        return;
    }

    addVisitor($data, $add_visitor_url);

    echo \json_encode(["success" => true, 'data' => $input]);
    return;
}

/**
 * @param array $data
 * @param string $url
 */
function addVisitor($data, $url)
{
    $data_json = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $result = curl_exec($ch);
    curl_close($ch);
}

http_response_code(400);
return \json_encode(["success" => false]);