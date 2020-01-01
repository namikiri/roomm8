<?php

require_once (dirname(__FILE__).'/../enconfig.php');
require_once (WORK_PATH.'/functions.php');
require_once (WORK_PATH.'/config.php');
require_once (WORK_PATH.'/leds.php');

// It was easier to predefine colors than generate them
$darkColors = [
	'010101',
	'101010',
	'202020',
	'303030',
	'505050',
	'606060',
	'707070',
	'000000',
	'020202',
	'030303',
	'040404',
	'f0c0c0',
	'404040',
	'ff0000',
	'00ffff',
	'ff00ff',
	'ffff00',
	'0c8ae7'
];

echo ("Happy Halloween! :3\n");

leds_setNightMode();

while (true) {
	if (rand(0, 100) > 83) {
		leds_setNightMode($darkColors[rand(0, count($darkColors) - 1)]);
		usleep(rand(1, 100) * 1000);
		leds_setNightMode();
	} else {
		leds_setNightMode();
	}

	usleep(rand(1, 13) * 10000);
}