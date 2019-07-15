<?php

require_once 'engine/leds.php';

function api_processStatus() {
    global $roomsLedConfig;

    $room = strtolower($_GET['room']);
    $status = strtolower($_GET['status']);

    if (empty(getRoomConfig($room))) {
        json_respond(1, 'Bad room');
    }

    $statusColor = statusText2Color($status);

    if (!$statusColor) {
        json_respond(2 , 'Bad color');
    }

    if (leds_writeHex($room, $statusColor)) {
        json_respond(0, 'OK');
    } else {
        json_respond(3, 'LED interaction error');
    }
}

function api_processColor() {
    

    $room = strtolower($_GET['room']);
    $color = strtolower($_GET['color']);

    if (empty(getRoomConfig($room))) {
        json_respond(1, 'Bad room');
    }

    if (isCorrectColor($color)) {
        if(leds_writeHex($room, $color)) {
            json_respond(0, 'OK');
        } else {
            json_respond(3, 'LED interaction error');
        }
    } else {
        json_respond(-1, 'Bad color');
    }
}

array_shift($route); // remove /api/
switch ($route[0])
{
    case 'shutdown' :
        if(leds_shutdown("*")) {
            json_respond(0, 'OK');
        } else {
            json_respond(3, 'LED interaction error');
        }
        break;

    case 'nightmode' :
        if(leds_setNightMode()) {
            json_respond(0, 'OK');
        } else {
            json_respond(3, 'LED interaction error');
        }
        break;

    case 'status': 
        api_processStatus();
        break;

    case 'color':
        api_processColor();
        break;

    default :
        json_respond (-1, 'Bad API request');
        break;
}