<?php
//Manage the game (game request, start game, calculate score, ..)

include "./library/Game.php";
include "./library/JSON.php";

if ($_POST) {

    if ($_POST['action'] == 'request_to_play') {
        //send a request to user to play with

        $user_id = $_POST['user_id'];
        $request_user_id = $_POST['request_user_id']; //app knows request_user_id 'cause Get_Online_User return that value too
        $type_game = $_POST['type_game'];

        try {

            $Game = new Game();
            $json_data = $Game->Request_to_Play($user_id, $request_user_id, $type_game);

            JSON::Deliver_Response(200, $json_data);

            $Game->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'accept_request') {
        //Accepted Request. App know id of request

        $id_request = $_POST['id_request'];

        try {

            $Game = new Game();
            $json_data = $Game->Accept_Request($id_request);

            JSON::Deliver_Response(200, $json_data);

            $Game->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'cancel_request') {
        //Cancel a request (mainly if a user's request was not accepted by anybody)
        //When the time of waiting for a response expire

        $id_request = $_POST['id_request'];

        try {

            $Game = new Game();
            $Game->Delete_Request($id_request);

            $json_data = array('status' => 'success');
            JSON::Deliver_Response(200, $json_data);

            $Game->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'process_vector') {
        //The position vector is send

        $user_id = $_POST['user_id'];
        $id_play = $_POST['id_play'];
        $type_game = $_POST['type_game'];
        $num_vector = $_POST['num_vector'];
        $vector = $_POST['vector'];  //Array with the values of position sample every x time


        try {
            $Game = new Game();

            //Clean Variables First
            $user_id = $Game->Clean_Variable($user_id);
            $id_play = $Game->Clean_Variable($id_play);
            $type_game = $Game->Clean_Variable($type_game);
            $num_vector = $Game->Clean_Variable($num_vector);
            $vector = $Game->Clean_Variable_Array($vector);

            //just to test the value of a vector
            //$vector = [1,2,3,4,5];

            $flag_finished_no_limit = false;

            //Check if the play have not been abandoned by one of the users
            $abandoned = $Game->Check_Status('Abandoned', $type_game, $id_play);

            if($abandoned == true) {
                $json_data = array('status' => 'abandoned', 'message' => 'Player abandoned the game');
                JSON::Deliver_Response(200, $json_data);

                //Delete the entry in score table (in vector Table isn't necessary 'cause when change to this state it was done
                $Game->Delete_Entry_Score($id_play, $type_game);

                //Change user's state to online again
                $Game->Change_User_State_by_id($user_id, 'online');

                die;
            }

            //if it's a play without limit verify if one of the user want to finish
            if($type_game == 2) {
                $flag_finished_no_limit = $Game->Check_Status('Finished', 2, $id_play);
            }

            if($flag_finished_no_limit == true) {
                //Change user's state to online again
                $Game->Change_User_State_by_id($user_id, 'online');
            }

            //check counter to see if both player continuing sending vectors
            //this function also remove data of tables if the play finished
            $stopped = $Game->Check_Counter($id_play, $type_game);

            if ($stopped == true) {
                //One of the User abandoned the game

                $json_data = array('status' => 'stopped', 'message' => 'Player lost connection');
                JSON::Deliver_Response(200, $json_data);

                //Change user's state to online again
                $Game->Change_User_State_by_id($user_id, 'online');


            } else {
                //Return Score to user first
                $score = $Game->Get_Score($id_play, $type_game);
                if( $flag_finished_no_limit == true) {
                    $json_data = array('status' => 'finished', 'score' => $score);
                } else {
                    $json_data = array('status' => 'OK', 'score' => $score);
                }

                JSON::Deliver_Response(200, $json_data);

                //Process the vector and do other things
                if( $flag_finished_no_limit != true) {
                    $Game->Process_Vector($id_play, $type_game, $num_vector, $vector);
                }

            }

            $Game->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'finish_play') {
        //Finish the play when the time expired or when a game without limit finished

        $user_id = $_POST['user_id'];
        $id_play = $_POST['id_play'];
        $type_game = $_POST['type_game'];

        try {

            $Game = new Game();
            $Game->Finish_Play($id_play, $type_game);
            $score = $Game->Get_Score($id_play, $type_game);

            $json_data = array('status' => 'success', 'score' => $score);
            JSON::Deliver_Response(200, $json_data);

            //Change user's state to online again
            $user_id = $Game->Clean_Variable($user_id);
            $Game->Change_User_State_by_id($user_id, 'online');

            $Game->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'abandon_play') {
        //When a user explicitly closes the play before it finishes

        $user_id = $_POST['user_id'];
        $id_play = $_POST['id_play'];
        $type_game = $_POST['type_game'];

        try {
            $Game = new Game();
            $Game->Abandon_Play($id_play, $type_game);

            //Change user's state to online again
            $user_id = $Game->Clean_Variable($user_id);
            $Game->Change_User_State_by_id($user_id, 'online');

            $json_data = array('status' => 'success');
            JSON::Deliver_Response(200, $json_data);

            $Game->Close_Connection();

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

if ($_GET) {

    if ($_GET['action'] == 'get_request_play') {
        //Function to see if any player wants to play with him

        $user_id = $_GET['user_id'];

        try {

            $Game = new Game();
            $json_data = $Game->Get_Request_Play($user_id);

            JSON::Deliver_Response(200, $json_data);

            $Game->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_GET['action'] == 'see_if_accepted') {
        //see if the request sent to play was accepted

        $id_request = $_GET['id_request'];

        try {

            $Game = new Game();
            $json_data = $Game->See_If_Accepted($id_request);

            JSON::Deliver_Response(200, $json_data);

            $Game->Close_Connection();

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