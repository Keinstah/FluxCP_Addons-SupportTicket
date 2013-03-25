<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');

$title = Flux::message('SupportEditTitle');

$support_tickets	= Flux::config('FluxTables.support_tickets');
$support_reply 		= Flux::config('FluxTables.support_reply');
$tableName			= "$server->loginDatabase.$support_tickets";
$tableName2			= "$server->loginDatabase.$support_reply";
$ticket_id			= (int) $params->get('id');
$errorMessage		= NULL;
$group_col 			= getGroupCol($server);

if (isset($_POST['save']))
{
	$email			= $params->get('email');
	$subject		= $params->get('subject');
	$department		= (int) $params->get('department');
	$priority		= (int) $params->get('priority');
	$char_id		= (int) $params->get('char');
	$status			= (int) $params->get('status');
	$message		= $params->get('message');
	$subscribe		= (int) $params->get('subscribe');

	$sql = "SELECT email FROM $server->loginDatabase.login WHERE email = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($email));

	// email doesn't exists
	if ($sth->rowCount() === 0)
	{
		$errorMessage = Flux::message('EmailNotExists');
	} else

	// subject doesn't meet the minimum length
	if (strlen($subject) < Flux::config('SubjectMinLen'))
	{
		$errorMessage = sprintf(Flux::message('SubjectMin'), Flux::config('SubjectMinLen'));
	} else

	// subject doesn't meet the maximum length
	if (strlen($subject) > Flux::config('SubjectMaxLen'))
	{
		$errorMessage = sprintf(Flux::message('SubjectMax'), Flux::config('SubjectMaxLen'));
	} else

	// check for subject characters
	if (!preg_match(Flux::config('SubjectChar'), $subject))
	{
		$errorMessage = Flux::message('SubjectChar');
	} else

	// error occured from department
	if (!getDepartment($server, $department))
	{
		$errorMessage = sprintf(Flux::message('SupportError'), 1);
	} else

	// error occured from priority
	if ($priority > 2)
	{
		$errorMessage = sprintf(Flux::message('SupportError'), 2);
	} else {

		// check if character exists
		if ($char_id !== 0)
		{
			$sql = "SELECT char_id FROM $server->loginDatabase.char WHERE char_id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($char_id));

			if ($sth->rowCount() === 0)
			{
				$errorMessage = Flux::message('CharNotExists');
			}
		} else

		// message doesn't meet minimum length
		if (strlen($message) < Flux::config('MessageMinLen'))
		{
			$errorMessage = sprintf(Flux::message('MessageMin'), Flux::config('MessageMinLen'));
		} else

		// message doesn't meet maximum length
		if (strlen($message) > Flux::config('MessageMaxLen'))
		{
			$errorMessage = sprintf(Flux::message('MessageMax'), Flux::config('MessageMaxLen'));
		}
	}

	if (is_null($errorMessage))
	{
		$sql = "UPDATE $tableName SET ";
		$sql .= "email = ?, subject = ?, department = ?, priority = ?, char_id = ?, status = ?, message = ?, subscribe = ?, datetime_updated = ?";
		$sql .= " WHERE id = ?";
		$sth = $server->connection->getStatement($sql);
		$bind = array($email, $subject, $department, $priority, $char_id, $status, $message, $subscribe, date(Flux::config('DateTimeFormat')), $ticket_id);
		$sth->execute($bind);

		if ($sth->rowCount() === 0)
		{
			$errorMessage = Flux::message('FailedToUpdateTicket');
		} else {
			$successMessage = Flux::message('SuccessToUpdateTicket');
		}
	}
}

if (isset($_POST['delete']))
{
	if ($session->account->$group_col < Flux::config('TicketDelGroup'))
	{
		$errorMessage = Flux::message('InsufficientPermission');
	} else {
		$sql = "DELETE FROM $tableName WHERE id = ?";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($ticket_id));

		if ($sth->rowCount() === 0)
		{
			$errorMessage = Flux::message('TicketDeleteFailed');
		} else {

			$sql = "DELETE FROM $tableName2 WHERE ticket_id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($ticket_id));

			$successMessage = Flux::message('TicketDeleteSuccess');
			$this->redirect($this->url('support'));
		}
	}
}

// fetch the ticket by id
$sqlpartial = "WHERE id = ? LIMIT 1";
$bind = array((int) $ticket_id);

$sql  = "SELECT * FROM $tableName $sqlpartial";
$sth  = $server->connection->getStatement($sql);
$sth->execute($bind);
$ticket_res = $sth->fetch();

if ($sth->rowCount() === 0)
{
	$ticket_res = NULL;
} else {
	if ($session->account->account_id != $ticket_res->account_id && $session->account->$group_col < getDepartment($server, $ticket_res->id)->group_id)
	{
		$ticket_res = NULL;
	}
}

if (count($ticket_res))
{
	$sql = "SELECT name, char_id FROM $server->loginDatabase.char WHERE account_id = ? LIMIT 1";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($ticket_res->account_id));

	$char_res = $sth->fetch();
}

?>