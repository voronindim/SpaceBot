<?php

class News
{
    private const TOKEN = "34ff380705cc465fa3c53f34e1914259";
    private const SEARCH_PARAMETERS = "NASA";
    private const SEARCH_URL = "http://newsapi.org/v2/everything?q=" . self::SEARCH_PARAMETERS . "&language=en&sortBy=publishedAt&apiKey=" . self::TOKEN;

    private function createArrayWithData($article) : string
    {
        $title = $article->title;
        $description = $article->description;
        $urlToImage = $article->urlToImage;
        $url = $article->url;

        return $result = json_encode(['title' => $title, 'description' => $description, 'urlToImage' => $urlToImage, 'url' => $url]);
    }

    public function getNews() : string
    {
        $response = query(self::SEARCH_URL);

        if(empty($response))
        {
            return "";
        }

        if(empty($response->articles))
        {
            return "";
        }

        $firstArticle = $response->articles[0];

        return $this->createArrayWithData($firstArticle);

    }

}