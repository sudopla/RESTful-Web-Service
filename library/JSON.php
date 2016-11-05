<?php
/**
 * Created by PhpStorm.
 * User: jpla
 * Date: 11/3/14
 * Time: 8:36 PM
 */

class JSON {

    public static function Deliver_Response($status, $data){

       switch ($status) {
           case "200": $status_message = "OK";
                        break;
           case "201": $status_message = "Created";
                        break;
           case "204": $status_message = "No Content";
                        break;
           case "400": $status_message = "Bad Request";
                        break;
           case "404": $status_message = "Not Found";
                        break;
           case "500": $status_message = "Bad Request";
                        break;
       }


        header('HTTP/1.1 '.$status.' '. $status_message);

        header('Content-type: application/json');
        echo json_encode($data);
    }
    
} 