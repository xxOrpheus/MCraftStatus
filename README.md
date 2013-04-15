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
if($statusInfo['online'] == true) {
	foreach($statusInfo as $key => $info) {
		echo $key . ' = ' . $info . '<br />';;
	}
} else {
	echo 'play.hackercraft.net is offline.';
}
?>
```

Example
=======
A working example can be found [here](http://cashgoat.us/MCraft)
