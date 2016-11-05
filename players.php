<?php
//Get data statistics

include "./library/User.php";
include "./library/JSON.php";

if ($_GET) {

     if ($_GET['action'] == 'find_players') {

        $player = $_GET['player'];
        try {
            $User = new User();
            $json_data = $User->Find_Players($player);

            JSON::Deliver_Response(200, $json_data);

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_GET['action'] == 'get_top_scores') {

        try {
            $User = new User();
            $json_data = $User->Get_Top_Scores();

            JSON::Deliver_Response(200, $json_data);

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }


    } else {
        //No action to be executed
        JSON::Deliver_Response(400, NULL);
    }

}