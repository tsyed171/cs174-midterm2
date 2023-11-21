<?php
session_start();

define("INVALID_LOGIN", "Invalid username/password combination");

require_once 'db-login.php'; //sql credentials

    echo <<<_END
        <html>
        <head>
        <link rel="stylesheet" href="css/login.css">
        <title>CS174 Midterm2</title>
        </head>
        
        <body>
        
        <form method="POST" action="signin.php" enctype="multipart/form-data"> 
        <h2>Hello!</h2>
    
        <h3>Login</h3>
    
        <label for="login_user"> Username: </label>
        <input type="text" name="login_user"><br><br>
    
        <label for="login_pass"> Password: </label>
        <input type="text" name="login_pass"><br>
    
        <p><input type="submit" name="submit_login" value="Submit"></p>
    
        <h3>Sign Up</h3>
    
        <label for="signup_name"> Name: </label>
        <input type="text" name="signup_name"><br><br>
        
        <label for="signup_user"> Username: </label>
        <input type="text" name="signup_user"><br><br>
        
        <label for="signup_pass"> Password: </label>
        <input type="text" name="signup_pass"><br>
        
        <p><input type="submit" name="submit_signup" value="Submit"></p>
        
        </form>
    _END;
    
    echo "</body></html>";

    //if user attempted to sign up
    if(isset($_POST['submit_signup']) && !empty($_POST['signup_name']) && !empty($_POST['signup_user']) && !empty($_POST['signup_pass'])){
        //open db connection
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli($hn,$un,$pw,$db);
        if($conn->connect_error) die (mysql_fatal_error());   

        //sanitize 
        $temp_name = mysql_entities_fix_string($conn, $_POST['signup_name']);  
        $temp_user = mysql_entities_fix_string($conn, $_POST['signup_user']);
        $temp_pass = mysql_entities_fix_string($conn, $_POST['signup_pass']);
             
        //make sure username is unique
        if(unique_user($conn, $temp_user)){
           $token = password_hash($temp_pass, PASSWORD_DEFAULT); //encrypt password
            add_user($conn, $temp_name, $temp_user, $token); //add new user
        } else {
            echo "Username taken; Choose another username<br>";
        }

        //close all connections
        $result->close();
        $conn->close();   
    }
    
    //if user attempts to login
    if (isset($_POST['submit_login']) && !empty($_POST['login_user']) && !empty($_POST['login_pass'])) {
         //open db connection
         mysqli_report(MYSQLI_REPORT_OFF);
         $conn = @new mysqli($hn,$un,$pw,$db);
         if($conn->connect_error) die (mysql_fatal_error());    

        //sanitize
        $temp_user = mysql_entities_fix_string($conn, $_POST['login_user']);
        $temp_pass = mysql_entities_fix_string($conn, $_POST['login_pass']);
    
        $query = "SELECT * FROM cs174_mid2_credentials WHERE username='$temp_user'";
        $result = $conn->query($query);
        
        if (!$result) { mysql_fatal_error();} 
        elseif ($result->num_rows){
            $row = $result->fetch_array(MYSQLI_NUM);
            
            //close connection
            $result->close();
            $conn->close();
           
            //verify password 
            if(password_verify($temp_pass, $row[3])){
                //set sessions
                $_SESSION['id'] = $row[0];
                $_SESSION['name'] = $row[1];
                //session regeneration
                if(!isset($_SESSION['initiated'])){
                    session_regenerate_id();
                    $_SESSION['initiated'] = 1;
                }   
                header("Location: landing.php");
            } else{
                echo INVALID_LOGIN . "<br>";
            }            
        } else {
            echo INVALID_LOGIN . "<br>";
            //close connection
            $result->close();
            $conn->close();
        }
    }
    
    //check for unique username
    function unique_user($conn, $user){
        $query = "SELECT * FROM cs174_mid2_credentials WHERE username='$user'";
        $result = $conn->query($query);
        
        if (!$result) { 
            mysql_fatal_error();
        } 
        else{ //if row has content, username is taken
            if($result->num_rows == 0) {return true;} 
            return false;
        }
    }

    //add user
    function add_user($conn, $name, $un, $pw){
        $query = "INSERT INTO cs174_mid2_credentials(name, username, password) VALUES('$name', '$un', '$pw')";
        $result = $conn->query($query);
        if (!$result) {mysql_fatal_error();}
    }
    
    //sql error func
    function mysql_fatal_error(){
        echo "Sorry, connection fail<br>";
    }

    //sanitize input
    function mysql_entities_fix_string($conn,$string){
        return htmlentities(mysql_fix_string($conn,$string));
    }

    //sanitize input: helper func
    function mysql_fix_string($conn,$string){
        $string = stripslashes($string);
        return $conn->real_escape_string($string);
    }

    //clear session
    function destroy_session_and_data(){
        $_SESSION = array();
        setcookie(session_name(), '', time() - 2592000, '/');
        session_destroy();
    }

?>

