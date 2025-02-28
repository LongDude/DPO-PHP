<?php
    $host = 'db';
    $dbname = $_ENV['DB_NAME'];
    $dbuser = $_ENV['DB_USER'];
    $dbpass = $_ENV['DB_PASSWORD'];
    $port = '5432';
    print("$dbuser $dbpass\n");
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$dbuser password=$dbpass");
    if (!$conn) {
        echo "Connection failed";
    } else {
        echo "Success";
    }
?>