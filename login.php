<?php
//Manage Users (new user, login, sessions)

include "./library/User.php";
include "./library/JSON.php";


if ($_POST) {

    if ($_POST['action'] == 'new_user') {
        //Create a New User (only)

        $nickname = $_POST['nickname'];
        $password = $_POST['password'];
        $email = $_POST['email'];

        try {

            $User = new User();
            $result = $User->Create_User($nickname, $password, $email);

            if ($result == true) {
                //echo json ok
                $json_data = array('status' => 'success');
                JSON::Deliver_Response(200, $json_data);

            } else {
                //echo json username already taken
                $json_data = array('status' => 'failed', 'message' => 'username already taken');
                JSON::Deliver_Response(200, $json_data);
            }

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo Error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }


    } elseif ($_POST['action'] == 'login') {
       //Login a user. First see if a session for that user is created and have not expired.
       //Change user's state to online if session have not expired

        $nickname = $_POST['nickname'];
        $imei = $_POST['imei'];

        try {
            $User = new User();

            //check first if user exist
            $exist = $User->Check_User_Exist($nickname);

            if($exist == true) {

                $result = $User->Login($nickname, $imei);

                if ($result == true) {
                    //First change user state (to online)
                    $User->Change_User_State($nickname, 'online');

                    //echo json ok
                    $json_data = array('status' => 'success');
                    JSON::Deliver_Response(200, $json_data);

                } else {
                    //echo json need to create a new session, requires password
                    $json_data = array('status' => 'failed', 'message' => 'Required Password, Session Expired');
                    JSON::Deliver_Response(200, $json_data);

                }

            } else {
                //echo json nouser
                $json_data = array('status' => 'nouser');
                JSON::Deliver_Response(200, $json_data);
            }

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo Error database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'offline') {
        //App is close then user state need to change to offline

        $nickname = $_POST['nickname'];

        try {
            $User = new User();
            $User->Change_User_State($nickname, 'offline');

            JSON::Deliver_Response(204, null);

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo Error database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }


    } elseif ($_POST['action'] == 'create_session'){
       //User send password to create a new session
       //Change user state too

        $nickname = $_POST['nickname'];
        $password = $_POST['password'];
        $imei = $_POST['imei'];

        try {
            $User = new User();
            $result = $User->Create_Session($nickname, $password, $imei);

            if ($result) {
                //session successful created and then Change User state too
                $User->Change_User_State($nickname, 'online');

                //echo json ok
                $json_data = array('status' => 'success');
                JSON::Deliver_Response(200, $json_data);
            } else {
                //echo json bad password
                $json_data = array('status' => 'failed', 'message' => 'Bad Password or User');
                JSON::Deliver_Response(200, $json_data);
            }

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'destroy_session'){
        //User wants to log out explicitly

        $nickname = $_POST['nickname'];
        $imei = $_POST['imei'];

        try {
            $User = new User();
            $result = $User->Destroy_Session($nickname, $imei);

            if ($result) {
                //change user state too
                $User->Change_User_State($nickname, 'offline');

                //echo json ok
                $json_data = array('status' => 'success');
                JSON::Deliver_Response(200, $json_data);
            }

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'reset_password') {
        //send an email to the user with a password generated automatically

        $nickname = $_POST['nickname'];
        try {

            $User = new User();
            $json_data = $User->Reset_Password($nickname);

            JSON::Deliver_Response(200, $json_data);

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_POST['action'] == 'change_password') {
        //change current password

        $nickname = $_POST['nickname'];
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        try {

            $User = new User();
            $json_data = $User->Change_Password($nickname, $old_password, $new_password);

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


if ($_GET) {

    if ($_GET['action'] == 'get_online_users') {

        if (isset($_GET['bool_total'])) {
            $bool_total = true;
        } else {
            $bool_total = false;
        }

        if (isset($_GET['limit_start'])) {
            $limit_start = $_GET['limit_start'];
            $limit = $_GET['limit'];
            $bool_pagination = true;
        } else {
            $bool_pagination = false;
        }

        try {
            $User = new User();

            if ($bool_total == true && $bool_pagination == true) {
                $json_data = $User->Get_Online_Users($bool_total, $limit_start, $limit);
            } elseif ($bool_total == false && $bool_pagination == true) {
                $json_data = $User->Get_Online_Users($limit_start, $limit);
            } elseif ($bool_total == true && $bool_pagination == false) {
                $json_data = $User->Get_Online_Users($bool_total);
            } else {
                $json_data = $User->Get_Online_Users();
            }

            JSON::Deliver_Response(200, $json_data);

            $User->Close_Connection();

        } catch (Exception $e) {
            //echo error Database
            $json_data = array('status' => 'crash');
            JSON::Deliver_Response(500, $json_data);
        }

    } elseif ($_GET['action'] == 'get_user_id') {

           $nickname = $_GET['nickname'];
           try {
               $User = new User();
               $json_data = $User->Get_User_id($nickname);

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