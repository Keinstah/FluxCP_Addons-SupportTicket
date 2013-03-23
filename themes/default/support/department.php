<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('DepSupportHeading')) ?></h2>
<?php if (!empty($errorMessage)): ?>
	<p class="red"><?php echo htmlspecialchars($errorMessage) ?></p>
<?php elseif (!empty($successMessage)): ?>
	<p class="green"><?php echo htmlspecialchars($successMessage) ?></p>
<?php endif ?>

<h3>Create or Update Department</h3>
<form action="<?php echo $this->urlWithQs ?>" method="post" class="generic-form">
	<table class='generic-form-table'>
		<tr>
			<th><label for='name'>Name</label></th>
			<td>
				<select name='take_action'>
					<option value='new'>~ Create New ~</option>
					<?php foreach ($all_dep_res as $row): ?>
					<option value='<?= (int) $row->id ?>'><?= htmlspecialchars($row->name) ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td><input type='text' id='name' name='name' placeholder='Name here...' /></td>
		</tr>
		<tr>
			<th><label for='group_id'>Group</label></th>
			<td>
				<select id='group_id' name='group_id'>
					<?php foreach ($groups as $name => $level): ?>
					<option value='<?= (int) $level ?>'><?= htmlspecialchars($name) ?></option>
					<?php endforeach ?>
				<select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' name='add' value='Submit' /></td>
		</tr>
	</table>
</form>

<h3>Department List</h3>
<?php if (!is_null($dep_res)): ?>

<form action="<?php echo $this->urlWithQs ?>" method="post">
	<table class='generic-form-table'>
		<button title='Delete' name='take_action' value='delete' onclick="if(!confirm('Are you sure about this?')) return false;" style='background:none;border:none;cursor:pointer'>
		<img src='<?= Flux::config('BaseURI').FLUX_ADDON_DIR.'/support/themes/'.Flux::config('ThemeName').'/img/delete.png' ?>' alt='Delete' border='' /> Delete
		</button>
	</table>

	<?php echo $paginator->infoText() ?>
	<table class='horizontal-table'>
		<tr>
			<th><input type='checkbox' onclick="selectAll(this, 'dep_id')" /></th>
			<th>ID</th>
			<th>Department Name</th>
			<th>Group</th>
			<th>Date Created</th>
			<th>Datetime Updated</th>
		</tr>
		<?php foreach ($dep_res as $row): ?>
		<tr>
			<td style='text-align:center'><input type='checkbox' name='dep_id[]' class='dep_id' value='<?= (int) $row->id ?>' /></td>
			<td style='text-align:center'><?= (int) $row->id ?></td>
			<td style='text-align:center'><?= htmlspecialchars($row->name) ?></td>
			<td style='text-align:center'><?= getGroupName($row->group_id) ?></td>
			<td style='text-align:center'><?= date("F j", strtotime($row->datetime_submitted)) ?></td>
			<td style='text-align:center'><?= date(Flux::config('DateTimeFormat'), strtotime($row->datetime_updated)) ?></td>
		</tr>
		<?php endforeach ?>
	</table>
</form>
<?php echo $paginator->getHTML() ?>
<?php else: ?>
<p><?= htmlspecialchars(Flux::message('NoDepartment')) ?></p>
<?php endif ?>

<script type='text/javascript'>
	function selectAll(source, c) {
	  	checkboxes = document.getElementsByClassName(c);
		  for(var i in checkboxes)
		    checkboxes[i].checked = source.checked;
	}
</script>