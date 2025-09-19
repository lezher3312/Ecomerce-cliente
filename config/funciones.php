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
?>