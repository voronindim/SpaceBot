<?php

include('BotCommands.php');

$telegram = new BotCommands();

$telegram->addNewPatentsToDatabase();