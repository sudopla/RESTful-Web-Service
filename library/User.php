<?php
include "ConnectDBi.php";
include "Compute.php";

class User extends ConnectDBi {

    public function __construct(){
        parent::__construct();
    }

    public function Create_User($nickname, $password, $email){

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));
        $password = mysqli_real_escape_string($this->_dbc, trim($password));
        $email = mysqli_real_escape_string($this->_dbc, trim($email));

        // Make sure someone isn't already registered using this username
        $query = "SELECT * FROM Users WHERE nickname = '$nickname'";
        $data = $this->Execute($query);
        if (mysqli_num_rows($data) == 0) {
            // The username is unique, so insert the data into the database
            $query = "INSERT INTO Users (nickname, password, email) VALUES ('$nickname',SHA('$password'),'$email')";
            $result = $this->Execute($query);
        } else {
            $result = false;
        }

        return $result;

    }

    public function Login($nickname, $imei){
        //See if user's session have not expired
        //Update time_stamp too

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));
        $imei = mysqli_real_escape_string($this->_dbc, trim($imei));

        $query = "SELECT time_stamp FROM Sessions WHERE imei = '$imei' AND user_id IN (SELECT user_id FROM Users WHERE nickname = '$nickname')";

        $data = $this->Execute($query);

        if (mysqli_num_rows($data) == 1) {
            //See if time_stamp have not expired

            $data = mysqli_fetch_array($data, MYSQL_ASSOC);

            $result = Compute::calculate_time_session($data['time_stamp']);

            if ($result <= 30){
                //Refresh time_stamp first
                $date = date( 'Y-m-d');
                $query = "UPDATE Sessions set time_stamp = '$date' WHERE imei = '$imei' AND user_id IN (SELECT user_id FROM Users WHERE nickname = '$nickname')";
                $this->Execute($query);

                return true;

            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    public function Check_User_Exist($nickname) {
        //Check if the user exist
        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));

        $query = "SELECT * FROM Users WHERE nickname = '$nickname'";
        $data = $this->Execute($query);
        if (mysqli_num_rows($data) != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function Change_User_State($nickname, $state){

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));

        $query = "UPDATE Users set state = '$state' WHERE nickname = '$nickname'";
        $result = $this->Execute($query);

        return $result;
    }

    public function Create_Session($nickname, $password, $imei){

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));
        $password = mysqli_real_escape_string($this->_dbc, trim($password));
        $imei = mysqli_real_escape_string($this->_dbc, trim($imei));

        $date = date('Y-m-d');

        //Check Password and select user_id
        $query = "SELECT user_id FROM Users WHERE nickname = '$nickname' AND password = SHA('$password')";
        $data = $this->Execute($query);

        if (mysqli_num_rows($data) == 1) {
            //Correct password

            $data = mysqli_fetch_array($data, MYSQL_ASSOC);
            $user_id = $data['user_id'];

            //First see if a session (username-imei pair) have been create before
            //In that case just update time_stamp value
            $query = "SELECT id_session FROM Sessions WHERE user_id = '$user_id' AND imei = '$imei'";
            $data = $this->Execute($query);

            if (mysqli_num_rows($data) == 1) {
                //update time_stamp
                $data = mysqli_fetch_array($data, MYSQL_ASSOC);
                $id_session = $data['id_session'];
                $query = "UPDATE Sessions set time_stamp = '$date' WHERE user_id = '$user_id'";
                $this->Execute($query);

                return true;

            } else {
                //not a session before, then create a new one
                $query = "INSERT INTO Sessions (user_id, imei, time_stamp) VALUES ('$user_id', '$imei', '$date')";
                $this->Execute($query);

                return true;
            }

        } else {
            //bad password
            return false;
        }

    }

    public function Destroy_Session($nickname, $imei){

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));
        $imei = mysqli_real_escape_string($this->_dbc, trim($imei));

        //Look for user_id
        $query = "SELECT user_id FROM Users WHERE nickname = '$nickname'";
        $data = $this->Execute($query);
        $data = mysqli_fetch_array($data, MYSQL_ASSOC);
        $user_id = $data['user_id'];

        $query = "SELECT id_session FROM Sessions WHERE user_id = '$user_id' AND imei = '$imei'";
        $data = $this->Execute($query);
        $data = mysqli_fetch_array($data, MYSQL_ASSOC);
        $id_session = $data['id_session'];

        $query = "DELETE FROM Sessions WHERE id_session = '$id_session'";
        $this->Execute($query);

        return true;
    }

    public function Reset_Password($nickname){

        require_once "class.phpmailer.php";
        require_once "class.smtp.php"; //its included in phpmailer but just in case fail

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));

        $query = "SELECT user_id, email FROM Users WHERE nickname = '$nickname'";
        $data = $this->Execute($query);
        $data = mysqli_fetch_array($data, MYSQL_ASSOC);
        $email = $data['email'];
        $user_id = $data['user_id'];

        //Generate password
        $password = $this->Generate_Password();

        //Send the email
        $mail = new PHPMailer;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.server.com';  // Specify SMTP server
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'username';                 // SMTP username
        $mail->Password = 'passwd';                           // SMTP password
        //$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        //$mail->Port = 587;                                    // TCP port to connect to
        //$mail->From = 'email_address';
        $mail->From = 'user@domain.com';
        $mail->FromName = 'App';
        $mail->addAddress($email);
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'New password for App';
        $mail->Body = 'For user '.$nickname.' the new password is: '.$password;
        $mail->AltBody = 'For user '.$nickname.' the new password is: '.$password;

        if(!$mail->send()) {
            return array('status' => 'failed', 'message' => $mail->ErrorInfo);
        } else {
            //Message sent successfully
            //Reset password in database too
            $query = "UPDATE Users set password = SHA('$password') WHERE user_id = '$user_id'";
            $this->Execute($query);

            return array('status' => 'success');
        }

        //Reset password in database too
        $query = "UPDATE Users set password = SHA('$password') WHERE user_id = '$user_id'";
        $this->Execute($query);
    }

    public function Change_Password($nickname, $old_password, $new_password){

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));
        $old_password = mysqli_real_escape_string($this->_dbc, trim($old_password));
        $new_password = mysqli_real_escape_string($this->_dbc, trim($new_password));

        //First check that old_password is OK
        //Check Password and select user_id
        $query = "SELECT user_id FROM Users WHERE nickname = '$nickname' AND password = SHA('$old_password')";
        $data = $this->Execute($query);

        if (mysqli_num_rows($data) == 1) {
            //Correct old_password

            $data = mysqli_fetch_array($data, MYSQL_ASSOC);
            $user_id = $data['user_id'];

            $query = "UPDATE Users set password = SHA('$new_password') WHERE user_id = '$user_id'";
            $this->Execute($query);

            return array('status' => 'success');

        } else {
            return array('status' => 'failed', 'message' => 'Wrong old password');
        }
    }

    public function Get_Online_Users(){

        $args = func_get_args();

        if (count($args) == 1 or count($args) == 0) {

            if (count($args) == 1) {
                $bool_total = true;
            } else {
                $bool_total = false;
            }

            $query = "SELECT user_id, nickname FROM Users WHERE state = 'online'";
        }

        if (count($args) == 2 or count($args) == 3) {
            if (count($args) == 2) {
                $bool_total = false;
                $limit_start = $args[0];
                $limit = $args[1];
            } else {
                $bool_total = true;
                $limit_start = $args[1];
                $limit = $args[2];
            }

            $limit_start = mysqli_real_escape_string($this->_dbc, trim($limit_start));
            $limit = mysqli_real_escape_string($this->_dbc, trim($limit));

            $query = "SELECT user_id, nickname FROM Users WHERE state = 'online' LIMIT $limit_start, $limit ";
        }


        //see if it's asked to calculate total of online users
        if ($bool_total == true) {
            $query_t = "SELECT COUNT(*) AS total from Users WHERE state = 'online'";
            $result = $this->Execute($query_t);
            $total = mysqli_fetch_array($result, MYSQL_ASSOC);
            $total = $total['total'];
        } else {
            $total = false;
        }

        $data = $this->Execute($query);

        $online_users = array();

        while ($row = mysqli_fetch_array($data, MYSQL_ASSOC)) {
            array_push($online_users, array('user_id' => $row['user_id'], 'nickname' => $row['nickname']));
        }

        if($bool_total == true){
            return array('total' => $total, 'online_users' => $online_users);
        } else {
            return array('online_users' => $online_users);
        }
    }

    public function Find_Players($player){

        $player = mysqli_real_escape_string($this->_dbc, trim($player));

        $query = "SELECT user_id, nickname, state FROM Users WHERE nickname LIKE '%$player%'";
        $data = $this->Execute($query);

        $players = array();

        while ($row = mysqli_fetch_array($data, MYSQL_ASSOC)) {
            array_push($players, array('user_id' => $row['user_id'], 'nickname' => $row['nickname'], 'state' => $row['state']));
        }

        return array('players' => $players);
    }

    public function Get_Top_Scores(){

        $query_1 = "SELECT id_user_1, id_user_2, score FROM Score_2_minut WHERE status = 'Finished' GROUP BY score DESC LIMIT 0, 3";
        $query_2 = "SELECT id_user_1, id_user_2, score FROM Score_no_time WHERE status = 'Finished' GROUP BY score DESC LIMIT 0, 3";

        $data_1 = $this->Execute($query_1);
        $data_2 = $this->Execute($query_2);

        $scores_2_minut = array();
        $scores_no_limit = array();

        while ($row = mysqli_fetch_array($data_1, MYSQL_ASSOC)) {
            
            $nickname_1 = $this->Get_Nickname($row['id_user_1']);
            $nickname_2 = $this->Get_Nickname($row['id_user_2']);

            array_push($scores_2_minut, array('nickname_1' => $nickname_1, 'nickname_2' => $nickname_2, 'score' => $row['score']));
        }

        while ($row = mysqli_fetch_array($data_2, MYSQL_ASSOC)) {

            $nickname_1 = $this->Get_Nickname($row['id_user_1']);
            $nickname_2 = $this->Get_Nickname($row['id_user_2']);

            array_push($scores_no_limit, array('nickname_1' => $nickname_1, 'nickname_2' => $nickname_2, 'score' => $row['score']));
        }

        return array('scores_2_minut' => $scores_2_minut, 'score_no_limit' => $scores_no_limit);

    }

    public function Get_User_id($nickname){

        $nickname = mysqli_real_escape_string($this->_dbc, trim($nickname));

        $query = "SELECT user_id FROM Users WHERE nickname = '$nickname'";
        $data = $this->Execute($query);

        $row = mysqli_fetch_array($data, MYSQL_ASSOC);
        $user_id = $row['user_id'];

        return array('user_id' => $user_id);
    }

    public function Get_Nickname($id_user){

        $query = "SELECT nickname FROM Users WHERE user_id = '$id_user'";
        $result = $this->Execute($query);
        $row = mysqli_fetch_array($result, MYSQL_ASSOC);
        $nickname = $row['nickname'];

        return $nickname;
    }

    private function Generate_Password() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789*-+.?#!";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

} 