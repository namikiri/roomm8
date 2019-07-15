<?php

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

function telegram_initiateStatusSet() {

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

Use the following commands:

/nightmode to set the LEDs to night light mode
/shutdown to disable them entirely
/status `<room> (welcome|busy|gtfo)` to set your room status 
/color `<room> <color>` to set arbitrary color
/start to show this help
ROOMM8;

            telegram_sendMessage($startMessage, $chat);
            break;


        case 'status':
            telegram_sendMessage('The command `/setchat` is deprecated. Please use `/addchat` instead.', $chat);
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