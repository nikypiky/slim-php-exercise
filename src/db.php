<!-- Create connection to database -->

<?php
$host = '127.0.0.1';
$dbname = 'php_slim';
$username = 'root';
$password = '';

try {
    $mysql = new mysqli($host, $username, $password, database: $dbname);
} catch (mysqli_sql_exception ) {
    echo 'could not connect';
}

if($mysql){
    echo'You are connected!';
}
else {
    echo'could not connect';
}
?>
