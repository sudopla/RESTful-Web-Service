<?php
include "User.php";
//Don't need to include ConnectDBi and Compute classes 'cause User.php includes them

class Game extends ConnectDBi {

    public function __construct(){
        parent::__construct();
    }

    public function Request_to_Play($user_id, $request_user_id, $type_game) {

        $user_id = mysqli_real_escape_string($this->_dbc, trim($user_id));
        $request_user_id = mysqli_real_escape_string($this->_dbc, trim($request_user_id));
        $type_game = mysqli_real_escape_string($this->_dbc, trim($type_game));


        $query = "INSERT INTO Request_Play (user_id, request_user_id, type_game) VALUES ('$user_id', '$request_user_id', '$type_game')";
        $id_request = $this->Insert($query);

        return array('id_request' => $id_request);
    }


    public function Get_Request_Play($user_id){
        //See if any player wants to play with user_id

        $user_id = mysqli_real_escape_string($this->_dbc, trim($user_id));

        $query = "SELECT id_request, user_id, type_game FROM Request_Play WHERE request_user_id = '$user_id'";
        //Don't confuse: the user_id return in this select is the one of the user that want to play with him
        $data = $this->Execute($query);

        if(mysqli_num_rows($data) != 0) {
            //process requests
            $requests = array();

            while ($row = mysqli_fetch_array($data, MYSQL_ASSOC)) {
                //get_nickname too. Used get_nickname function of User class
                $User = new User();
                $nickname = $User->Get_Nickname($row['user_id']);

                array_push($requests, array('id_request' => $row['id_request'], 'nickname' => $nickname, 'type_game' => $row['type_game']));
            }

            return array('result' => 'true', 'requests' => $requests);

        } else {
            return array('result' => 'false');
        }

    }

    public function Accept_Request($id_request) {
        //Accept a request to play

        $id_request = mysqli_real_escape_string($this->_dbc, trim($id_request));

        //Change status to 'accepted' in Request_Play Table
        $query = "UPDATE Request_Play set status = 'accepted' WHERE id_request = '$id_request'";
        $this->Execute($query);

        $query = "SELECT user_id, request_user_id, type_game FROM Request_Play WHERE id_request = '$id_request'";
        $data= $this->Execute($query);

        $row =  mysqli_fetch_array($data, MYSQL_ASSOC);
        $user_1_id = $row['user_id'];
        $user_2_id = $row['request_user_id'];
        $type_game = $row['type_game'];

        $date = date( 'Y-m-d');

        //Create an entry in Score_Table depending of type of game
        if($type_game == 1) {

            $query = "INSERT INTO Score_2_minut (id_user_1, id_user_2, date, status) VALUES ('$user_1_id', '$user_2_id', '$date', 'Playing')";
            $id_play = $this->Insert($query);

        } elseif ($type_game == 2) {

            $query = "INSERT INTO Score_no_time (id_user_1, id_user_2, date, status) VALUES ('$user_1_id', '$user_2_id', '$date', 'Playing')";
            $id_play = $this->Insert($query);

        }

        //This is done to find id_play easier and faster when a user read the accepted status
        $query = "UPDATE Request_Play set id_play = '$id_play' WHERE id_request = '$id_request'";
        $this->Execute($query);

        //Update user's state to busy
        $this->Change_User_State_by_id($user_2_id, 'busy');

        return array('id_play' => $id_play);

    }

    public function See_If_Accepted($id_request) {
        //Function to see if a request to play was accepted

        $id_request = mysqli_real_escape_string($this->_dbc, trim($id_request));

        $query = "SELECT user_id, id_play FROM Request_Play WHERE id_request = '$id_request' AND status = 'accepted'";
        $data = $this->Execute($query);

        if (mysqli_num_rows($data) != 0) {
            //accepted
            $row = mysqli_fetch_array($data, MYSQL_ASSOC);
            $user_id = $row['user_id'];
            $id_play = $row['id_play'];

            $this->Change_User_State_by_id($user_id, 'busy');

            //Delete Request
            $this->Delete_Request($id_request);

            return array('accepted' => 'yes', 'id_play' => $id_play);

        } else {
            //no accepted
            return array('accepted' => 'no');
        }

    }

    public function Delete_Request($id_request){

        $id_request = mysqli_real_escape_string($this->_dbc, trim($id_request));

        $query = "DELETE FROM Request_Play WHERE id_request = '$id_request'";
        $this->Execute($query);

        return true;

    }

    public function Change_User_State_by_id($user_id, $state) {

        $query = "UPDATE Users set state = '$state' WHERE user_id = '$user_id'";
        $result = $this->Execute($query);

        return $result;
    }

    public function Check_Counter($id_play, $type_game) {
        //Check counter to see if one of the two user didn't quit the game

        $query = "SELECT intents FROM Counter WHERE id_play = '$id_play'";
        $data = $this->Execute($query);
        $row = mysqli_fetch_array($data, MYSQL_ASSOC);
        $intents = $row['intents'];

        if($intents <= 10) {
            return false;
        } else {
            //One of the user lost connection or app crashed

            //Delete those vectors that still stand in Vector_Temp Table
            $this->Delete_Remaining_Vectors($id_play);

            //Delete entry in Scores Table because the play will not finish
            $this->Delete_Entry_Score($id_play, $type_game);

            //Delete Counter used during the play
            $this->Delete_Counter($id_play);

            return true;
        }

    }

    public function Get_Score($id_play, $type_game){
        //Get the current score of the game

        if($type_game == 1) {
            $query = "SELECT score FROM Score_2_minut WHERE id_play = '$id_play'";
        } elseif($type_game == 2) {
            $query = "SELECT score FROM Score_no_time WHERE id_play = '$id_play'";
        }

        $data = $this->Execute($query);
        $row = mysqli_fetch_array($data, MYSQL_ASSOC);
        $score = $row['score'];

        return $score;

    }

    public function Process_Vector($id_play, $type_game, $num_vector, $vector_2) {
        //function to process a received vector

        //Check in Temp_Vector Tables if there is the pair's vector
        $query = "SELECT vector FROM Vector_Temp WHERE id_play = '$id_play' AND num_vector = '$num_vector'";
        $data = $this->Execute($query);

        if(mysqli_num_rows($data) != 0) {
            //Compare vectors
            $row = mysqli_fetch_array($data, MYSQL_ASSOC);
            $save_vector = $row['vector'];
            $vector_1 = explode(',', $save_vector);
            $count = count($vector_2); //For now 5 values in each vector
            $score_ratio = 10; //For now a match sum 10 point
            $score = 0;

            //See if there are matches
            for ($i = 0; $i < $count; $i++) {
                if($vector_1[$i] == $vector_2[$i]) {
                    $score += $score_ratio;
                }
            }

            //Add score
            if($type_game == 1) {
                $query = "UPDATE Score_2_minut set score = score + '$score' WHERE id_play = '$id_play'";
            } elseif($type_game == 2) {
                $query = "UPDATE Score_no_minut set score = score + '$score' WHERE id_play = '$id_play'";
            }
            $this->Execute($query);

            //Reset Counter
            $query = "UPDATE Counter set intents = 0 WHERE id_play = '$id_play'";
            $this->Execute($query);

            //Delete te pair of vectors 'cause they were already checked
            $query = "DELETE FROM Vector_Temp WHERE id_play = '$id_play' AND  num_vector = '$num_vector'";
            $this->Execute($query);

        } else {
            //Save vector 'cause his pair isn't store yet
            $to_save_vector = $vector_2[0].','.$vector_2[1].','.$vector_2[2].','.$vector_2[3].','.$vector_2[4];
            $query = "INSERT INTO Vector_Temp (id_play, num_vector, vector) VALUES ('$id_play', '$num_vector', '$to_save_vector')";
            $this->Execute($query);

            //Increment Counter
            if($num_vector == 1) {
                $query = "INSERT INTO Counter (id_play, intents) VALUES ('$id_play', 0)";
            } else {
                $query = "UPDATE Counter set intents = intents + 1 WHERE id_play = '$id_play'";
            }
                 $this->Execute($query);
        }

    }

    public function Finish_Play($id_play, $type_game) {
        //When the time of the play is finished or if it's a not time game a player want to stop

        //Delete those vectors that stayed in Vector_Temp Table
        $this->Delete_Remaining_Vectors($id_play);

        //Change status in Score Table to Finished
        $this->Change_Status($id_play, $type_game, 'Finished');

        //Delete the counter used during the play
        $this->Delete_Counter($id_play);
    }

    public function Abandon_Play($id_play, $type_game){
        //A player explicitly close that play

        //Change status to Abandoned
        $this->Change_Status($id_play, $type_game, 'Abandoned');

        //Delete those vectors that still stand in Vector_Temp Table
        $this->Delete_Remaining_Vectors($id_play);

        //Delete the counter used during the play
        $this->Delete_Counter($id_play);
    }

    public function Check_Status($status_to_compare, $type_game, $id_play){
        //Check the column status in Scores Tables

        if($type_game == 1) {
            $query = "SELECT status FROM Score_2_minut WHERE id_play = '$id_play'";
        } else {
            $query = "SELECT status FROM Score_no_time WHERE id_play = '$id_play'";
        }

        $data = $this->Execute($query);
        $row = mysqli_fetch_array($data, MYSQL_ASSOC);
        $status = $row['status'];

        if($status_to_compare == $status) {
            return true;
        } else {
            return false;
        }

    }

    public function Delete_Entry_Score($id_play, $type_game) {
        //Delete entry in score Table because the play didn't finish

        if($type_game == 1) {
            $query = "DELETE FROM Score_2_minut WHERE id_play = '$id_play'";
        } elseif($type_game == 2) {
            $query = "DELETE FROM Score_no_time WHERE id_play = '$id_play'";
        }
        $this->Execute($query);
    }

    public function Clean_Variable($variable){

        $variable = mysqli_real_escape_string($this->_dbc, trim($variable));
        return $variable;
    }

    public function Clean_Variable_Array($array){

        for($i = 0; $i < count($array); $i++) {
            $array[$i] = mysqli_real_escape_string($this->_dbc, trim($array[$i]));
        }
        return $array;
    }

    public function Delete_Remaining_Vectors($id_play){
        //Delete those vectors that still stand in Vector_Temp Table

        $query = "DELETE FROM Vector_Temp WHERE id_play = '$id_play'";
        $this->Execute($query);
    }

    public function Delete_Counter($id_play){
        //Delete the counter used during the play

        $query = "DELETE FROM Counter WHERE id_play = '$id_play'";
        $this->Execute($query);
    }

    //PRIVATES FUNCTIONS

    private function Change_Status($id_play, $type_game, $status) {
        //Change status in Score Table

        if($type_game == 1) {
            $query = "UPDATE Score_2_minut set status = '$status' WHERE id_play = '$id_play'";
        } elseif($type_game == 2) {
            $query = "UPDATE Score_no_time set status = '$status' WHERE id_play = '$id_play'";
        }
        $this->Execute($query);
    }

}