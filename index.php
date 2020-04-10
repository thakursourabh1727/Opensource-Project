<?php
require 'db.php';
$msg = [];
$status = 400;
$action = filter_input(INPUT_GET, "action", FILTER_DEFAULT);
if($action=="register"){
    $name = filter_input(INPUT_POST, "name", FILTER_DEFAULT);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
    
    try{
        $db = new db();
        $query = $db->connection()->prepare("INSERT INTO user (name, email, password) VALUES (:name, :email, :password)");
        $query->bindValue(":name", $name);
        $query->bindValue(":email", $email);
        $query->bindValue(":password", md5($password));
        if($query->execute()){
            $msg[] = "Registration Successful.";
            $status = 200;
        }else{
            $msg[] = "Registration Failed.";
        }
    } catch (Exception $ex) {
        $msg[] = "There's an error: {$ex->getMessage()}";
    }
}

if($action == "login"){
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_DEFAULT);
    
    try{
        $db = new db();
        $query = $db->connection()->prepare("SELECT userId FROM user WHERE email=:email AND password=:pwd");
        $query->bindValue(":email", $email);
        $query->bindValue(":pwd", md5($password));
        $query->execute();
        $data = $query->fetch(\PDO::FETCH_ASSOC);
        if($data){
            $_SESSION["userId"] = $data["userId"];
            header("Location: ./dashboard.php");
            exit();
        }else{
            $msg[] = "Invalid Email/Password!";
        }
        
    } catch (Exception $ex) {
        $msg[] = "There's an error: {$ex->getMessage()}";
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>Job Portal!</title>
  </head>
  <body>
    <div class="container">
        <div class='row mt-5 mb-5'>
            <?php
            foreach($msg as $m){
                echo "<div class='alert alert-".(($status==400) ? "danger":"success")."'>{$m}</div>";
            }
            ?>
        </div>
        <div class="row">
            <div class="col-12 mb-5 mt-5">
                <h1 align="center">Job Portal</h1>
            </div>
            <div class="col shadow-m p-3 m-2 bg-white rounded">
                <h1>Register</h1>
                <form action="?action=register" method='POST'>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class='form-control'/>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class='form-control'/>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class='form-control'/>
                    </div>
                    <input type="submit" class='btn btn-primary' value='Register'/>
                </form>
            </div><div class="col shadow-m p-3 m-2 bg-white rounded">
                <h1>Login</h1>
                <form action="?action=login" method='POST'>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class='form-control'/>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class='form-control'/>
                    </div>
                    <input type="submit" class='btn btn-primary' value='Login'/>
                </form>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>
