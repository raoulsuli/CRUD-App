<?php
require_once "pdo.php";
session_start();
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$pos = $pdo->prepare("SELECT * FROM position where profile_id = :prof");
$pos->execute(array(":prof" => $_GET['profile_id']));
$posA = $pos->fetch(PDO::FETCH_ASSOC);

$edu = $pdo->prepare("SELECT * FROM education where profile_id = :prof");
$edu->execute(array(":prof" => $_GET['profile_id']));
$eduR = $edu->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Raoul Sulimovici's Profile View</title>
<!-- bootstrap.php - this is HTML -->

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous">

</head>
<body>
<div class="container">
<h1>Profile information</h1>
<p>First Name:
<?= $row['first_name']?></p>
<p>Last Name:
<?= $row['last_name']?></p>
<p>Email:
<?= $row['email']?></p>
<p>Headline:<br/>
<?= $row['headline']?></p>
<p>Summary:<br/>
<?= $row['summary']?></p>
<?php

if ($eduR != false) {
	echo "<p>Education<br/><ul>";
	$eduName = $pdo->prepare("SELECT * FROM institution where institution_id = :iid");
	$eduName->execute(array(":iid" => $eduR['institution_id']));
	$eduName1 = $eduName->fetch(PDO::FETCH_ASSOC);
	echo "<li>".$eduR['year'].": ".$eduName1['name']."</li>";
	while ($eduR = $edu->fetch(PDO::FETCH_ASSOC)) {
		$eduName = $pdo->prepare("SELECT * FROM institution where institution_id = :iid");
		$eduName->execute(array(":iid" => $eduR['institution_id']));
		$eduName1 = $eduName->fetch(PDO::FETCH_ASSOC);
		echo "<li>".$eduR['year'].": ".$eduName1['name']."</li>";
	}
	echo "</ul>";
}

if ($posA != false) {
	echo "<p>Position<br/><ul>";
	echo "<li>".$posA['year'].": ".$posA['description']."</li>";
	while ($posA = $pos->fetch(PDO::FETCH_ASSOC)) {
		echo "<li>".$posA['year'].": ".$posA['description']."</li>";
	}
	echo "</ul>";
}
?>
<a href="index.php">Done</a>
</div>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script></body>
</html>
