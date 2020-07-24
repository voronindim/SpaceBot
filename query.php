<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

function query($url)
{
    $client = new Client();

    try
    {
        $result = $client->request('GET', $url);
    }
    catch (GuzzleException $e)
    {
        return null;
    }

    return json_decode($result->getBody());
}