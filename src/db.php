<!-- Create connection to database -->

<?php
$host = '127.0.0.1';
$dbname = 'php_slim';
$username = 'root';
$password = '';

try {
    $mysqli = new mysqli($host, $username, $password, database: $dbname);
} catch (mysqli_sql_exception ) {
    echo 'could not connect';
}

if($mysqli){
    echo'You are connected!';
}
else {
    echo'could not connect';
}
?>
