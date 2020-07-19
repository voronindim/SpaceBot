<?php

include ('query.php');

const UNAVAILABLE_SERVICE_MESSAGE = "Sorry, the service is temporarily unavailable, please try again later.";

class GetInformationFromNASA
{
    private $token = "G56AJ2k89DUMHwInYmpFCkVBp0kzDEKHrZ1p1cWk";

    public function getAstronomicalPicture()
    {
        $url = "https://api.nasa.gov/planetary/apod?api_key=".$this->token;
        $response = query($url);

        if(empty($response))
        {
            return UNAVAILABLE_SERVICE_MESSAGE ;
        }

        $title = $response->title;
        $description = $response->explanation;
        $urlToImage = $response->url;

        return json_encode(['title' => $title, 'description' =>$description, 'urlToImage' => $urlToImage]);
    }

    private function replaceHtmlTags($string)
    {
        $eraseStr = '</span>';
        $eraseStr1 = '<span class="highlight">';

        $string = str_replace($eraseStr, "", $string);
        $string = str_replace($eraseStr1, "", $string);

        return $string;
    }

    private function getCorrectContent(&$article)
    {
        $title = $this->replaceHtmlTags($article[2]);
        $description = $this->replaceHtmlTags($article[3]);

        $urlToImage = str_replace(" ", "%20", $article[10]);
        return json_encode(['title' => $title, 'description' => $description, 'urlToImage' => $urlToImage]);
    }

    private function getRandomPatent(&$response)
    {
        $articleNumber = rand(0, $response->total);
        return $this->getCorrectContent($response->results[$articleNumber]);
    }

    public function getPatent()
    {
        $url = "https://api.nasa.gov/techtransfer/patent/?engine&api_key=".$this->token;
        $response = query($url);

        if(empty($response))
        {
            return UNAVAILABLE_SERVICE_MESSAGE;
        }

        return $this->getRandomPatent($response);
    }
}