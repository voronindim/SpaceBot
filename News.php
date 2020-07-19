<?php

class News
{
    private $token = "34ff380705cc465fa3c53f34e1914259";

    private function createArrayWithRequiredData(&$article)
    {
        $title = $article->title;
        $description = $article->description;
        $urlToImage = $article->urlToImage;
        $url = $article->url;

        return json_encode(['title' => $title, 'description' => $description, 'urlToImage' => $urlToImage, 'url' => $url]);
    }

    public function getNews()
    {
        $url = "http://newsapi.org/v2/everything?q=new technologies, NASA&language=en&sortBy=publishedAt&apiKey=".$this->token;

        $response = query($url);

        if(empty($response))
        {
            return UNAVAILABLE_SERVICE_MESSAGE;
        }

        if(empty($response->articles))
        {
            return "Sorry, but no news lately";
        }

        $firstArticle = $response->articles[0];

        return $this->createArrayWithRequiredData($firstArticle);

    }

}