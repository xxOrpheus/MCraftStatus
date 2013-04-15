MCraftStatus
============

Returns the current status of a Minecraft server.

Usage
=====
```php
<?php
require 'class.MCStatus.php';
$mcstatus = new Orpheus\MCStatus('play.hackercraft.net');
$statusInfo = $mcstatus->getStatus();
foreach($statusInfo as $key => $info) {
  echo $key . ': ' . $info . '<br />';;
}
?>
```
