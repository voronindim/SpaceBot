<?php

function query($url)
{
    $client = new \GuzzleHttp\Client();

    try
    {
        $result = $client->request('GET', $url);
    }
    catch (\GuzzleHttp\Exception\GuzzleException $e)
    {
        return [];
    }

    return json_decode($result->getBody());
}