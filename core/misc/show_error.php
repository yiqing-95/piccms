<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $error['type']?>:<?php echo $error['message']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
<style type="text/css">
<!--
body {
	background-color: white;
	color: black;
}

#container {
	width: 650px;
}

#message {
	width: 650px;
	color: black;
	background-color: #FFFFCC;
}

#bodytitle {
	font: 13pt/15pt verdana, arial, sans-serif;
	height: 35px;
	vertical-align: top;
}

.bodytext {
	font: 8pt/11pt verdana, arial, sans-serif;
}

.help {
	font: 12px verdana, arial, sans-serif;
	color: red;
}

.red {
	color: red;
}

a:link {
	font: 8pt/11pt verdana, arial, sans-serif;
	color: red;
}

a:visited {
	font: 8pt/11pt verdana, arial, sans-serif;
	color: #4e4e4e;
}
-->
</style>
</head>
<body>
	<table cellpadding="1" cellspacing="5" id="container">
		<tr>
			<td id="bodytitle" width="100%"><?php echo $error['type']?></td>
		</tr>
		<tr>
			<td class="bodytext">The program has encountered a problem. <a
				target="_blank" href="<?php echo Wee::$config['sys_url']?>"><span
					class="red">Need Help?</span></a></td>
		</tr>
		<tr>
			<td><hr size="1" /></td>
		</tr>
		<tr>
			<td class="bodytext">Error messages:</td>
		</tr>
		<tr>
			<td class="bodytext" id="message"><div style="padding: 10px">
        <?php echo $error['message']?></div></td>
		</tr>
		<tr>
			<td class="bodytext">&nbsp;</td>
		</tr>
		<tr>
			<td class="bodytext">Program messages:</td>
		</tr>
		<tr>
			<td class="bodytext"><ul>
        <?php foreach ($error['trace'] as $value):?>
        <li><?php echo $value?></li>
        <?php endforeach?>
      </ul></td>
		</tr>
		<tr>
			<td class="help"><br /> <br /> 请与 <a
				href="<?php echo Wee::$config['sys_url']?>"><?php echo Wee::$config['sys_name']?></a><sup><?php echo Wee::$config['sys_ver'];?></sup></a>
				管理员联系, 由此给您带来的访问不便我们深感歉意</td>
		</tr>
	</table>
</body>
</html>