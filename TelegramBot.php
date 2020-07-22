<?php

use Telegram\Bot\Api;

class TelegramBot
{
    private const TOKEN = "807483838:AAF2k7r7YmqqQ1eZQpK0yFdptR-xjJlnXA8";

    private Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(self::TOKEN);
    }

    public function getWebhook()
    {
        return $this->telegram->getWebhookUpdates();
    }

    public function createReplyKeyboardMarkup($keyboard) : string
    {
        return $this->telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true]);
    }

    public function sendMessage($chat_id, $text, $keyboard = null) : bool
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
            print_r($e->getMessage());
            return false;
        }
    }
}