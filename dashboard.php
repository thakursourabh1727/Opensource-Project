<?php
require 'db.php';
$msg = [];
$status = 400;
$action = filter_input(INPUT_GET, "action", FILTER_DEFAULT);
$db = new db();
if($action=='search'){
    $q = filter_input(INPUT_POST, "q");
    $query = $db->connection()->prepare("SELECT * FROM skills WHERE skillId NOT IN (SELECT skillId FROM user_skill WHERE userId=:userId) AND title LIKE :q");
    $query->bindValue(":userId", $_SESSION["userId"]);
    $query->bindValue(":q", "%{$q}%");
    $query->execute();
    $data = ["status"=>200, "data"=>[]];
    while($row = $query->fetch()){
        $data["data"][] = ["title"=>$row["title"],"id"=>$row["skillId"]];
    }
    echo json_encode($data);
    exit();
}
if($action=="delete"){
    $id = filter_input(INPUT_GET, "id");
    $query = $db->connection()->prepare("DELETE FROM user_skill WHERE user_skill_id=:id");
    $query->bindValue(":id", $id);
    $query->execute();
}
if($action=="add"){
    $id = filter_input(INPUT_POST, "id");
    $query = $db->connection()->prepare("INSERT INTO user_skill (skillId, userId) VALUES (:skillId, :userId)");
    $query->bindValue(":skillId", $id);
    $query->bindValue(":userId", $_SESSION["userId"]);
    if($query->execute()){
        echo $query->lastInsertId();        
    }

    exit();
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
            <div class="col" id="jobs">

                <h1>Jobs for you!</h1>
                <table class="table table-striped">
                    <thead class="thead-dark"><th>Title</th><th>Description</th></thead>
                    <tbody>
                    <?php
$sql = <<<SQL
SELECT

*

FROM 
job 
WHERE
EXISTS (
    SELECT 
        job_skills.skillId 
    FROM job_skills 
    WHERE 
        job_skills.jobId=job.jobId 
        AND 
        EXISTS
        (
            SELECT 
                user_skill.skillId 
            FROM 
                user_skill 
            WHERE 
                user_skill.userId = :userId
                AND
                user_skill.skillId=job_skills.skillId
        )
    );

SQL;
                    $query = $db->connection()->prepare($sql);
                    $query->bindValue(":userId", $_SESSION["userId"]);
                    $query->execute();
                    while($row = $query->fetch()){
                        echo "<tr><td>{$row["title"]}</td><td>{$row["description"]}</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col shadow-m p-3 m-2 bg-white rounded">
                <h1>My Skills</h1>
                <form action="JavaScript:void(0);" class="mb-3" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="" onkeyup="search_skills(this.value)" placeholder="Skill">
                    </div>
                </form>
                <div id="skill_list"></div>
                <table class="table table-striped">
                    <thead class="thead-dark"><tr><th>Skill</th><th>Action</th></tr></thead>
                    <tbody id="skills">
                    <?php
                        $query = $db->connection()->prepare("SELECT user_skill_id, title  FROM  skills LEFT JOIN user_skill ON user_skill.skillId=skills.skillId WHERE user_skill.userId = :userId");
                        $query->bindValue(":userId", $_SESSION["userId"]);
                        $query->execute();
                        while($row = $query->fetch()){
                            echo "<tr><td>{$row["title"]}</td><td><a href='?action=delete&id={$row["user_skill_id"]}'>Delete</a></td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <script>
        function search_skills(v){
            $.ajax({
                type: "POST",
                data: {q:v},
                url: "?action=search",
                dataType: "JSON",
                success: function(d){
                    var html = "<table class='table'>";
                    if(d.status==200){
                        $.each(d.data, function(key,value){
                            html += "<tr><td>"+value.title+"</td><td><a href='javascript:void(0);' onclick='add(\""+value.title+"\","+value.id+"); return false;'>Add</a></td></tr>";
                        });
                    }
                    html += "</table>";
                    document.getElementById("skill_list").innerHTML = html;
                }
            });
        }
        function add(title,id){
            $("#skill_list").empty();
            $.ajax({
                type: "POST", 
                url: "?action=add",
                data: {id: id},
                success: function (d){
                    if(d){
                        $("#skills").append("<tr><td>"+title+"</td><td><a href='?action=delete&id="+id+"'>Delete</a></td></tr>");
                        $( "#jobs" ).load( "dashboard.php #jobs" );

                    }
                }
            });
        }
    </script>
  </body>
</html>
