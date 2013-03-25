<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('SupportHeading')) ?></h2>
<?php if (!empty($errorMessage)): ?>
	<p class="red"><?php echo htmlspecialchars($errorMessage) ?></p>
<?php elseif (!empty($successMessage)): ?>
	<p class="green"><?php echo htmlspecialchars($successMessage) ?></p>
<?php endif ?>
<?php if (count($ticket_res) !== 0): ?>
	<form action="<?php echo $this->urlWithQs ?>" method="get">
		<input type='hidden' name='module' value='support' />
		<input type='hidden' name='action' value='search' />
		<table class='generic-form-table'>
			<tr>
				<td><input type='text' name='q' style='width:250px;' placeholder='Ticket ID#, Subject...' value='<?= htmlspecialchars($params->get('q')) ?>' /></td>
				<td><input type='submit' value='Search' /></td>
			</tr>
		</table>
	</form>
<?php echo $paginator->infoText() ?>
<table class="horizontal-table">
	<tr>
		<th><?= $paginator->sortableColumn('datetime_submitted', 'Date') ?></th>
		<th><?= $paginator->sortableColumn('subject', 'Subject') ?></th>
		<th><?= $paginator->sortableColumn('department', 'Department') ?></th>
		<th><?= $paginator->sortableColumn('status', 'Status') ?></th>
		<th><?= $paginator->sortableColumn('datetime_updated', 'Last Updated') ?></th>
		<th></th>
	</tr>
	<?php foreach ($ticket_res as $row): ?>
	<tr>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= date("F j", strtotime($row->datetime_submitted)) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><a href='<?= getURL($row->id, $this->url('support', 'view')) ?>'><?= "#".$row->id." - ".htmlspecialchars($row->subject) ?></a></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= getDepartment($server, (int)$row->department)->name ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= getStatus($row->status) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= date(Flux::config('DateTimeFormat'), strtotime($row->datetime_updated)) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><a href='<?= getURL($row->id, $this->url('support', 'view')) ?>'>View Ticket</a></td>
	</tr>
	<?php endforeach ?>
</table>
<?php echo $paginator->getHTML() ?>
<?php else: ?>
<p class='message'><?= Flux::message('NoTicket') ?></p>
<?php endif ?>