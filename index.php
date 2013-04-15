<!DOCTYPE html>
<html>
  <head>
		<style type="text/css">
			body {
				font-family: arial, helvetica, sans-serif;
				font-size: 10pt;
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
