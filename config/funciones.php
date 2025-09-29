<?php

function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

function isauth() : bool{
  if(!isset($_SESSION)){
    session_start();
  }
  return isset($_SESSION['NOMBRE']) && !empty($_SESSION);
}
?>