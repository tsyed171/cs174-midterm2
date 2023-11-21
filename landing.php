<?php
session_start();

define("COLUMN_SIZE", 3);
define("START_SUBSTR", 0);
define("END_SUBSTR", 100);
define("TIME_COOKIE", 2592000);

require_once 'db-login.php'; //sql credentials

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($hn,$un,$pw,$db);
if($conn->connect_error) die (mysql_fatal_error()); 

//when logged in, show landing page
if (isset($_SESSION['name']) && isset($_SESSION['id'])){
    //sanitize
    $id = mysql_entities_fix_string($conn, $_SESSION['id']);
    $name = mysql_entities_fix_string($conn, $_SESSION['name']);

   // echo "Hello, $name!<br>"; //welcome user

    // Display input form
    echo <<<_END
    <html>
    <head><title>CS174 Mid2 Homepage</title></head><body>
    
    <form method="POST" action="signin.php" enctype="multipart/form-data"> 
    <p><input type="submit" name="logout" value="Log Out"></p>
    </form>


    <form method="POST" action="landing.php" enctype="multipart/form-data"> 
    <h3>Hello, $name!</h3>

    <label for="textname"> Thread Name: </label>
    <input type="text" name="textname"><br>

    <label for="filename"> File Content: </label>
    <input type="file" name="filename"><br><br>

    <p><input type="submit" name="submit" value="Submit"></p>

    <p><input type="submit" name="expand" value="Expand"></p>

    </form>
    _END;

    //if file submitted, sanitize and add to table
    if ($_FILES && isset($_POST['submit'])) {

        //file exists
       $name = mysql_entities_fix_string($_FILES['filename']['tmp_name']);   
        
       if(!is_uploaded_file($name) ){
           echo "No file uploaded! Please upload.";
       }
       else if(empty($name)){
           echo "File is empty! Please use file with values.";
       } 
       else {
           //restrict filetype to text
           $file_type = $_FILES['filename']['type'];
           if($file_type != 'text/plain'){
               //restrict to only text file using FILE
               echo "Only text files accepted! Try again.";
           }
           else {
               $thread = mysql_entities_fix_string($conn,$_POST['textname']);
               if(empty($thread)){
                   echo "Title is necessary! Try again.<br>";
               }
               else {
                   //open file to get line
                   $fh = fopen($name, 'r') or die("File does not exist or you lack permission to open it");
                   $line = mysql_entities_fix_string(fread($fh, filesize($name)));
   
                   //add data into table; title and file line
                   $query = "INSERT INTO cs174_mid2_content VALUES ('$id','$thread','$line')";
                   $result = $conn->query($query);
                   if (!$result) {mysql_fatal_error();}
   
                   fclose($fh); 
               }    
           } 
       }
   }

   
   if(isset($_POST['expand'])){
        //make query
        $query = "SELECT * FROM cs174_mid2_content WHERE id = '$id'";
        $result = $conn->query($query);
        if (!$result) {mysql_fatal_error();}
    
        //print out
        $rows = $result->num_rows;
        echo "<table><tr><th>Thread Name</th><th>File Content</th></tr>";
        for ($j = 0 ; $j < $rows ; ++$j){
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);
            echo "<tr>";
            for ($k = 1 ; $k < COLUMN_SIZE; ++$k){
                echo "<td><br>$row[$k]</td>";
            } 
            echo "</tr>";
        }
        echo "</table>";
   } else {
        //print content
        print_content($conn, $id);
   }

    echo"</body></html>";

    //close all connections
    $result->close();
    $conn->close();

} 
else { 
    echo "Please <a href='signin.php'>Click Here</a> to log in.";
    exit();
}

//logout pressed
if(isset($_POST['logout'])){
    destroy_session_and_data();
    header("Location: signin.php");
}

function print_content($conn, $id) {
     //make query
     $query = "SELECT * FROM cs174_mid2_content WHERE id = '$id'";
     $result = $conn->query($query);
     if (!$result) {mysql_fatal_error();}
 
     //print out
     $rows = $result->num_rows;
     echo "<table><tr><th>Thread Name</th><th>File Content</th></tr>";
     for ($j = 0 ; $j < $rows ; ++$j){
         $result->data_seek($j);
         $row = $result->fetch_array(MYSQLI_NUM);  
         echo "<tr>";
         for ($k = 1 ; $k < COLUMN_SIZE; ++$k){
            //check for 100 char limit and make sur its a whole word
            if($k == 2) { $row[$k] = substr($row[$k], START_SUBSTR , END_SUBSTR);}
            echo "<td><br>$row[$k]</td>";
        } 
         echo "</tr>";
     }
     echo "</table>";
     
}

//clear session
function destroy_session_and_data() {
    $_SESSION = array();
    setcookie(session_name(), '', time() - TIME_COOKIE, '/');
    session_destroy();
}

//sanitize input
function mysql_entities_fix_string($conn,$string){
    return htmlentities(mysql_fix_string($conn,$string));
}

//sanitize input: helper func
function mysql_fix_string($conn,$string){
   //$string = stripslashes($string);
    return $conn->real_escape_string($string);
}

?>