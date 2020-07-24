<?php

include ('query.php');

class GetInformationFromNASA
{
    private const TOKEN = "G56AJ2k89DUMHwInYmpFCkVBp0kzDEKHrZ1p1cWk";
    private const API_URL = "https://api.nasa.gov/planetary/apod?api_key=" . self::TOKEN;
    private const API_PATENT_URL = "https://api.nasa.gov/techtransfer/patent/?engine&api_key=" . self::TOKEN;

    public function getAstronomicalPicture() : ?string
    {
        $response = query(self::API_URL);

        if(is_null($response))
        {
            return null;
        }

        $title = $response->title;
        $description = $response->explanation;
        $urlToImage = $response->url;

        return json_encode(['title' => $title, 'description' => $description, 'urlToImage' => $urlToImage]);
    }

    private function replaceHtmlTags($string) : string
    {
        $eraseStr = '</span>';
        $eraseStr1 = '<span class="highlight">';

        $string = str_replace($eraseStr, "", $string);
        $string = str_replace($eraseStr1, "", $string);

        return $string;
    }

    private function getCorrectContent($article) : array
    {
        $title = $this->replaceHtmlTags($article[2]);
        $description = $this->replaceHtmlTags($article[3]);

        $urlToImage = str_replace(" ", "%20", $article[10]);
        return ['title' => $title, 'description' => $description, 'urlToImage' => $urlToImage];
    }

    public function getPatents() : ?string
    {
        $response = query(self::API_PATENT_URL)->results;

        if(is_null($response))
        {
            return null;
        }

        $arrayOfPatents = [];

        foreach ($response as $item)
        {
            array_push($arrayOfPatents, $this->getCorrectContent($item));
        }

        return json_encode($arrayOfPatents);
    }
}