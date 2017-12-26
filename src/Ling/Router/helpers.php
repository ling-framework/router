<?php
namespace Ling\Router;

function redirect($url) {

}

function error_404() {

}

function abort($error_no) {}


function getRealIPAddress(){
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }
    return $ip;
}