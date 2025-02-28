<?php
    if (isset($_ENV['TEST_MESSAGE'])){
        echo $_ENV['TEST_MESSAGE'];
    }
    else {
        echo 'NO_ENV_SET';
    }
?>