<?php

include ('vendor/autoload.php');
include('BotCommands.php');

$telegram = new BotCommands();

try
{
    $telegram->useBot();
}
catch (Exception $e)
{
}
