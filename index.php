<?php
session_start();
require_once "pdo.php";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maxim Rosin - Resume Registry</title>

<?php require_once "bootstrap.php"; ?>
    <script
        src="https://code.jquery.com/jquery-3.2.1.js"
        integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
        crossorigin="anonymous">
    </script>
</head>
<body>
<div class="container">
<h1>Maxim Rosin's Resume Registry</h1>

<?php
$smth = $pdo->query("SELECT profile_id, user_id, first_name, last_name, email, headline, summary FROM profile");
if ( isset($_SESSION['name']) ) {
    if ( isset($_SESSION['success']) ) {
        echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
        unset($_SESSION['success']);
    }
    echo ('<div><a href="logout.php">Logout</a></div>');
    if ($smth->rowCount() == 0) {
        echo ('<p>No rows found</p>');
    } else {
        echo ('<table border = "1">'."\n");
        echo ("<tr><th>");
        echo ("Name");
        echo ("</th><th>");
        echo ("Headline");
        echo ("</th><th>");
        echo ("Action");
        echo ("</th></tr>");
        while ($row = $smth->fetch(PDO::FETCH_ASSOC)) {
            echo ("<tr><td>");
            echo ('<a href="view.php?profile_id='.$row['profile_id'].'">'.$row['first_name']." ".$row['last_name'].'</a>');
            echo ("</td><td>");
            echo (htmlentities($row['headline']));
            echo ("</td><td>");
            echo ('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a>/');
            echo ('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
            echo ("</td></tr>\n");
        }
        echo ("</table>\n");
    }
    echo ('<div><a href="add.php">Add New Entry</a></div>');    
} else {
    echo ('<div><a href="login.php">Please log in</a></div>');
    if ($smth->rowCount() == 0) {
        echo ('<p>No rows found</p>');
    } else {
    echo ('<table border = "1">'."\n");
        echo ("<tr><th>");
        echo ("Name");
        echo ("</th><th>");
        echo ("Headline");
        echo ("</th></tr>");
        while ($row = $smth->fetch(PDO::FETCH_ASSOC)) {
            echo ("<tr><td>");
            echo (htmlentities($row['first_name'])." ".htmlentities($row['last_name']));
            echo ("</td><td>");
            echo (htmlentities($row['headline']));
            echo ("</td></tr>\n");
        }
    echo ("</table>\n");
    }
}
?>

</div>
</body>