<?php
require_once "pdo.php";
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxim Rosin Profile Information</title>
</head>
<body>
    <h1>Profile information</h1>
    <?php
    $smth = $pdo->query("SELECT first_name, last_name, email, headline, summary FROM profile");
    $row = $smth->fetch(PDO::FETCH_ASSOC);
    echo('<p>First Name: '.$row['first_name'].'</p>');
    echo('<p>Last Name: '.$row['last_name'].'</p>');
    echo('<p>Email: '.$row['email'].'</p>');
    echo('<p>Headline: '.$row['headline'].'</p>');
    echo('<p>Summary: '.$row['summary'].'</p>');
    ?>
    <?php
    $smth = $pdo->query("SELECT year, name FROM Education
        JOIN Institution ON Education.institution_id = Institution.institution_id");
    if ($smth->rowCount() > 0) {
        echo ('<p>Education</p>');
        echo ('<ul>');
        while ($row = $smth->fetch(PDO::FETCH_ASSOC)) {
            echo ('<li>'.$row['year'].': '.$row['name'].'</li>');
        }
        echo ("</ul>\n");
    }
    ?>
    <?php
    $smth = $pdo->query("SELECT year, description FROM position");
    if ($smth->rowCount() > 0) {
        echo ('<p>Position</p>');
        echo ('<ul>');
        while ($row = $smth->fetch(PDO::FETCH_ASSOC)) {
            echo ('<li>'.$row['year'].': '.$row['description'].'</li>');
        }
        echo ("</ul>\n");
    }
    ?>
    <?php
        echo('<a href="index.php">Done</a>');
    ?>
</body>
</html>