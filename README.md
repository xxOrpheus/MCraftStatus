MCraftStatus
============
Returns the current status of a Minecraft server.

Usage
=====
```php
<?php
require 'class.MCStatus.php';
$mcstatus = new Orpheus\MCStatus('127.0.0.1');
$status = $mcstatus->getStatus(true, true); // if you do not have enable-query set to "true", set the second argument to false or remove it.
echo '<pre>';
var_dump($status);
echo '</pre>';
?>
```

Example
=======
A working example can be found [here](http://programm.in/mcstatus)
