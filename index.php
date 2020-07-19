<?php

include ('vendor/autoload.php');
include('botCommands.php');

$telegram = new botCommands();

while(true)
{
    $telegram->useBot();
}
