<?php

function mknonce($len = 64) {
    $SNChars = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    $SNCCount = strlen($SNChars);
    $s = '';
    while (strlen($s) < $len)
    {
        $s .= $SNChars[random_int(0, $SNCCount-1)];
    }
    return $s;
}

function displayStub() {
    header ("HTTP/1.1 404 Not Found");
    die ("<h1>WTF are you stalking here m8?</h1>");
}

function json_respond ($status, $payload = Array()) {
    if (CORS_ALLOW_EXTERNAL)
        header ('Access-Control-Allow-Origin: *');

    header('Content-Type: application/json');
    die(json_encode(Array('status' => (int)$status, 'payload' => $payload)));
}
