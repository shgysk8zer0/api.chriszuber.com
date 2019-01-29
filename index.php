<?php
require('./autoloader.php');
$dsn = \shgysk8zer0\DSN::loadFromURL('mysql://user:pass@localhost:3306/dbname');
echo json_encode($dsn, JSON_PRETTY_PRINT);
