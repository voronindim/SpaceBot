<?php


include ('vendor/autoload.php');
include('BotCommands.php');

$telegram = new BotCommands();

$telegram->sendNotification();