<?php

/**
 * Prints a string in an HTML header tag
 * @param string $txt - Text of the message
 * @param int    $s - The HTML header level
 */
function p($txt, $s = 3)
{
    if (is_array($txt)) {
        aPrint($txt);
        return;
    }
    echo "<h{$s}>{$txt}</h{$s}>";
}


/**
 * Prints an array in a readable form
 * @param array $a
 */
function aPrint(array $a)
{
    echo "<pre>";
    print_r($a);
    echo "</pre>";
}

/**
 * Redirects the user
 *
 * @param bool|string $url
 * @param int         $time
 */
function redirect($url = false, $time = 0)
{
    $url = $url ? $url : $_SERVER['HTTP_REFERER'];

    if (!headers_sent()) {
        if (!$time) {
            header("Location: {$url}");
        } else {
            header("refresh: $time; {$url}");
        }
    } else {
        echo "<script> setTimeout(function(){ window.location = '{$url}' }," . ($time * 1000) . ")</script>";
    }
}

/**
 * Gets a content of a GET variable either by name or position in the path
 * @param $index
 *
 * @return mixed
 */
function getVar($index)
{
    $tree = explode("/", @$_GET['path']);
    $tree = array_filter($tree);

    if (is_int($index)) {
        $res = @$tree[$index - 1];
    } else {
        $res = @$_GET[$index];
    }
    return $res;
}

/**
 * Triggers a 404 error
 */
function send404()
{
    if (!headers_sent()) {
        header("HTTP/1.0 404 Not Found");
        include("404.html");
        die();
    } else {
        redirect("404.html");
    }
}

/**
 * Generates HTML for a gravatar icon
 *
 * @param string $email
 * @param int    $size
 *
 * @return string
 */
function gravatar($email, $size = 80)
{
    $hash = md5($email);
    return "<img class='img-thumbnail' src='http://www.gravatar.com/avatar/{$hash}?d=monsterid&s={$size}'>";
}