<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('ListSupportHeading')) ?></h2>
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
				<td><input type='text' name='q' style='width:250px;' placeholder='Ticket ID#, Subject or Account ID...' value='<?= htmlspecialchars($params->get('q')) ?>' /></td>
				<td><input type='submit' value='Search' /></td>
			</tr>
		</table>
	</form>
	<form action="<?php echo $this->urlWithQs ?>" method="post">
		<table>
			<tr>
				<td><button title='Unsubscribe' name='take_action' value='unsubscribe' style='background:none;border:none;cursor:pointer'>
						<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/unsubscribe.png' ?>' alt='Unsubscribe' border='' />
						Unsubscribe
					</button>
				</td>
				<td><button title='Subscribe' name='take_action' value='subscribe' style='background:none;border:none;cursor:pointer'>
						<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/subscribe.png' ?>' alt='Subscribe' border='' />
						Subscribe
					</button>
				</td>
				<?php if ($session->account->$group_col >= Flux::config('TicketCloseGroup')): ?>
					<td><button title='Close' name='take_action' value='close' style='background:none;border:none;cursor:pointer'>
							<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/close.png' ?>' alt='Close' border='' />
							Close
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col  >= Flux::config('TicketOpenGroup')): ?>
					<td><button title='Open' name='take_action' value='open' style='background:none;border:none;cursor:pointer'>
							<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/open.png' ?>' alt='Open' border='' />
							Open
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col  >= Flux::config('TicketResolveGroup')): ?>
					<td><button title='Resolve' name='take_action' value='resolve' style='background:none;border:none;cursor:pointer'>
							<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/resolve.png' ?>' alt='Resolve' border='' />
							Resolve
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col  >= Flux::config('TicketDelGroup')): ?>
					<td><button title='Delete' name='take_action' value='delete' onclick="if(!confirm('Are you sure about this?')) return false;" style='background:none;border:none;cursor:pointer'>
							<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/delete.png' ?>' alt='Delete' border='' />
							Delete
						</button>
					</td>
				<?php endif ?>
			</tr>
		</table>
<?php echo $paginator->infoText() ?>
<table class="horizontal-table">
	<tr>
		<th><input type='checkbox' onclick="selectAll(this, 'id')" /></th>
		<th><?= $paginator->sortableColumn('datetime_submitted', 'Date') ?></th>
		<th><?= $paginator->sortableColumn('subject', 'Subject') ?></th>
		<th><?= $paginator->sortableColumn('department', 'Department') ?></th>
		<th><?= $paginator->sortableColumn('status', 'Status') ?></th>
		<th><?= $paginator->sortableColumn('priority', 'Priority') ?></th>
		<th><?= $paginator->sortableColumn('datetime_updated', 'Last Updated') ?></th>
		<th></th>
	</tr>
	<?php foreach ($ticket_res as $row): ?>
	<tr>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col , $row->id, $server) ? "background:#fff9ba" : "") ?>'><input type='checkbox' class='id' name='ticket_id[]' value='<?= (int) $row->id ?>' /></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col , $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= date("F j", strtotime($row->datetime_submitted)) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col , $row->id, $server) ? "background:#fff9ba" : "") ?>'><a href='<?= getURL($row->id, $this->url('support', 'view')) ?>'><?= "#".$row->id." - ".htmlspecialchars($row->subject) ?></a></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col , $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= getDepartment($server, (int)$row->department)->name ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col , $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= getStatus($row->status) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= getPriority($row->priority) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'><?= date(Flux::config('DateTimeFormat'), strtotime($row->datetime_updated)) ?></td>
		<td style='text-align:center;<?php echo (!isRead($session->account->account_id, $session->account->$group_col, $row->id, $server) ? "background:#fff9ba" : "") ?>'>
			<a href='<?= getURL($row->id, $this->url('support', 'view')) ?>'>View</a>
			<?php if ($session->account->$group_col >= Flux::config('TicketEditGroup')): ?>
				| <a href='<?= getURL($row->id, $this->url('support', 'edit')) ?>'>Edit</a>
			<?php endif ?>
		</td>
	</tr>
	<?php endforeach ?>
</table>
<?php echo $paginator->getHTML() ?>
</form>
<script type='text/javascript'>
	function selectAll(source, c) {
	  	checkboxes = document.getElementsByClassName(c);
		  for(var i in checkboxes)
		    checkboxes[i].checked = source.checked;
	}
</script>
<?php else: ?>
<p class='message'><?= Flux::message('NoTicketList') ?></p>
<?php endif ?>