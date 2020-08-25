<?php
require_once "pdo.php";
require_once "util.php";
session_start();
if (!isset($_SESSION['name'])) {
	die("Not logged in");
}

$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $_REQUEST['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$pos = $pdo->prepare("SELECT * FROM position WHERE profile_id = :prof");
$pos->execute(array(":prof" => $_REQUEST['profile_id']));
$posA = $pos->fetch(PDO::FETCH_ASSOC);

$edu = $pdo->prepare("SELECT * FROM education where profile_id = :prof");
$edu->execute(array(":prof" => $_REQUEST['profile_id']));
$eduR = $edu->fetch(PDO::FETCH_ASSOC);
// if ( $row === false ) {
//     $_SESSION['error'] = 'Bad value for profile_id';
//     header( 'Location: index.php' ) ;
//     return;
// }

if ( isset($_POST['first_name']) && isset($_POST['last_name'])
     && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['profile_id'])) {

    // Data validation
    if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1
		&& strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = 'All fields are required';
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    $msg = validatePos();
    if (is_string($msg)) {
    	$_SESSION['error'] = $msg;
    	header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
    	return;
    }

    $msg = validateEdu();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $sql = "UPDATE profile SET first_name = :fst,
            last_name = :lst, email = :eml, headline = :hdl, summary = :smr
            WHERE profile_id = :prf AND user_id = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
    	':uid' => $_SESSION['user_id'],
        ':fst' => $_POST['first_name'],
        ':lst' => $_POST['last_name'],
        ':eml' => $_POST['email'],
        ':hdl' => $_POST['headline'],
        ':smr' => $_POST['summary'],
        ':prf' => $_POST['profile_id']));

    $stmt = $pdo->prepare('DELETE FROM Position
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year'.$i]) || !isset($_POST['desc'.$i])) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description) 
        VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $_REQUEST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }

     $stmt = $pdo->prepare('DELETE FROM Education
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    insertEdu($pdo, $_REQUEST['profile_id']);

    $_SESSION['success'] = 'Record updated';
    header( 'Location: index.php' ) ;
    return;
}

// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}

$fst = htmlentities($row['first_name']);
$lst = htmlentities($row['last_name']);
$mail = htmlentities($row['email']);
$head = htmlentities($row['headline']);
$summary = htmlentities($row['summary']);
$profile_id = $row['profile_id'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Raoul Sulimovici's Profile Edit</title>
<!-- head.php -->

<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">

<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous">

<script
  src="https://code.jquery.com/jquery-3.2.1.js"
  integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
  crossorigin="anonymous"></script>

</head>
<body>
<div class="container">
<h1>Editing Profile for UMSI</h1>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id"
value="<?=$profile_id ?>"
/>
<p>First Name:
<input type="text" name="first_name" size="60"
value="<?=$fst ?>"
/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"
value="<?=$lst ?>"
/></p>
<p>Email:
<input type="text" name="email" size="30"
value="<?=$mail ?>"
/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"
value="<?=$head ?>"
/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"><?= $summary?></textarea></p>
<p>
Education: <input type="submit" id="addEdu" value="+">
<div id="edu_fields">
<?php
if ($eduR != false) {
    echo "<div id=edu".$eduR['institution_id'].">";
    echo "<p>Year: <input type=\"text\" name=\"edu_year".$eduR['institution_id']."\" value=\"".$eduR['year']."\" />";
    echo "<input type=\"button\" value=\"-\" onclick=\"$('#edu".$eduR['institution_id']."').remove();return false;\">"."</p>";
    $eduName = $pdo->prepare("SELECT * FROM institution where institution_id = :iid");
    $eduName->execute(array(":iid" => $eduR['institution_id']));
    $eduName1 = $eduName->fetch(PDO::FETCH_ASSOC);
    echo "<p>School: <input type=\"text\" size=\"80\" name=\"edu_school".$eduR['institution_id']."\" class=\"school\"
value=\"".$eduName1['name']."\" />"."</div>";    
    while ($eduR = $edu->fetch(PDO::FETCH_ASSOC)) {
        echo "<div id=edu".$eduR['institution_id'].">";
        echo "<p>Year: <input type=\"text\" name=\"edu_year".$eduR['institution_id']."\" value=\"".$eduR['year']."\" />";
        echo "<input type=\"button\" value=\"-\" onclick=\"$('#edu".$eduR['institution_id']."').remove();return false;\">"."</p>";
        $eduName = $pdo->prepare("SELECT * FROM institution where institution_id = :iid");
        $eduName->execute(array(":iid" => $eduR['institution_id']));
        $eduName1 = $eduName->fetch(PDO::FETCH_ASSOC);
        echo "<p>School: <input type=\"text\" size=\"80\" name=\"edu_school".$eduR['institution_id']."\" class=\"school\"
    value=\"".$eduName1['name']."\" />"."</div>";
    }
}
?>    
</div>
</p>
<p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
<?php
if ($posA != false) {
	echo "<div id=position".$posA['position_id'].">";
	echo "<p>Year: <input type=\"text\" name=\"year".$posA['position_id']."\" value=\"".$posA['year']."\" />";
	echo "<input type=\"button\" value=\"-\" onclick=\"$('#position".$posA['position_id']."').remove();return false;\">"."</p>";
	echo "<textarea name=\"desc\"".$posA['position_id']."rows=\"8\" cols=\"80\">".$posA['description']."</textarea> </div>";
	
	while ($posA = $pos->fetch(PDO::FETCH_ASSOC)) {
		echo "<div id=position".$posA['position_id'].">";
		echo "<p>Year: <input type=\"text\" name=\"year".$posA['position_id']."\" value=\"".$posA['year']."\" />";
		echo "<input type=\"button\" value=\"-\" onclick=\"$('#position".$posA['position_id']."').remove();return false;\">"."</p>";
		echo "<textarea name=\"desc\"".$posA['position_id']."rows=\"8\" cols=\"80\">".$posA['description']."</textarea> </div>";
	}
}
?>
</div>
</p>
<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script>
countPos = 2;
countEdu = 2;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
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

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        // Grab some HTML with hot spots and insert into the DOM
        var source  = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        // Add the even handler to the new ones
        $('.school').autocomplete({
            source: "school.php"
        });

    });

    $('.school').autocomplete({
        source: "school.php"
    });

});

</script>
<!-- HTML with Substitution hot spots -->
<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</div>
</body>
</html>
