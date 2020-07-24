<?php


class Database
{
    private const HOST = 'us-cdbr-east-02.cleardb.com';
    private const USERNAME = 'b08be9762eaaf9';
    private const PASSWORD = 'ec559987';
    private const DB = 'heroku_34a7b089fa72039';

    private MysqliDb $database;
    public function __construct()
    {
        $this->database = new MysqliDb (self::HOST, self::USERNAME, self::PASSWORD, self::DB);
    }

    private function getPatentByTitle($title)
    {
        return $this->database->where('title', $title)->getOne('patent');
    }

    public function getPatentById($id)
    {
        return json_encode($this->database->where('id', $id)->getOne('patent'));
    }

    private function getUserIdByChatId($chatId)
    {
        $result = $this->database->where('chat_id', $chatId)->getOne('user');
        return $result['id'];
    }

    public function savePatentToLibrary($chatId, $title)
    {
        $data = [
            'user_id' => $this->getUserIdByChatId($chatId),
            'patent_id' => $this->getPatentByTitle($title)['id']
        ];

        $this->database->insert('library', $data);
    }

    public function erasePatenFromLibrary($chatId, $title)
    {
        $userId = $this->getUserIdByChatId($chatId);
        $patentId = $this->getPatentByTitle($title)['id'];
        $this->database
            ->where('user_id', $userId)
            ->where('patent_id', $patentId)
            ->delete('library');
    }

    public function addNewUser($chatId)
    {
        if ($this->isNewUser($chatId))
        {
            $data = ['chat_id' => $chatId];
            $this->database->insert('user', $data);
        }
    }

    public function getAllUsers()
    {
        return $this->database->get('user');
    }

    private function isNewUser($chatId)
    {
        $result = $this->database
            ->where('chat_id', $chatId)
            ->getOne('user');
        return empty($result);
    }

    public function deleteAll($chatId)
    {
        $userId = $this->getUserIdByChatId($chatId);
        $this->database
            ->where('user_id', $userId)
            ->delete('library');
    }

    public function isPatentInLibraryByTitle($chatId, $title) : bool
    {
        $patentId = $this->getPatentByTitle($title)['id'];
        return $this->isPatentInLibraryById($chatId, $patentId);
    }

    public function isPatentInLibraryById($chatId, $id) : bool
    {
        $userId = $this->getUserIdByChatId($chatId);
        return !empty($this->database
            ->where('user_id', $userId)
            ->where('patent_id', $id)
            ->get('library'));
    }

    public function searchPatent($title) : bool
    {
        return !empty($this->getPatentByTitle($title));
    }

    public function addPatent($patent)
    {
        $data = [
            'title' => $patent->title,
            'description' => $patent->description,
            'urlToImage' => $patent->urlToImage
        ];
        $this->database->insert('patent', $data);
    }

    public function getAllPatents()
    {
        return json_encode($this->database->get('patent'));
    }

    public function getLibrary($chatId) : ?array
    {
        $userId = $this->getUserIdByChatId($chatId);
        $result = $this->database
            ->join('patent', 'library.patent_id = patent.id', 'LEFT')
            ->where('library.user_id', $userId)->get('library');

        if (empty($result))
        {
            return null;
        }
        return $result;
    }

}