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
    return config_getStatusColor(strtolower($statusText));
}

function getRoomConfig($room) {
    global $roomsLedConfig;

    if (empty($roomsLedConfig[$room])) {
        return false;
    } else {
        return $roomsLedConfig[$room];
    }
}

function time2Minutes($time) {
    $timeComponents = Array();

    if (preg_match('/^([0-9]{1,2}):([0-9]{1,2})$/', $time, $timeComponents) === 1) {
        $hours = (int)$timeComponents[1];
        $minutes = (int)$timeComponents[2];

        return ($hours * 60) + $minutes;

    } else {
        return false;
    }
}

function minutes2Time($minutes) {
    $hours = floor($minutes / 60);
    $minutes = floor($minutes % 60);

    return ($hours < 10 ? "0" : "") . $hours . ":" . ($minutes < 10 ? "0" : "") . $minutes;
}

function currentMinutes() {
    return time2Minutes(date('H:i'));
}

function isInRange($check, $start, $end) {
    if ($start < $end) {
        return ($check >= $start && $check < $end);
    } else {
        return ($check > $end && $check <= $start);
    }
}

function array_sortby($array, $on, $order = SORT_ASC) {
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

