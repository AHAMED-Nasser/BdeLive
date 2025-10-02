<?php
class User {
    public function __construct(private $id, private $username, private $email, private $password) {
        database::connect();
    }

}
?>
