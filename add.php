<?php
session_start();
require_once "pdo.php";
require_once "util.php";
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
    $quest_first_name = htmlentities($_POST['first_name']);
    $quest_last_name = htmlentities($_POST['last_name']);
    $quest_email = htmlentities($_POST['email']);
    $quest_headline = htmlentities($_POST['headline']);
    $quest_summary = htmlentities($_POST['summary']);

    if (empty($quest_first_name) || empty($quest_last_name) || empty($quest_email) || empty($quest_headline) || empty($quest_summary) ) {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
    }
    if ($_POST['email']) {
        $email = test_input($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email address must contain @";
            header("Location: add.php");
            return;
        }
    } 

    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg = validateEd();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO Profile 
        (user_id, first_name, last_name, email, headline, summary) 
        VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );
    $profile_id = $pdo->lastInsertId();

    insertPositions($pdo, $profile_id);

    insertEducations($pdo, $profile_id);

    $_SESSION['success'] = "Record added";
    header("Location: index.php");
    return;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxim Rosin's Automobile Tracker</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css"> 
    <script src="https://code.jquery.com/jquery-3.2.1.js" 
    integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" 
    crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" 
    integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" 
    crossorigin="anonymous">
    </script>
</head>
<body>
<div class="container">
<h1> Adding Profile for <?= htmlentities($_SESSION['name']); ?></h1>
<?php
// Note triple not equals and think how badly double
// not equals would work here...
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>
<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60"/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"/></p>
<p>Email:
<input type="text" name="email" size="30"/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"></textarea></p>
Education: <input type="submit" id="addEd" value="+"><br/>
<div id="education_fields">
</div>
<p></p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
</div>
<p></p>
<input type="submit" value="Add">
<input type="submit" name="cancel" value="Cancel">
</form>
<script>
let countPos = 0;
let countEd = 0;

$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });
    $('#addEd').click(function(event){
        event.preventDefault();
        if ( countEd >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countEd++;
        window.console && console.log("Adding education "+countEd);

        var source = $("#edu-template").html();
        $('#education_fields').append(source.replace(/@COUNT/g,countEd));

        $('.school').autocomplete({
            source: "school.php"
        });
    });
    $('.school').autocomplete({
            source: "school.php"
        });
});
</script>
<script id="edu-template">
    <div id="edu@COUNT">
        <p> Year: <input type="text" name="edu_year@COUNT" value="" />
        <input type="button" value="-" onclick="$('#edu@COUNT').remove(); return false;"> <br>
        <p> School: <input type="text" size="80" name="edu_school@COUNT" class="school" value="" /> 
        </p>
    </div>
</script>
</body>
</html>