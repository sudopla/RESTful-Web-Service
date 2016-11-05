<?php

//Front Controller
//.htaccess make all url to be handle by this script
//Enable mod_rewrite module in Apache and ensure that directives could be overridden (AllowOverride All)

//route the request internally
$location = $_SERVER['REQUEST_URI'];
if ($_SERVER['QUERY_STRING']) {
    $location = substr($location, 0, strrpos($location, $_SERVER['QUERY_STRING']) - 1);
}

$uri = $location;

if ($uri == '/index.php') {
    //
} elseif ($uri == '/web_service/json/user') {  //remove 'web_service' when the service is hosted.

    require "login.php";

}  elseif ($uri == '/web_service/json/players') {

    require "players.php";

} elseif ($uri == '/web_service/json/game') {

    require "play.php";

} else {

    header('Status: 404 Not Found');
    echo '<html><body><h1>Page: '.$uri.' Not Found</h1></body></html>';

}