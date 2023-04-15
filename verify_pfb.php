<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

if (isset($_POST['namespace_id'])) {
    header('Content-Type: application/json');

    $namespace_id = $_POST['namespace_id'] ?? '';
    $height = $_POST['height'] ?? '';

    if (empty($namespace_id) || empty($height)) {
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
    $response = $client->request('GET', $node_url . '/namespaced_shares/' . $namespace_id . '/height/'. $height);

    echo $response->getBody();
}