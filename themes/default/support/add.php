<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('AddSupportHeading')) ?></h2>
<?php if (!empty($errorMessage)): ?>
	<p class="red"><?php echo htmlspecialchars($errorMessage) ?></p>
<?php elseif (!empty($successMessage)): ?>
	<p class="green"><?php echo htmlspecialchars($successMessage) ?></p>
<?php endif ?>
<?php if (is_null($unavailable)): ?>
<form action="<?php echo $this->urlWithQs ?>" method="post" class="generic-form">
	<input type='hidden' name='account_id' value='<?= htmlspecialchars($session->account->account_id) ?>' />
	<input type='hidden' name='email' value='<?= htmlspecialchars($session->account->email) ?>' />
	<table class="horizontal-table">
		<tr>
			<th colspan='3' style='text-align:left!important;'><label for='subject'>Subject</label></th>
		</tr>
		<tr>
			<td colspan='3' style='text-align:left!important;'><input type='text' id='subject' name='subject' style='width:500px' value='<?= htmlspecialchars($params->get('subject')) ?>' /></td>
		</tr>
		<tr>
			<th style='text-align:left!important;'><label for='department'>Department</label></th>
			<th style='text-align:left!important;'<?php echo (count($char_res) === 0 ? " colspan='2'" : "") ?>><label for='priority'>Priority</label></th>
			<?php if (count($char_res) > 0): ?>
			<th style='text-align:left!important;'><label for='char'>Character affected</label></th>
			<?php endif ?>
		</tr>
		<tr>
			<td>
				<select name='department' id='department'>
					<?php foreach (getDepartment($server) as $row): ?>
						<?php if ($params->get('department') == $row->id): ?>
							<option value='<?= $row->id ?>' selected='selected'><?= htmlspecialchars($row->name) ?></option>
						<?php else: ?>
							<option value='<?= $row->id ?>'><?= htmlspecialchars($row->name) ?></option>
						<?php endif ?>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<select name='priority' id='priority'>
					<option value='0'<?php echo ((int) $params->get('priority') === 0 ? " selected='selected'" : "") ?>>Low</option>
					<option value='1'<?php echo ((int) $params->get('priority') === 1 ? " selected='selected'" : "") ?>>Medium</option>
					<option value='2'<?php echo ((int) $params->get('priority') === 2 ? " selected='selected'" : "") ?>>High</option>
				</select>
			</td>
			<?php if (count($char_res) > 0): ?>
			<td>
				<select name='char' id='char'>
					<option value=''>None</option>
				<?php foreach ($char_res as $row): ?>
					<?php if ((int)$params->get('char') === $row->char_id): ?>
						<option value='<?= (int) $row->char_id ?>' selected='selected'><?= htmlspecialchars($row->name) ?></option>
					<?php else: ?>
						<option value='<?= (int) $row->char_id ?>'><?= htmlspecialchars($row->name) ?></option>
					<?php endif ?>
				<?php endforeach ?>
				</select>
			</td>
			<?php endif ?>
		</tr>
		<tr>
			<th colspan='3' style='text-align:left!important;'><label for='message'>Message</label></th>
		</tr>
		<tr>
			<td colspan='3'>
				<textarea id='message' name='message' style='width:500px'><?= htmlspecialchars($params->get('message')) ?></textarea>
			</td>
		</tr>
		<?php if (Flux::config('EnableSubscribing')): ?>
		<tr>
			<td colspan='3'><input style='float:left' type='checkbox' name='subscribe' id='subscribe' value='1'<?php echo ((int)$params->get('subscribe') === 1 ? " checked='checked'" : "") ?> />
				<label style='float:left;padding-top:2px;' for='subscribe'>Subscribe</label>
				 <span style='float:left;display:block;font-size:11px;color:#999;padding-top:2px;margin-left:3px'><?= htmlspecialchars(Flux::message('EmailNotice')) ?></span></td>
		</tr>
		<?php endif ?>
		<tr>
			<td colspan='3'><input style='float:right' type='submit' value='<?= htmlspecialchars(Flux::message('SubmitTicket')) ?>'></td>
		</tr>
	</table>
</form>
<?php else: ?>
	<p class='red'><?= htmlspecialchars(sprintf(Flux::message('TicketAlreadySubmitted'), getTimeLeft(strtotime("+".Flux::config('TicketDelay').' hours', strtotime($timestamp))))) ?></p>
<?php endif ?>

<script src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/js/nicEdit.js' ?>' type='text/javascript'>
</script>
<script type='text/javascript'>

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
		iconsPath : '<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/nicEditorIcons.gif' ?>',
		buttonList : ['save','bold','italic','underline','left','center','right','justify','ol','ul','fontSize','fontFamily','fontFormat','indent','outdent','image','upload','link','unlink','forecolor','bgcolor'],
		iconList : {"bgcolor":1,"forecolor":2,"bold":3,"center":4,"hr":5,"indent":6,"italic":7,"justify":8,"left":9,"ol":10,"outdent":11,"removeformat":12,"right":13,"save":24,"strikethrough":15,"subscript":16,"superscript":17,"ul":18,"underline":19,"image":20,"link":21,"unlink":22,"close":23,"arrow":25,"upload":26}
		
	});
	;
	
	//<![CDATA[
        bkLib.onDomLoaded(function() { new nicEditor().panelInstance('message'); });
  	//]]>
</script>