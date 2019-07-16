<?php

$config = Array();

function config_load() {
    global $config;

    $content = file_get_contents('engine/runtime/config.json');

    if (!empty($content)) {
        $config = json_decode($content, true);

        if (empty($config))
            $config = Array();
    }
}

function config_save() {
    global $config;

    file_put_contents('engine/runtime/config.json', json_encode($config));
}

function config_getVal($key, $defaultValue = 0) {
    global $config;

    return empty($config[$key]) ? $defaultValue : $config[$key];
}

function config_setVal($key, $value) {
    global $config;

    $config[$key] = $value;
    config_save();
}

function config_getRoomPreference($userId) {
    $prefs = config_getVal('room_prefs', null);

    return $prefs[$userId];
}

function config_setRoomPreference($userId, $roomPref) {
    $prefs = config_getVal('room_prefs', null);

    if ($prefs === null) {
        $prefs = Array();
    }

    $prefs[$userId] = $roomPref;

    config_setVal('room_prefs', $prefs);
}


config_load();