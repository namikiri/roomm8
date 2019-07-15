<?php

// Fill in the config params and rename me to enconfig.php

// LEDs pinout (RPi's GPIO IDs are used)
$roomsLedConfig = Array(
    "jonas" => Array(
        "led_r" => 17,
        "led_g" => 22,
        "led_b" => 24
    ),

    "alex" => Array(
        "led_r" => 4,
        "led_g" => 18,
        "led_b" => 27
    )
);

// System
define ('PIGPIO_DEVICE', '/dev/pigpio');
define ('NIGHTMODE_COLOR', '1E1E1E')
define ('WELCOME_COLOR', '00FF00');
define ('BUSY_COLOR', 'FFFF00');
define ('GTFO_COLOR', 'FF0000');

// Telegram
define ('TELEGRAM_BOT_TOKEN', ''); // Obtain in from @BotFather
define ('TELEGRAM_CALLBACK_KEY', ''); // This will be used in your webhook
define ('TELEGRAM_USE_DIRECT_RESPONSE', true); // respond directly or use HTTP API

// Add your Telegram account IDs here to allow these people to change LEDs state
$telegram_allowedIds = [31337, 228420];