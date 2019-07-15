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

function isCorrectColor($color) {
    return preg_match('/^[0-9a-f]{6}$/', $color) == 1;
}

function statusText2Color($statusText) {
    switch (strtolower($statusText)) {
        case 'welcome':
            return WELCOME_COLOR;
            break;

        case 'busy': 
            return BUSY_COLOR;
            break;

        case 'gtfo':
            return GTFO_COLOR;
            break;

        default:
            return false;
    }
}

function getRoomConfig($room) {
    global $roomsLedConfig;

    if (empty($roomsLedConfig[$room])) {
        return false;
    } else {
        return $roomsLedConfig[$room];
    }
}