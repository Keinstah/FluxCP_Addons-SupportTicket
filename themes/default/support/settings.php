<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('SupportSettingsHeading')) ?></h2>
<?php if (!empty($errorMessage)): ?>
	<p class="red"><?php echo htmlspecialchars($errorMessage) ?></p>
<?php elseif (!empty($successMessage)): ?>
	<p class="green"><?php echo htmlspecialchars($successMessage) ?></p>
<?php endif ?>
<form action="<?php echo $this->urlWithQs ?>" method="post" class="generic-form">
	<input type='hidden' name='account_id' value='<?= (int) $session->account->account_id ?>' />
	<table class="vertical-table" style='width:100%'>
		<tr>
			<th><label for='nickname'>Nickname</label></th>
			<td><input type='text' id='nickname' name='nickname' placeholder='<?= htmlspecialchars($nickname) ?>' />
				<span style='font-size:11px;color:#666'><?= htmlspecialchars(Flux::message('NicknameNotice')) ?></span></td>
		</tr>
		<tr>
			<th><label for='email'>Email</label></th>
			<td><input type='text' id='email' name='email' placeholder='<?= htmlspecialchars($email) ?>' />
				<span style='font-size:11px;color:#666'><?= htmlspecialchars(Flux::message('SupportEmailNotice')) ?></span></td>
		</tr>
		<tr>
			<th><label for='subscribe'>Subcription</label></th>
			<td><input type='checkbox' id='subscribe' name='subscribe'<?php echo ($subscribe ? " checked='checked'" : "") ?> />
				<span style='font-size:11px;color:#666'><?= htmlspecialchars(Flux::message('EmailNotice2')) ?></span></td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' value='Save' />
				<span style='font-size:11px;color:#666'><?php echo (!is_null($last_updated) ? htmlspecialchars(sprintf(Flux::message('SupportSettingsLastUpdated'), $last_updated)) : "")  ?></span></td>
		</tr>
	</table>
</form>