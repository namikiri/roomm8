<?php

require_once 'engine/leds.php';

function api_processStatus() {
    global $roomsLedConfig;

    $room = $_GET['room'];
    $status = $_GET['status'];

    if (empty($roomsLedConfig[$room])) {
        json_respond(1, 'Bad room');
    }

    $statusColor = '';

    switch ($status) {
        case 'welcome':
            $statusColor = WELCOME_COLOR;
            break;

        case 'busy': 
            $statusColor = BUSY_COLOR;
            break;

        case 'gtfo':
            $statusColor = GTFO_COLOR;
            break;

        default:
            json_respond(2, 'Bad status');
    }

    if (leds_writeHex($room, $statusColor)) {
        json_respond(0, 'OK');
    } else {
        json_respond(3, 'LED interaction error');
    }
}

function api_processColor() {
    global $roomsLedConfig;

    $room = strtolower($_GET['room']);
    $color = strtolower($_GET['color']);

    if (empty($roomsLedConfig[$room])) {
        json_respond(1, 'Bad room');
    }

    if (preg_match('/^[0-9a-f]{6}$/', $color) == 1) {
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