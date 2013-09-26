<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('ViewSupportHeading')) ?>
	<a title='Refresh this page' href='<?php echo getURL($params->get('id'), $this->url('support', 'view')) ?>'>
		<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/refresh.png' ?>' alt='Refresh' border='' />
	</a></h2>
<?php if (!empty($errorMessage)): ?>
	<p class="red"><?php echo htmlspecialchars($errorMessage) ?></p>
<?php elseif (!empty($successMessage)): ?>
	<p class="green"><?php echo htmlspecialchars($successMessage) ?></p>
<?php endif ?>

<?php if (!is_null($ticket_res)): ?>

	<form action="<?php echo $this->urlWithQs ?>" method="post">
		<input type='hidden' name='ticket_id' value='<?php echo (int) $ticket_res->id ?>' />
		<table>
			<tr>
				<?php if (isSubscribed($ticket_res->id, $session->account->account_id, $server)): ?>
					<td><button title='Unsubscribe' name='take_action' value='unsubscribe' style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/unsubscribe.png' ?>' alt='Unsubscribe' border='' />
							Unsubscribe
						</button>
					</td>
				<?php else: ?>
					<td><button title='Subscribe' name='take_action' value='subscribe' style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/subscribe.png' ?>' alt='Subscribe' border='' />
							Subscribe
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col >= Flux::config('TicketCloseGroup') && $ticket_res->status != 0): ?>
					<td><button title='Close' name='take_action' value='close' style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/close.png' ?>' alt='Close' border='' />
							Close
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col >= Flux::config('TicketOpenGroup') && $ticket_res->status != 1): ?>
					<td><button title='Open' name='take_action' value='open' style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/open.png' ?>' alt='Open' border='' />
							Open
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col >= Flux::config('TicketResolveGroup') && $ticket_res->status != 2): ?>
					<td><button title='Resolve' name='take_action' value='resolve' style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/resolve.png' ?>' alt='Resolve' border='' />
							Resolve
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col >= Flux::config('TicketDelGroup')): ?>
					<td><button title='Delete' name='take_action' value='delete' onclick="if(!confirm('Are you sure about this?')) return false;" style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/delete.png' ?>' alt='Delete' border='' />
							Delete
						</button>
					</td>
				<?php endif ?>
				<?php if ($session->account->$group_col >= Flux::config('TicketEditGroup')): ?>
					<td><button type='button' title='Edit' onclick="parent.location='<?php echo getURL($ticket_res->id, $this->url('support', 'edit')) ?>'" style='background:none;border:none;cursor:pointer'>
							<img src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/edit.png' ?>' alt='Edit' border='' />
							Edit
						</button>
					</td>
				<?php endif ?>
			</tr>
		</table>
	</form>

	<hr style='border:none;padding-top:5px;' />

	<table class="horizontal-table">
		<tr>
			<th>Submitted</th>
			<th>Ticket ID#</th>
			<th>Priority</th>
			<th>Status</th>
			<th>Department</th>
			<th>Character Affected</th>
		</tr>
		<tr>
			<td style='text-align:center;'><?php echo date(Flux::config('DateTimeFormat'), strtotime($ticket_res->datetime_submitted)) ?></td>
			<td style='text-align:center;'><?php echo (int)$ticket_res->id ?></td>
			<td style='text-align:center;'><?php echo getPriority($ticket_res->priority) ?></td>
			<td style='text-align:center;'><?php echo getStatus($ticket_res->status) ?></td>
			<td style='text-align:center;'><?php echo getDepartment($server, $ticket_res->department)->name ?></td>
			<?php if ($session->account->$group_col >= Flux::config('TicketShowChar') && $ticket_res->char_id): ?>
				<td style='text-align:center;'><a title='View Character' href='<?php echo getURL($ticket_res->char_id, $this->url('character', 'view')) ?>'><?php echo (getCharAffected($ticket_res->char_id, $server) ? getCharAffected($ticket_res->char_id, $server) : "<span style='color:#999'>None</span>") ?></a></td>
			<?php else: ?>
				<td style='text-align:center;'><?php echo (getCharAffected($ticket_res->char_id, $server) ? getCharAffected($ticket_res->char_id, $server) : "<span style='color:#999'>None</span>") ?></td>
			<?php endif ?>
		</tr>
	</table>
	<hr style='border:none;padding-top:5px;' />
	<table class='horizontal-table'>
		<tr>
			<th>
				<span style='float:left;font-size:18px;color:#4083c6;max-width:90%'><?php echo htmlspecialchars($ticket_res->subject) ?></span>
					<span style='float:right;'>
						<?php if ($session->account->$group_col >= Flux::config('TicketShowUsername')): ?>
						<a href='<?php echo getURL($ticket_res->account_id, $this->url('account', 'view')) ?>'>
						<?php endif ?>
							<?php echo getNickname($ticket_res->account_id, $server) ?>
						<?php if ($session->account->$group_col >= Flux::config('TicketShowUsername')): ?>
						</a>
						<?php endif ?>
					</span>
				
			</th>
		</tr>
		<tr>
			<td><?php echo $ticket_res->message ?></td>
		</tr>
	</table>
	<h3 style='padding:0;' id='reply_area'>Reply</h3>
	<?php if ($ticket_res->status != 0 && $ticket_res->status != 2): ?>
	<form action="<?php echo $this->urlWithQs ?>#reply_area" method="post" class="generic-form">
		<input type='hidden' name='account_id' value='<?php echo (int) $session->account->account_id ?>' />
		<input type='hidden' name='ticket_id' value='<?php echo (int) $ticket_res->id ?>' />
		<input type='hidden' name='subscribe' value='<?php echo (int) $ticket_res->subscribe ?>' />
		<input type='hidden' name='subject' value='<?php echo $ticket_res->subject ?>' />
		<input type='hidden' name='priority' value='<?php echo $ticket_res->priority ?>' />
		<input type='hidden' name='email' value='<?php echo $ticket_res->email ?>' />
		<textarea style='width:500px' id='reply' name='reply'></textarea><br />
		<input type='submit' value='Submit Reply' />
		<?php if ($session->account->$group_col >= AccountLevel::LOWGM): ?>
			<input type='radio' id='nothing' name='status' value='3' checked='checked' /> <label for='nothing'>Do nothing</label>
			<input type='radio' id='close' name='status' value='0' /> <label for='close'>Close</label>
			<input type='radio' id='resolved' name='status' value='2' /> <label for='resolved'>Resolve</label>
		<?php else: ?>
			<input type='hidden' name='status' value='3' />
		<?php endif ?>
	</form>
	<hr style='border:none;padding-top:5px;' />
	<?php endif ?>
	<?php if (count($reply_res) !== 0): ?>
		<?php echo $paginator->infoText() ?>
		<form action="<?php echo $this->urlWithQs ?>" method="post">
		<?php $i = 0; foreach ($reply_res as $row): ?>
		<?php if ($i !== 0): ?>
			<hr style='border:none;padding-top:2px;' />
		<?php endif ?>
			<table class='horizontal-table'>
				<tr>
					<th>
						<span style='display:block;float:left;'>
						<?php if ($session->account->$group_col >= Flux::config('TicketShowUsername')): ?>
						<a href='<?php echo getURL($ticket_res->account_id, $this->url('account', 'view')) ?>'>
						<?php endif ?>
							<?php echo (getGroupID($row->account_id, $server) >= AccountLevel::LOWGM ? "<img src='".Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/staff.png'."' alt='Staff' border='' title='Staff' />" : "")." ".getNickname($row->account_id, $server) ?>
						
						<?php if ($session->account->$group_col >= Flux::config('TicketShowUsername')): ?>
						</a>
						<?php endif ?>
						</span>
						<span style='display:block;float:right;'>
							<?php echo date(Flux::config('DateTimeFormat'), strtotime($row->datetime_submitted)) ?>
						</span>
					</th>
				</tr>
				<tr class='deleteTrigger'>
					<td<?php echo (getGroupID($row->account_id, $server) >= AccountLevel::LOWGM ? " style='background:#fff3d8'" : " style='background:#e4ffe8'") ?>>
						<span style='width:90%;dispaly:block;float:left'><?php echo $row->reply ?></span>
						<?php if ($session->account->$group_col >= AccountLevel::LOWGM): ?>
							<span style='display:block;float:right;height:25px'><button style='display:none' type='submit' name='delete_reply' value='<?php echo (int) $row->id ?>' onclick="if(!confirm('Are you sure about this?'))return false;">Delete</button></span>
						<?php endif ?>
					</td>
				</tr>
			</table>
		<?php $i++; endforeach ?>
		</form>
		<?php echo $paginator->getHTML() ?>
	<?php endif ?>


<script src='<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/js/nicEdit.js' ?>' type='text/javascript'>
</script>
<script type='text/javascript'>

	$(function() {
		$('.deleteTrigger').hover(function() {
			var button = $(this).find('button[name=delete_reply]');
			$(button).show();
		}, function() {
			var button = $(this).find('button[name=delete_reply]');
			$(button).hide();
		});

	});

	var nicEditorConfig = bkClass.extend({
		buttons : {
			'bold' : {name : __('Click to Bold'), command : 'Bold', tags : ['B','STRONG'], css : {'font-weight' : 'bold'}, key : 'b'},
			'italic' : {name : __('Click to Italic'), command : 'Italic', tags : ['EM','I'], css : {'font-style' : 'italic'}, key : 'i'},
			'underline' : {name : __('Click to Underline'), command : 'Underline', tags : ['U'], css : {'text-decoration' : 'underline'}, key : 'u'},
			'left' : {name : __('Left Align'), command : 'justifyleft', noActive : true},
			'center' : {name : __('Center Align'), command : 'justifycenter', noActive : true},
			'right' : {name : __('Right Align'), command : 'justifyright', noActive : true},
			'justify' : {name : __('Justify Align'), command : 'justifyfull', noActive : true},
			'ol' : {name : __('Insert Ordered List'), command : 'insertorderedlist', tags : ['OL']},
			'ul' : 	{name : __('Insert Unordered List'), command : 'insertunorderedlist', tags : ['UL']},
			'subscript' : {name : __('Click to Subscript'), command : 'subscript', tags : ['SUB']},
			'superscript' : {name : __('Click to Superscript'), command : 'superscript', tags : ['SUP']},
			'strikethrough' : {name : __('Click to Strike Through'), command : 'strikeThrough', css : {'text-decoration' : 'line-through'}},
			'removeformat' : {name : __('Remove Formatting'), command : 'removeformat', noActive : true},
			'indent' : {name : __('Indent Text'), command : 'indent', noActive : true},
			'outdent' : {name : __('Remove Indent'), command : 'outdent', noActive : true},
			'hr' : {name : __('Horizontal Rule'), command : 'insertHorizontalRule', noActive : true}
		},
		iconsPath : '<?php echo Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/nicEditorIcons.gif' ?>',
		buttonList : ['save','bold','italic','underline','left','center','right','justify','ol','ul','fontSize','fontFamily','fontFormat','indent','outdent','image','upload','link','unlink','forecolor','bgcolor'],
		iconList : {"bgcolor":1,"forecolor":2,"bold":3,"center":4,"hr":5,"indent":6,"italic":7,"justify":8,"left":9,"ol":10,"outdent":11,"removeformat":12,"right":13,"save":24,"strikethrough":15,"subscript":16,"superscript":17,"ul":18,"underline":19,"image":20,"link":21,"unlink":22,"close":23,"arrow":25,"upload":26}
		
	});
	;
	
	//<![CDATA[
        bkLib.onDomLoaded(function() { new nicEditor().panelInstance('reply'); });
  	//]]>
</script>
<?php else: ?>
<p class='message'><?php echo Flux::message('TicketNotExists') ?></p>
<?php endif ?>