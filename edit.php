<?php
require_once "pdo.php";
require_once "util.php";
session_start();
if ( ! isset($_SESSION['user_id']) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

// Guardian: Make sure that autos_id is present
if ( ! isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare('SELECT * FROM profile 
    WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(':prof' => $_REQUEST['profile_id'], 
    ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = 'Could not load profile';
    header("Location: index.php");
    return;
}
  
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && 
    isset($_POST['email']) && isset($_POST['headline']) && 
    isset($_POST['summary'])) {

    // Data validation
    if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = 'Missing data';
        header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
        return;
    }

    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
        return;
    }

    $msg = validateEd();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
        return;
    }

    $stmt = $pdo->prepare('UPDATE profile SET 
        first_name = :fn, last_name = :ln, 
        email = :em, headline = :he, summary = :su
        WHERE profile_id = :pid AND user_id = :uid');
    $stmt->execute(array(
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'],
        ':uid' => $_SESSION['user_id'],
        ':pid' => $_REQUEST['profile_id'])
    );

    $stmt = $pdo->prepare('DELETE FROM Position
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));


    insertPositions($pdo, $_REQUEST['profile_id']);

    $stmt = $pdo->prepare('DELETE FROM Education
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    insertEducations($pdo, $_REQUEST['profile_id']);
  
    $_SESSION['success'] = 'Profile updated';
    header( 'Location: index.php' ) ;
    return;
}

$schools = loadEd($pdo, $_REQUEST['profile_id']);
$positions = loadPos($pdo, $_REQUEST['profile_id']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxim Rosin Edit</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

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
<h1> Editing Profile for <?= htmlentities($_SESSION['name']); ?></h1>
<?php
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
?>
<form method="post" action="edit.php">
<p>First Name:
<input type="text" name="first_name" size="60" value="<?= htmlentities($profile['first_name']); ?>"></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value="<?= htmlentities($profile['last_name']); ?>"></p>
<p>Email:
<input type="text" name="email" size="30" value="<?= htmlentities($profile['email']); ?>"></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80" value="<?= htmlentities($profile['headline']); ?>"></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"><?= htmlentities($profile['summary']); ?></textarea></p>
<input type="hidden" name="profile_id" value="<?= $profile['profile_id']; ?>">

<?php
    $ed = 0;
    echo('<p>Education: <input type="submit" id="addEd" value="+">'."\n");
    echo('<div id="education_fields">'."\n");
    if (count($schools) > 0) {
        foreach ($schools as $school) {
            $ed++;
            echo('<div id="edu'.$ed.'">'."\n");
            echo('<p>Year: <input type="text" name="edu_year'.$ed.'" value="'.$school['year'].'" />
            <input type="button" value="-" onclick="$(\'#edu'.$ed.'\').remove();return false;"></p>
            <p>School: <input type="text" size="80" name="edu_school'.$ed.'" class="school" 
            value="'.htmlentities($school['name']).'" />');
            echo("\n</div>\n");
        }
    }
    echo("</div></p>\n");

    $pos = 0;
    echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
    echo('<div id="position_fields">'."\n");
    if (count($positions) > 0) {
        foreach ($positions as $position) {
            $pos++;
            echo('<div class="position" id="position'.$pos.'">'."\n");
            echo('<p>Year: <input type="text" name="year'.$pos.'" value="'.htmlentities($position['year']).'"/>
            <input type="button" value="-" onclick="$(\'#position'.$ed.'\').remove();return false;"><br>');
            echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
            echo(htmlentities($position['description'])."\n");
            echo("\n</textarea>\n</div>\n");
        }
    }
    echo("</div></p>\n");
?>
<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script>
let countPos = <?= $pos ?>;
let countEd = <?= $ed ?>;

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



