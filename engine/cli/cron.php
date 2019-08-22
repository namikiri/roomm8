<?php

require_once (dirname(__FILE__).'/../enconfig.php');
require_once (WORK_PATH.'/functions.php');
require_once (WORK_PATH.'/config.php');
require_once (WORK_PATH.'/leds.php');


echo ("Welcome to Roomm8 automatic scheduling script!\n");
echo ("Loading schedule...\n");

$schedule = config_getNightmodeSchedule();

if (empty($schedule)) {
	die ("No schedule set, exiting.\n");
}

$currentTime = currentMinutes();

echo ("Current time is " . minutes2Time($currentTime) . " ($currentTime minutes)\n");
echo ("Checking schedule...\n");

/*

	TODOs:
	- check nightmode range
	- check end_time to shutdown leds
	- test this shit

*/

$startTime = config_getNMStartTime();
$stopTime = config_getNMStopTime();

printf("Time: Start time %s (%dm), End time %s (%dm)\n", minutes2Time($startTime), $startTime, minutes2Time($stopTime), $stopTime);

if (isInRange($currentTime, $startTime, $stopTime)) {

	echo ("Oh, it's night mode time! Let's check if some color is set for this minute...\n");

	foreach ($schedule as $item) {
		if ($item['time'] === $currentTime) {
			echo ("A match found! Setting the color " . $item['color'] . "\n");
			leds_setNightMode($item['color']);
			break;
		}
	}
} elseif ($currentTime === $stopTime) {
	echo ("It's time to stop the night mode...\n");
	leds_shutdown("*");
}