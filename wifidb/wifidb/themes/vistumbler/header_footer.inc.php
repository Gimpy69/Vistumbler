<?php
#========================================================================================================================#
#											Header (writes the Headers for all pages)									 #
#========================================================================================================================#

function pageheader($title, $output="detailed")
{
	session_start();
	if(!$_SESSION['token'] or !$_GET['token'])
	{
		$token = md5(uniqid(rand(), true));
		$_SESSION['token'] = $token;
	}else
	{
		$token = $_SESSION['token'];
	}
	
	$root	= $GLOBALS['root'];
	$conn	=	$GLOBALS['conn'];
	$db		=	$GLOBALS['db'];
	$head	= 	$GLOBALS['header'];
	
	echo "<html>\r\n<head>\r\n<title>Wireless DataBase".$GLOBALS['ver']['wifidb']." --> ".$title."</title>\r\n".$head."\r\n</head>\r\n";
	check_install_folder();
	if($output == "detailed")
	{
		# START YOUR HTML EDITS HERE #
		?>
		<link rel="stylesheet" href="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/styles.css">
			<body style="background-color: #145285">
			<table style="width: 90%; " class="no_border" align="center">
				<tr>
					<td>
					<table>
						<tr>
							<td style="width: 228px">
							<a href="http://www.randomintervals.com">
							<img alt="Random Intervals Logo" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/logo.png" class="no_border" /></a></td>
						</tr>
					</table>

					</td>
				</tr>
			</table>
			<table style="width: 90%" align="center">
				<tr>
					<td style="width: 165px; height: 114px" valign="top">
						<table style="width: 100%" cellpadding="0" cellspacing="0">
							<tr>
								<td style="width: 10px; height: 20px" class="cell_top_left">
									<img alt="" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/1x1_transparent.gif" width="10" height="1" />
								</td>
								<td class="cell_top_mid" style="height: 20px">
									<img alt="" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/1x1_transparent.gif" width="185" height="1" />
								</td>
								<td style="width: 10px" class="cell_top_right">
									<img alt="" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/1x1_transparent.gif" width="10" height="1" />
								</td>
								<td style="width: 100%" align="right">
									<?php
									if(login_check())
									{
										list($cookie_pass_seed, $username) = explode(':', $_COOKIE['WiFiDB_login_yes']);
										echo "Welcome ".$username." !";
									}else
									{
										?><a class="links" href="login.php">Login</a><?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="cell_side_left">&nbsp;</td>
								<td class="cell_color">
									<div class="inside_dark_header">WiFiDB Links</div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/?token=<?php echo $token;?>">Main Page</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/all.php?sort=SSID&ord=ASC&from=0&to=100&token=<?php echo $token;?>">View All APs</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/import/?token=<?php echo $token;?>">Import</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/opt/scheduling.php?token=<?php echo $token;?>">Files Waiting for Import</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/opt/scheduling.php?func=done&token=<?php echo $token;?>">Files Already Imported</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/opt/scheduling.php?func=daemon_kml&token=<?php echo $token;?>">Daemon Generated kml</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/console/?token=<?php echo $token;?>">Daemon Console</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/opt/export.php?func=index&token=<?php echo $token;?>">Export</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/opt/search.php?token=<?php echo $token;?>">Search</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/themes/?token=<?php echo $token;?>">Themes</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/opt/userstats.php?func=allusers&token=<?php echo $token;?>">View All Users</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a class="links" href="http://forum.techidiots.net/forum/viewforum.php?f=47">Help / Support</a></strong></div>
									<div class="inside_text_bold"><strong>
										<a href="<?php if($root != ''){echo '/'.$root;}?>/ver.php?token=<?php echo $token;?>">WiFiDB Version</a></strong></div>
								</td>
								<td class="cell_side_right">&nbsp;</td>
							</tr>
							<tr>
								<td class="cell_bot_left">&nbsp;</td>
								<td class="cell_bot_mid">&nbsp;</td>
								<td class="cell_bot_right">&nbsp;</td>
							</tr>
						</table>
					</td>
					<td style="height: 114px" valign="top" class="center">
						<table style="width: 100%" cellpadding="0" cellspacing="0">
							<tr>
								<td style="width: 10px; height: 20px" class="cell_top_left">
									<img alt="" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/1x1_transparent.gif" width="10" height="1" />
								</td>
								<td class="cell_top_mid" style="height: 20px">
									<img alt="" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/1x1_transparent.gif" width="120" height="1" />
								</td>
								<td style="width: 10px" class="cell_top_right">
									<img alt="" src="<?php if($root != ''){echo '/'.$root;}?>/themes/vistumbler/img/1x1_transparent.gif" width="10" height="1" />
								</td>
							</tr>
							<tr>
								<td class="cell_side_left">&nbsp;</td>
								<td class="cell_color_centered" align="center">
								<div align="center">
		<?php
	}
}


#========================================================================================================================#
#											Footer (writes the footer for all pages)									 #
#========================================================================================================================#

function footer($filename = '')
{
	?>
							</div>
							<br>
							</td>
							<td class="cell_side_right">&nbsp;</td>
						</tr>
						<tr>
							<td class="cell_bot_left">&nbsp;</td>
							<td class="cell_bot_mid">&nbsp;</td>
							<td class="cell_bot_right">&nbsp;</td>
						</tr>
					</table>
				<div class="inside_text_center" align=center><strong>
				Random Intervals Wireless DataBase<?php echo $GLOBALS['ver']['wifidb'].'<br />'; ?></strong></div>
				<br />
				<?php
				echo $GLOBALS['tracker'];
				echo $GLOBALS['ads']; 
				?>
				</td>
			</tr>
		</table>
	</body>
	</html>
	<?php
}
?>