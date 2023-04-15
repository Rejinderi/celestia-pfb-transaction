<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

if (isset($_POST['namespace_id'])) {
    header('Content-Type: application/json');

    $namespace_id = $_POST['namespace_id'] ?? '';
    $data = $_POST['data'] ?? '';
    $gas_limit = (int)$_POST['gas_limit'] ?? '';
    $fee = (int)$_POST['fee'] ?? '';

    if (empty($namespace_id) || empty($data) || empty($gas_limit) || empty($fee)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request. Please provide all required fields.']);
        exit;
    }

    $node_url = $_POST['node_url'] ?? '';
    if (empty($node_url)) {
        http_response_code(400);
        echo json_encode(['error' => 'Node URL is required.']);
        exit;
    }

    $client = new Client();
    $response = $client->request('POST', $node_url . '/submit_pfb', [
        'json' => [
            'namespace_id' => $namespace_id,
            'data' => $data,
            'gas_limit' => $gas_limit,
            'fee' => $fee
        ]
    ]);

    echo $response->getBody();
}