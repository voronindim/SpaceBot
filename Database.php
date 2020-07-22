<?php


class Database
{
    private MysqliDb $database;
    public function __construct()
    {
        $this->database = new MysqliDb ('us-cdbr-east-02.cleardb.com', 'b08be9762eaaf9', 'ec559987', 'heroku_34a7b089fa72039');
    }

    private function getPatentByTitle($title)
    {
        return $this->database->where('title', $title)->getOne('patent');
    }

    public function getPatentById($id)
    {
        return json_encode($this->database->where('id', $id)->getOne('patent'));
    }

    public function savePatentToLibrary($chatId, $title)
    {
        $data = [
            'chat_id' => $chatId,
            'patent_id' => $this->getPatentByTitle($title)['id']
        ];

        $this->database->insert('library', $data);
    }

    public function erasePatenFromLibrary($chatId, $title)
    {
        $patentId = $this->getPatentByTitle($title)['id'];
        $this->database
            ->where('chat_id', $chatId)
            ->where('patent_id', $patentId)
            ->delete('library');
    }


    public function deleteAll($chatId)
    {
        $this->database
            ->where('chat_id', $chatId)
            ->delete('library');
    }

    public function isPatentInLibraryByTitle($chatId, $title) : bool
    {
        $patentId = $this->getPatentByTitle($title)['id'];
        return $this->isPatentInLibraryById($chatId, $patentId);
    }

    public function isPatentInLibraryById($chatId, $id) : bool
    {
        return !empty($this->database
            ->where('chat_id', $chatId)
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

    public function getLibrary($chatId) : array
    {
        return $this->database
            ->join('patent', 'library.patent_id = patent.id', 'LEFT')
            ->where('library.chat_id', $chatId)->get('library');
    }

}