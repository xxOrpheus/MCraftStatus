MCraftStatus
============

Returns the current status of a Minecraft server.

Usage
=====
```php
<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">
			@font-face {
				font-family: minecraft;
				src: url('minecraft.ttf');
			}

			body {
				font-family: minecraft;
				font-size: 12pt;
				font-smooth: never;
				-webkit-font-smoothing: none;
			}
		</style>
	</head>

	<body>
		<?php
		require 'class.MCStatus.php';
		$mcstatus = new Orpheus\MCStatus('oreminecraft.net');
		$statusInfo = $mcstatus->getStatus();
		foreach($statusInfo as $key => $info) {
			echo $key . ' = ' . $info . '<br />';;
		}
		?>
	</body>
</html>
```

Example
=======
A working example can be found [here](http://cashgoat.us/MCraft)
