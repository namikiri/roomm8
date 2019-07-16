<?php
require_once 'engine/enconfig.php';
require_once 'engine/functions.php';
require_once 'engine/config.php';

error_reporting(E_ALL ^ E_NOTICE);

$route = explode('/', $_GET['route']);

switch ($route[0])
{
    case 'api' :
        require_once 'engine/api.php';
        break;

    case 'telegram' :
        require_once 'engine/telegram.php';
        break;

    default :
        header('HTTP/1.1 302 Nothing To See Here');
        header('Location: https://github.com/namikiri/roomm8');
        die ('Loal');
    break;
}