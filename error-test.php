<?php

echo "Before error<br>";

require 'db.php';           // if this fails → shows connection error

echo "After require<br>";

throw new Exception("Intentional test error");

echo "This line will never show";
?>
