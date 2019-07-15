<?php

function leds_write($room, $r, $g, $b) {
	global $roomsLedConfig;

	$fd = fopen(PIGPIO_DEVICE, 'w');

	if ($fd) {
		if (empty($roomsLedConfig[$room])) {
			error_log('Room ID ' . $room . ' is not configured');
			fclose($fd);

			return false;
		} else {
			$roomConfig = $roomsLedConfig[$room];

			$result = (fwrite($fd, "p {$roomConfig['led_r']} $r\n") &&
				       fwrite($fd, "p {$roomConfig['led_g']} $g\n") &&
					   fwrite($fd, "p {$roomConfig['led_b']} $b\n"));

			fclose($fd);

			return $result;
		}
	} else {
		error_log('Could not open PIGPIO device. Is daemon running?');
		return false;
	}
}

function leds_writeHex($room, $color) {

	$color = strtolower($color);

	if (preg_match('/^[0-9a-f]{6}$/', $color) == 1) {
		$colorInt = hexdec($color);

		$r = ($colorInt >> 16) & 0xFF;
		$g = ($colorInt >> 8) & 0xFF;
		$b = $colorInt & 0xFF;

		return leds_write($room, $r, $g, $b);
	} else {
		error_log('Bad color parameter, use 6-symbol hexadecimal string');
		return false;
	}
}

function leds_shutdown($room) {
	global $roomsLedConfig;

	if ($room === '*') {
		$status = true;
		foreach ($roomsLedConfig as $roomId => $roomConfig) {
			if (!leds_write($roomId, 0, 0, 0)) {
				$status = false;
			}
		}

		return $status;
	} else {
		return leds_write($room, 0, 0, 0);
	}
}

function leds_setNightMode() {
	global $roomsLedConfig;

	$status = true;
	foreach ($roomsLedConfig as $roomId => $roomConfig) {
			if (!leds_writeHex($roomId, NIGHTMODE_COLOR)) {
				$status = false;
			}
		}

	return $status;
}
