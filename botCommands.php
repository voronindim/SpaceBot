<?php

include ('TelegramBot.php');
include('GetInformationFromNASA.php');
include ('News.php');

define('MENU', array(
    array('Astronomy picture of the day'),
    array('Get random the NASA patent'),
    array('NASA NEWS'),
));

const NOTIFICATION = "Hello, don't forget to see the astronomical picture of the day";

class botCommands
{
    private $telegramBot;
    public  function __construct()
    {
        $this->telegramBot = new TelegramBot();
    }


    private function sendTechTransform(&$chat_id)
    {
        $count = 0;
        $patent = json_decode($this->telegramBot->getPatent());
        $messageText = $this->getFormattedText($patent);
        while (!$this->telegramBot->sendMessage($chat_id, $messageText))
        {
            if(++$count > 20)
            {
                $this->telegramBot->sendMessage($chat_id, UNAVAILABLE_SERVICE_MESSAGE);
                break;
            }
        }
    }

    private function sendAstronomicalPicture(&$chat_id)
    {
        $astronomicalPicture = json_decode($this->telegramBot->getAstronomicalPicture());
        $messageText = $this->getFormattedText($astronomicalPicture);
        $this->telegramBot->sendMessage($chat_id, $messageText);
    }

    private function getFormattedText($response)
    {
        $title = $response->title."\n\n";
        $description = $response->description."\n\n";
        $photoUrl = $response->urlToImage;
        return '<b>'.$title.'</b>'.$description.$photoUrl;
    }

    private function sendNews(&$chat_id)
    {
        $news = json_decode($this->telegramBot->getNews());

        $inline = [[['text'=>'Read more', 'url'=>$news->url]]];
        $reply_markup = ['inline_keyboard'=>$inline];
        $inline_keyboard = json_encode($reply_markup);

        $messageText = $this->getFormattedText($news);

        $this->telegramBot->sendMessage($chat_id, $messageText, $inline_keyboard);
    }

    private function sendWelcomeMessage(&$chat_id)
    {
        $commands = $this->telegramBot->createReplyKeyboardMarkup(MENU);
        $this->telegramBot->sendMessage($chat_id, "Welcome! Use the built-in keyboard", $commands);
    }

    public function useBot()
    {
        $updates = $this->telegramBot->getUpdates();
        foreach ($updates as $update)
        {
            $chat_id = $update->getMessage()->getChat()->getId();
            $user_text = $update->getMessage()->getText();

            if ($user_text == "/start")
            {
                $this->sendWelcomeMessage($chat_id);
            }
            elseif ($user_text == "Astronomy picture of the day")
            {
                $this->sendAstronomicalPicture($chat_id);
            }
            elseif ($user_text == "Get random the NASA patent")
            {
                $this->sendTechTransform($chat_id);
            }
            elseif ($user_text == "NASA NEWS")
            {
                $this->sendNews($chat_id);
            }
            else
            {
                $this->telegramBot->sendMessage($chat_id, "Please, use the built-in keyboard");
            }
        }
    }

    public function sendNotification($chat_id)
    {
        $this->telegramBot->sendMessage($chat_id, NOTIFICATION);
    }
}