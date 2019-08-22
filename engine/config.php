<?php

$config = Array();

function config_load() {
    global $config;

    $content = file_get_contents(WORK_PATH.'/runtime/config.json');

    if (!empty($content)) {
        $config = json_decode($content, true);

        if (empty($config))
            $config = Array();
    }
}

function config_save() {
    global $config;

    file_put_contents(WORK_PATH.'/runtime/config.json', json_encode($config));
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

function config_getStatusColor($status) {

    $statusColorConfig = config_getVal('status_color_config', Array());
    $key = "";
    $defaultColor = "";

    switch ($status) {
        case 'welcome':
            $key = 'welcome';
            $defaultColor = WELCOME_COLOR;
            break;

        case 'busy':
            $key = 'busy';
            $defaultColor = BUSY_COLOR;
            break;

        case 'gtfo':
            $key = 'gtfo';
            $defaultColor = GTFO_COLOR;
            break;
        
        default:
            error_log('Bad status in config_getStatusColor()');
            return false;
    }

    return (empty($statusColorConfig[$key]) ? $defaultColor : $statusColorConfig[$key]);
}

function config_setStatusColor($status, $color) {
    $statusColorConfig = config_getVal('status_color_config', Array());

    switch ($status) {
        case 'welcome':
            $statusColorConfig['welcome'] = $color;
            break;

        case 'busy':
            $statusColorConfig['busy'] = $color;
            break;

        case 'gtfo':
            $statusColorConfig['gtfo'] = $color;
            break;
        
        default:
            error_log('Bad status in config_setStatusColor()');
            return false;
    }

    config_setVal('status_color_config', $statusColorConfig);
}

function config_setNMStartTime($time) {
    $nightmodeSettings = config_getVal('nightmode', Array());

    $nightmodeSettings['start_time'] = (int)$time;

    config_setVal('nightmode', $nightmodeSettings);
}

function config_getNMStartTime() {
    $nightmodeSettings = config_getVal('nightmode', Array());

    if (empty($nightmodeSettings['start_time'])) {
        return false;
    } else {
        return (int)$nightmodeSettings['start_time'];
    }
}


function config_setNMStopTime($time) {
    $nightmodeSettings = config_getVal('nightmode', Array());

    $nightmodeSettings['stop_time'] = (int)$time;

    config_setVal('nightmode', $nightmodeSettings);
}

function config_getNMStopTime() {
    $nightmodeSettings = config_getVal('nightmode', Array());

    if (empty($nightmodeSettings['stop_time'])) {
        return false;
    } else {
        return (int)$nightmodeSettings['stop_time'];
    }
}

function config_getNightmodeSchedule() {
    $nightmodeSettings = config_getVal('nightmode', Array());

    if (empty($nightmodeSettings['schedule'])) {
        return false;
    } else {
        return $nightmodeSettings['schedule'];
    }
}

function config_setNightmodeSchedule($schedule) {
    $nightmodeSettings = config_getVal('nightmode', Array());

    $schedule = array_sortby($schedule, 'time'); // schedule is always sorted and stored as sorted

    $nightmodeSettings['schedule'] = $schedule;

    config_setVal('nightmode', $nightmodeSettings);
}

function config_addNMScheduleItem($time, $color) {
    $schedule = config_getNightmodeSchedule();

    if (empty($schedule)) {
        $schedule = Array();
    }

    array_push($schedule, Array(
            'time' => $time,
            'color' => $color
        ));

    config_setNightmodeSchedule($schedule);
}

function config_removeNMScheduleItemByIndex($index) { // oh I'm so sorry for that name...
    $schedule = config_getNightmodeSchedule();

    if (empty($schedule)) {
        return false; // okay...
    }

    $index = (int)$index;

    if ($index >= count($schedule)) {
        return false;
    }

    unset($schedule[$index]);

    config_setNightmodeSchedule(array_values($schedule)); // re-indexed array

    return true;
}

config_load();