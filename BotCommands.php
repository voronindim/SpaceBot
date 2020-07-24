<?php

include('TelegramBot.php');
include('GetInformationFromNASA.php');
include('News.php');
include('Database.php');
include('Const.php');

class BotCommands
{
    private TelegramBot $telegramBot;

    private GetInformationFromNASA $information;

    private News $news;

    private Database $database;

    public function __construct()
    {
        $this->telegramBot = new TelegramBot();
        $this->information = new GetInformationFromNASA();
        $this->news = new News();
        $this->database = new Database();
    }

    public function sendNotification() : void
    {
        $allChatId = $this->database->getAllUsers();

        foreach ($allChatId as $item)
        {
            $chatId = $item['chat_id'];
            $this->telegramBot->sendMessage($chatId, Constants\NOTIFICATION);
            $this->sendAstronomicalPicture($chatId);
        }
    }

    private function sendRandomPatent($chatId) : void
    {
        $patents = json_decode($this->database->getAllPatents());
        $patent = $this->getRandomPatent($patents);

        if (empty($patent))
        {
            $this->telegramBot->sendMessage($chatId, Constants\UNAVAILABLE_SERVICE_MESSAGE);
            return;
        }

        $messageText = $this->getFormattedText($patent);
        $inline_keyboard = $this->createInlineButton();

        $this->telegramBot->sendMessage($chatId, $messageText, $inline_keyboard);
    }

    private function getRandomPatent($articles)
    {
        $articleNumber = rand(0, count($articles) - 1);
        return $articles[$articleNumber];
    }

    private function createInlineButton() : string
    {
        $inline = [[['text' => 'ADD TO LIBRARY', 'callback_data' => 'addToLibrary']]];
        $reply_markup = ['inline_keyboard' => $inline];
        return json_encode($reply_markup);
    }


    private function sendAstronomicalPicture($chatId) : void
    {
        $astronomicalPicture = $this->information->getAstronomicalPicture();

        if(is_null($astronomicalPicture))
        {
            $this->telegramBot->sendMessage($chatId, Constants\UNAVAILABLE_SERVICE_MESSAGE);
            return;
        }

        $astronomicalPicture = json_decode($astronomicalPicture);
        $messageText = $this->getFormattedText($astronomicalPicture);
        $this->telegramBot->sendMessage($chatId, $messageText);
    }

    private function sendNews($chatId) : void
    {
        $news = $this->news->getNews();

        if(is_null(null))
        {
            $this->telegramBot->sendMessage($chatId, Constants\NEWS_NOT_FOUND);
            return;
        }

        $news = json_decode($news);
        $messageText = $this->getFormattedText($news);
        $inline_keyboard = $this->createInlineUrl($news->url);

        $this->telegramBot->sendMessage($chatId, $messageText, $inline_keyboard);
    }

    private function createInlineUrl($url) : string
    {
        $inline = [[['text' => 'Read more', 'url' => $url]]];
        $reply_markup = ['inline_keyboard' => $inline];
        return json_encode($reply_markup);
    }

    private function sendLibrary($chatId) : void
    {
        $library = $this->database->getLibrary($chatId);

        if (is_null($library))
        {
            $this->telegramBot->sendMessage($chatId, Constants\LIBRARY_IS_EMPTY);
            return;
        }

        $libraryMessage = $this->createLibraryMessage($library);

        $messageText = $libraryMessage['text'];
        $reply_markup = $libraryMessage['reply_markup'];

        $this->telegramBot->sendMessage($chatId, $messageText, $reply_markup);
    }

    private function createLibraryMessage($library) : array
    {
        $message = '<b>' . 'LIBRARY' . '</b>' . "\n\n";
        $count = 1;
        $libraryButtons = [];

        foreach ($library as $item)
        {
            $message = $message . $count . ". " . $item['title'] . "\n\n";
            $id = $item['id'];
            array_push($libraryButtons, ['text' => $count, 'callback_data' => $id]);
            $count++;
        }

        $libraryButtons = array_chunk($libraryButtons, 5);
        array_push($libraryButtons, [['text' => 'DELETE ALL', 'callback_data' => 'deleteAll']]);

        $reply_markup = json_encode(['inline_keyboard' => $libraryButtons]);

        return [
            'text' => $message,
            'reply_markup' => $reply_markup,
        ];
    }

    private function sendWelcomeMessage($chatId) : void
    {
        $commands = $this->telegramBot->createReplyKeyboardMarkup(Constants\MENU);
        $this->telegramBot->sendMessage($chatId, Constants\WELCOME_MESSAGE, $commands);
    }

    private function sendPatent($chatId, $id) : void
    {
        if (!$this->database->isPatentInLibraryById($chatId, $id))
        {
            $this->telegramBot->sendMessage($chatId, Constants\PATENT_NOT_FOUND_IN_LIBRARY);
            return;
        }
        $patent = json_decode($this->database->getPatentById($id));
        $text = $this->getFormattedText($patent);

        $inline = [[['text' => "DELETE FROM LIBRARY", 'callback_data' => 'deleteFromLibrary']]];
        $reply_markup = json_encode(['inline_keyboard' => $inline]);

        $this->telegramBot->sendMessage($chatId, $text, $reply_markup);
    }

    private function getFormattedText($article) : string
    {
        $title = $article->title . "\n\n";
        $description = $article->description . "\n\n";
        $photoUrl = $article->urlToImage;

        return '<b>' . $title . '</b>' . $description . $photoUrl;
    }

    private function tryHandleCommand($chatId, $userText) : void
    {
        switch ($userText)
        {
            case "/start":
                $this->sendWelcomeMessage($chatId);
                $this->database->addNewUser($chatId);
                break;
            case Constants\ASTRONOMY_PICTURE:
                $this->sendAstronomicalPicture($chatId);
                break;
            case Constants\RANDOM_PATENT:
                $this->sendRandomPatent($chatId);
                break;
            case Constants\NEWS:
                $this->sendNews($chatId);
                break;
            case Constants\LIBRARY:
                $this->sendLibrary($chatId);
                break;
            default:
                $this->telegramBot->sendMessage($chatId, Constants\USE_KEYBOARD_MESSAGE);
        }
    }

    private function handleCommand($update) : void
    {
        $chatId = $update->getMessage()->getChat()->getId();
        $userText = $update->getMessage()->getText();

        try
        {
            $this->tryHandleCommand($chatId, $userText);
        }
        catch (Exception $e)
        {
            $this->telegramBot->sendMessage($chatId, Constants\UNAVAILABLE_SERVICE_MESSAGE);
        }
    }

    private function tryUseCallbackQuery($callback) : void
    {
        switch ($callback['data'])
        {
            case Constants\ADD_TO_LIBRARY:
                $this->addToLibrary($callback['message']);
                break;
            case Constants\DELETE_FROM_LIBRARY:
                $this->removeFromLibrary($callback['message']);
                break;
            case Constants\DELETE_ALL:
                $this->deleteAllPatents($callback['message']);
                break;
            default:
                $chatId = $callback['message']['chat']['id'];
                $id = $callback['data'];
                $this->sendPatent($chatId, $id);
        }
    }

    private function useCallbackQuery($update) : bool
    {
        try
        {
            if (isset($update['callback_query']))
            {
                $callback = $update['callback_query'];
                $this->tryUseCallbackQuery($callback);
                return true;
            }
            return false;
        }
        catch (Exception $e)
        {
            $chatId = $update['callback_query']['message']['chat']['id'];
            $this->telegramBot->sendMessage($chatId, Constants\UNAVAILABLE_SERVICE_MESSAGE);
            return true;
        }
    }

    public function useBot() : void
    {
        $update = $this->telegramBot->getWebhook();
        if (!$this->useCallbackQuery($update))
        {
            $this->handleCommand($update);
        }
    }

    public function addToLibrary($message) : void
    {
        $text = $message['text'];
        $chatId = $message['chat']['id'];

        $title = mb_strstr($text, "\n\n", true);

        if ($this->database->isPatentInLibraryByTitle($chatId, $title))
        {
            $this->telegramBot->sendMessage($chatId, Constants\PATENT_IN_LIBRARY);
        }
        else
        {
            $this->database->savePatentToLibrary($chatId, $title);
            $this->telegramBot->sendMessage($chatId, Constants\CORRECT_ADD_TO_LIBRARY);
        }
    }

    public function deleteAllPatents($message) : void
    {
        $chatId = $message['chat']['id'];
        try
        {
            $this->database->deleteAll($chatId);
            $this->telegramBot->sendMessage($chatId, Constants\ALL_PATENTS_REMOVE);
        }
        catch (Exception $e)
        {
            $this->telegramBot->sendMessage($chatId, Constants\ERROR_DELETE_PATENTS);
        }

    }

    public function removeFromLibrary($message) : void
    {
        $text = $message['text'];
        $chatId = $message['chat']['id'];

        $title = mb_strstr($text, "\n\n", true);

        try
        {
            $this->database->erasePatenFromLibrary($chatId, $title);
            $this->telegramBot->sendMessage($chatId, Constants\DELETE_PATENT);
        }
        catch (Exception $e)
        {
            $this->telegramBot->sendMessage($chatId, Constants\ERROR_DELETE_PATENT);
        }

    }
}
