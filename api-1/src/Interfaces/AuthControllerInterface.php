<?php 

namespace App\Interfaces;

Interface AuthControllerInterface{
    public function login($data) : bool|string ;
}