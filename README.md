MCraftStatus
============
Returns the current status of a Minecraft server.

Usage
=====
```php
<?php
require 'class.MCStatus.php';
$mcstatus = new Orpheus\MCStatus('127.0.0.1');

// using enable-query=true
$status = $mcstatus->getStatus(true, true);

// using enable-query=false
$status = $mcstatus->getStatus(true);

echo '<pre>';
var_dump($status);
echo '</pre>';
?>
```

Example
=======
A working example can be found [here](http://programm.in/mcstatus)
