<?php
/*
 * This index file servers as a simple unified request
 * controller and route handler
 */

if(!file_exists("core/config.php")){
	header("Location: install/");
}

$base = dirname($_SERVER['PHP_SELF']);
$pagePath = substr($_SERVER['REQUEST_URI'], strlen($base)+1);

include('core/config.php');

// Remove any URI variables
$pagePath = explode('?', $pagePath);
$pagePath = $pagePath[0];

// Trim any leading forward slash
$pagePath = trim($pagePath,"/");

if ( ! $pagePath )
{
	$pagePath = 'home';
}

$pageInclude = "page/$pagePath.php";

//Page not found
if(!file_exists($pageInclude) || strpos($pageInclude, ".."))
{
	send404();
}

$pageTitle = str_replace('/', ' ', $pagePath);
$pageTitle = ucfirst($pageTitle);

include 'page/header.php';
include $pageInclude;
include 'page/footer.php';