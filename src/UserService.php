<?php

class UserService
{
    private $app;
    function __construct($app) {
        $this->app = $app;
    }

    public function fetchByUsernameAndPassword($username, $password) {

        $sql = "SELECT * FROM users WHERE username = ? and password = ?";
        $user = $this->app['db']->fetchAssoc($sql, [$username, $password]);
        return $user;
    }


}
