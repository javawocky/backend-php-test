<?php

class TodoService {

    private $databaseConnection;

    function __construct($databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    public function fetchAllByUser($userId) {
        $sql = "SELECT * FROM todos WHERE user_id = ?";
        $todos = $this->databaseConnection->fetchAll($sql,array($userId));
        return $this->mapFromArray($todos);
    }

    public function fetchByIdAndUser($id, $userId) {
        $sql = "SELECT * FROM todos WHERE id = ? and user_id = ?";
        $todo = $this->databaseConnection->fetchAssoc($sql,array($id, $userId));

        return $this->map($todo);
    }

    public function add($todo) {
        $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
        return $this->databaseConnection->executeUpdate($sql,[$todo->getUserId(),$todo->getDescription()]);
    }

    public function toggleCompleted($id, $userId) {
        $sql = "UPDATE todos SET completed = NOT completed WHERE id = ? and user_id = ?";
        $this->databaseConnection->executeUpdate($sql,[$id,$userId]);
    }

    public function deleteByIdAndUser($id, $userId) {
        $sql = "DELETE FROM todos WHERE id = ? and user_id = ?";
        return $this->databaseConnection->executeUpdate($sql,[$id,$userId]);
    }

    private function mapFromArray($todos) {
        $todoEntities = array();
        foreach($todos as $todo) {
            $todoEntities[] = $this->map($todo);
        }

        return $todoEntities;
    }

    private function map($todo) {

        if(!isset($todo) || $todo === false || !is_array($todo)) return false;

        $todoEntity = new Todo();
        $todoEntity->setId($todo['id']);
        $todoEntity->setUserId($todo['user_id']);
        $todoEntity->setDescription($todo['description']);
        $todoEntity->setCompleted($todo['completed']);

        return $todoEntity;
    }
}
