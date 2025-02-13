<!-- Create connection to database -->

<?php
$host = '127.0.0.1';
$dbname = 'php_slim_test';
$username = 'root';
$password = '';

try {
    $conn = mysqli_connect($host, $username, $password, database: $dbname);
} catch (mysqli_sql_exception ) {
    echo 'could not connect';
}

if($conn){
    echo'You are connected!';
}
else {
    echo'could not connect';
}
?>
