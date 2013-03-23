<?php
if (!defined('FLUX_ROOT')) exit;
$siteTitle  = Flux::config('SiteTitle');
$emailTitle = sprintf('%s: Support Ticket', $siteTitle);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo htmlspecialchars($emailTitle) ?></title>
		<style type="text/css" media="screen">
			body, table {
				font-family: sans-serif;
				font-size: 10pt;
			}
		</style>
	</head>
	<body>
		<h2><?php echo htmlspecialchars($emailTitle) ?></h2>
		
		<p>{From}</p>
		<p>{Message}</p>
		
		<p>
			<table style="margin-left: 18px">
				<tr>
					<td align="right">Subject:&nbsp;&nbsp;</td>
					<th align="left">{Subject}</th>
				</tr>
				<tr>
					<td align="right">Priority:&nbsp;&nbsp;</td>
					<th align="left">{Priority}</th>
				</tr>
				<tr>
					<td align="right">Status:&nbsp;&nbsp;</td>
					<th align="left">{Status}</th>
				</tr>
				<tr>
					<td align="right">URL:&nbsp;&nbsp;</td>
					<th align="left">{URL}</th>
				</tr>
			</table>
		</p>
		
		<p><em><strong>Note:</strong> This is an automated e-mail, please do not reply to this address.</em></p>
	</body>
</html>