<?php

require_once 'engine/leds.php';

function curl_request ($method, $type, $data = Array()) {
    $curl = curl_init();

    if($curl)
    {
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot'.TELEGRAM_BOT_TOKEN.'/'.$method.($type == 'get' ? '?'.http_build_query($data) : ''));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Roomm8; +https://github.com/namikiri/roomm8');
        
        if ($type == 'post')
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        
        $out = curl_exec($curl);
        curl_close($curl);
        return (empty ($out)) ? false : $out;
    } else
        return false;
}

// NB: if $dontExit is set to false, Roomm8 will exit the script after sending the message
function telegram_sendMessage ($text, $chat, $additionalParams = null, $dontExit = false)
{
    $message = Array (
        'text' => $text,    
        'parse_mode' => 'markdown',
        'chat_id' => $chat);

    if (!empty($additionalParams))
            $message = array_merge($message, $additionalParams);

    if (TELEGRAM_USE_DIRECT_RESPONSE && !$dontExit) // see enconfig.php
    {
        $message = array_merge($message, Array('method' => 'sendMessage'));

        header('Content-Type: application/json');
        die(json_encode($message));
    }
    else {
        $answer = curl_request('sendMessage', 'post', $message);

        if (!$dontExit) {
            die('OK');
        }
    }
}

function telegram_processStatusCommand($commands, $chat, $user) {
    $room = getRoomConfig($commands[1]);

    if ($room === false) {
        telegram_sendMessage('Bad room ID', $chat);
    }

    $statusColor = statusText2Color($commands[2]);

    if ($statusColor === false) {
        telegram_sendMessage('Bad status, can be either `welcome`, `busy` or `gtfo`', $chat);
    }

    if (leds_writeHex($commands[1], $statusColor)) {
        telegram_sendMessage('Status has been set', $chat);
    } else {
        telegram_sendMessage('LED interaction failed, check `pigpiod`', $chat);
    }
}

function telegram_processColorCommand($commands, $chat, $user) {
    $room = getRoomConfig($commands[1]);

    if ($room === false) {
        telegram_sendMessage('Bad room ID', $chat);
    }

    if (isCorrectColor($commands[2])) {
        if (leds_writeHex($commands[1], $commands[2])) {
            telegram_sendMessage('Color has been set', $chat);
        } else {
            telegram_sendMessage('LED interaction failed, check `pigpiod`', $chat);
        }
        
    } else {
        telegram_sendMessage('Bad color, can be 6-symbol hex code, e.g. `FACE8D`', $chat);
    }
}

function telegram_processConfigCommand($commands, $chat, $user) {
    switch ($commands[1]) {
        case 'statuscolor':
            switch ($commands[2]) { // easy validation
                case 'welcome':
                case 'busy':
                case 'gtfo':
                    if (isCorrectColor($commands[3])) {
                        config_setStatusColor($commands[2], $commands[3]);
                        telegram_sendMessage('Successfully set new status color', $chat);
                    } else {
                        telegram_sendMessage('Bad color code, use 6-digit hexadecimal', $chat);
                    }
                    break;

                default:
                    telegram_sendMessage('Bad status code, can be either `welcome`, `busy` or `gtfo`', $chat);
            }
            break;

        case 'help':
                    $helpMessage = <<<CONFIGHELP
Roomm8 can be configured using a `/config` command. 
Common syntax is: `/config <parameter> <arguments>`

Available `parameter`s:
---
`statuscolor` - set status color. 
Syntax: `/config statuscolor <room> <color>`
---
CONFIGHELP;

                telegram_sendMessage($helpMessage, $chat);
            break;

        default:
            telegram_sendMessage('Unknown configuration directive', $chat);
    }
}

function telegram_processCommand($commandline, $chat, $user, $messageId)
{
    $commandline = mb_substr($commandline, 1, NULL, 'UTF-8');

    if (strpos($commandline, '@') > -1)
        $commandline = mb_substr($commandline, 0, mb_strpos($commandline, '@', 0, 'UTF-8'), 'UTF-8');

    if (strlen($commandline) == 0) {
        telegram_sendMessage('Empty command? Really? Why?', $chat);
        return; // not a command
    }

    $commands = explode(' ', $commandline);

    if ($commands[0] == 'whoami') {
        telegram_sendMessage(sprintf('Your id is `%d`.', $user), $chat);
        return;
    }

    global $telegram_allowedIds;

    if (!in_array($user, $telegram_allowedIds)) {
        telegram_sendMessage('Not authorized', $chat);
        return;
    }

    switch ($commands[0]) {

        case 'start':
            $startMessage = <<<ROOMM8
Hi! This is Roomm8 bot.

Status shorthand commands:
/welcome to say that you're welcoming 
/busy to indicate that you're busy right now but it's still OK to ask you
/gtfo to tell your neighbours to fuck off

Common commands:
/nightmode to set the LEDs to night light mode
/shutdown to disable them entirely

Configuration & less common commands:
/setroom `<room>` to set your room
/status `<room> (welcome|busy|gtfo)` to set your room status 
/color `<room> <color>` to set arbitrary color
/start to show this help
ROOMM8;

            telegram_sendMessage($startMessage, $chat);
            break;

        case 'nightmode':
            if(leds_setNightMode()) {
                telegram_sendMessage('Successfully set night mode', $chat);
            } else {
                telegram_sendMessage('LED interaction failed, check `pigpiod`', $chat);
            }
            break;

        case 'shutdown':

            $shutdownRoom = ($commands[1] === '*') ? '*' : config_getRoomPreference($user);

            if ($shutdownRoom === null) {
                telegram_sendMessage('No room preference set. Use `/setroom <room>` to set your room preference or `/shutdown *` to shut down all the lights.', $chat);
            } else {
                if(leds_shutdown($shutdownRoom)) {
                    telegram_sendMessage('Successfully performed shutdown operation', $chat);
                } else {
                    telegram_sendMessage('LED interaction failed, check `pigpiod`', $chat);
                }
            }
            break;

        case 'status':
            telegram_processStatusCommand($commands, $chat, $user);
            break;

        case 'config':
            telegram_processConfigCommand($commands, $chat, $user);
            break;

        case 'color':
            telegram_processColorCommand($commands, $chat, $user);
            break;

        case 'setroom':
            if (getRoomConfig($commands[1]) !== false) {
                config_setRoomPreference($user, strtolower($commands[1]));

                $prefHint = <<<PREFHINT
Successfully set the room preference!

Now you can use these shorthand commands:
/welcome to say that you're welcoming 
/busy to indicate that you're busy right now but it's still OK to ask you
/gtfo to tell your neighbours to fuck off
PREFHINT;

                telegram_sendMessage($prefHint, $chat);
            } else {
                telegram_sendMessage('Bad room ID', $chat);
            }
            break;

        case 'welcome':
        case 'busy':
        case 'gtfo':
            $roomPref = config_getRoomPreference($user);

            if ($roomPref === null) {
                telegram_sendMessage('No room preference set, use `/setroom <room_id>`', $chat);
            } else {
                $color = statusText2Color($commands[0]);

                if(leds_writeHex($roomPref, $color)) {
                    telegram_sendMessage('Successfully set your room status', $chat);
                } else {
                    telegram_sendMessage('LED interaction failed, check `pigpiod`', $chat);
                }
            }
            break;

        default:
            telegram_sendMessage('Bad command.', $chat);
    }

}

function telegram_processMessage($message) {
    $user = (int)$message['from']['id'];
    $chat = (int)$message['chat']['id'];
    $post = (int)$message['message_id'];

    $text = $message['text'];

    if ($text[0] === '/') {
        telegram_processCommand($text, $chat, $user, $post);
    }

    die ("OK");
}

function telegram_processInput() {
    global $route;

    if ($route[1] !== TELEGRAM_CALLBACK_KEY)
        die('Bad Telegram API Key!');

    $event = json_decode(file_get_contents('php://input'), true);

    if (empty($event))
        die('Bad event data.');

    if (!empty($event['message'])) {
        telegram_processMessage($event['message']);
    }
}


telegram_processInput();