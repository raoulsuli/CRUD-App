<?php
require_once "pdo.php";
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Raoul Sulimovici's Index Page</title>
<?php require_once("bootstrap.php")?>
</head>
<body>	
<div class="container">
<h2> Welcome to my Automobiles Database</h2>
<?php 

if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}
	
if (!isset($_SESSION['name'])) {
	echo "<p><a href=\"login.php\">Please log in</a></p>";
} 

$stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row == false) echo "<p>No rows found</p>";
else {
	echo ('<table border="1">'."\n");
	echo "<thead><tr>";
	echo "<th>Name </th>";
	echo "<th>Headline </th>";
	echo "<th>Action </th>";
	echo "</tr></thead>";
	echo "<tr><td>";
	echo("<a href=\"view.php?profile_id=".$row['profile_id']."\">".htmlentities($row['first_name'].$row['last_name']). "</a>");
	echo("</td><td>");
    echo(htmlentities($row['headline']));
    echo("</td><td>");
    echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
    echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
    echo("</td></tr>\n");
}
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {		
	    
	    echo "<tr><td>";
	    echo("<a href=\"view.php?profile_id=".$row['profile_id']."\">".htmlentities($row['first_name'].$row['last_name']). "</a>");
	    echo("</td><td>");
	    echo(htmlentities($row['headline']));
	    echo("</td><td>");
	    echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
	    echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
	    echo("</td></tr>\n");
}


if (isset($_SESSION['name'])) {
	echo "<p><a href=\"add.php\">Add New Entry</a></p> <p><a href=\"logout.php\">Logout</a></p> <p>";
}

?>
</div>
</body>
</html>