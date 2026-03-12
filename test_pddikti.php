<?php
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Origin: https://pddikti.kemdiktisaintek.go.id\r\n"
    ],
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ]
];
$context = stream_context_create($opts);
$response = file_get_contents('https://api-pddikti.kemdiktisaintek.go.id/pencarian/mhs/1908107010022', false, $context);
file_put_contents('pddikti_test_res.json', json_encode(json_decode($response)->mahasiswa[0] ?? json_decode($response)[0], JSON_PRETTY_PRINT));
