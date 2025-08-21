<?php

namespace App\Interfaces;

interface AuthRepositoryInterface{
    public function findUserByUsername($username) :mixed;
    public function verifyPassword($username, $password) :bool;

    public function updateLastLogin($username):bool;

}