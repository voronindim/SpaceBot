<?php

use Telegram\Bot\Api;

class TelegramBot
{
    private $token = "807483838:AAF2k7r7YmqqQ1eZQpK0yFdptR-xjJlnXA8";
    private $telegram;
    private $information;
    private $news;

    public function __construct()
    {
        $this->telegram = new Api($this->token);
        $this->information = new GetInformationFromNASA();
        $this->news = new News();
    }

    public function getWebHook()
    {
        return $this->telegram->getWebhookUpdates();
    }

    private $updateId;
    public function getUpdates()
    {
        $response = $this->telegram->getUpdates([
            'offset' => $this->updateId + 1
        ]);

        if (!empty($response))
        {
            $this->updateId = $response[count($response) - 1]->getUpdateId();
        }
        return $response;
    }

    public function createReplyKeyboardMarkup($keyboard)
    {
        return $this->telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true]);
    }

    public function sendMessage(&$chat_id, $text, $keyboard = null)
    {
        try
        {
            $this->telegram->sendMessage([
                'text' => $text,
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'reply_markup' => $keyboard
            ]);
            return true;
        }
        catch (Exception $e)
        {
             return false;
        }
    }

    public function getAstronomicalPicture()
    {
        return $this->information->getAstronomicalPicture();
    }

    public function getPatent()
    {

        return $this->information->getPatent();
    }

    public function getNews()
    {
        return $this->news->getNews();
    }
}