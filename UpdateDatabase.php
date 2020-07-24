<?php

include ('vendor/autoload.php');
include('Database.php');
include('GetInformationFromNASA.php');

$information = new GetInformationFromNASA();
$database = new Database();

$patents = json_decode($information->getPatents());

foreach ($patents as $patent)
{
    if (!$database->searchPatent($patent->title))
    {
        $database->addPatent($patent);
    }
}