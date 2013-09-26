<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');
require_once 'Flux/Mailer.php';
Flux::config('MailerFromName', Flux::config('SupportFromName'));
$mail = @new Flux_Mailer();
$group_col = getGroupCol($server);

$title = Flux::message('SupportCreateTitle');

$support_tickets= Flux::config('FluxTables.support_tickets');
$account_id 	= $session->account->account_id;
$unavailable 	= NULL;
$timestamp		= NULL;
$errorMessage	= NULL;

if (isset($_POST['account_id']))
{
	$email			= $params->get('email');
	$account_id		= (int) $params->get('account_id');
	$subject		= $params->get('subject');
	$department		= (int) $params->get('department');
	$priority		= (int) $params->get('priority');
	$char_id		= (int) $params->get('char');
	$message 		= $params->get('message');
	$subscribe		= (int) $params->get('subscribe');
	$status 		= 1;

	// subject doesn't meet the minimum length
	if (strlen($subject) < Flux::config('SubjectMinLen'))
		$errorMessage = sprintf(Flux::message('SubjectMin'), Flux::config('SubjectMinLen'));
	else
	// subject doesn't meet the maximum length
	if (strlen($subject) > Flux::config('SubjectMaxLen'))
		$errorMessage = sprintf(Flux::message('SubjectMax'), Flux::config('SubjectMaxLen'));
	else
	// check for subject characters
	if (!preg_match(Flux::config('SubjectChar'), $subject))
		$errorMessage = Flux::message('SubjectChar');
	else
	// error occured from department
	if (!getDepartment($server, $department))
		$errorMessage = sprintf(Flux::message('SupportError'), 1);
	else
	// error occured from priority
	if ($priority > 2)
		$errorMessage = sprintf(Flux::message('SupportError'), 2);
	else {

		// check if character exists
		if ($char_id)
		{
			$sql = "SELECT char_id FROM $server->loginDatabase.char WHERE char_id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($char_id));

			if ( ! $sth->rowCount())
			{
				$errorMessage = Flux::message('CharNotExists');
			}
		} else

		// message doesn't meet minimum length
		if (strlen($message) < Flux::config('MessageMinLen'))
			$errorMessage = sprintf(Flux::message('MessageMin'), Flux::config('MessageMinLen'));
		else
		// message doesn't meet maximum length
		if (strlen($message) > Flux::config('MessageMaxLen'))
			$errorMessage = sprintf(Flux::message('MessageMax'), Flux::config('MessageMaxLen'));
		else
		if (Flux::config('TicketDelay'))
		{
			// check if already submitted a ticket
			$sql = "SELECT datetime_submitted FROM $server->loginDatabase.$support_tickets WHERE account_id = ? ORDER BY id DESC LIMIT 1";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array((int) $account_id));

			if ($sth->rowCount())
			{
				$timestamp = $sth->fetch()->datetime_submitted;

				if (strtotime("+".Flux::config('TicketDelay').' hours', strtotime($timestamp)) > time())
					$errorMessage = sprintf(Flux::message('TicketAlreadySubmitted'), getTimeLeft(strtotime("+".Flux::config('TicketDelay').' hours', strtotime($timestamp))));
			}
		}
	}

	// check for errormessages
	if (is_null($errorMessage))
	{
		$sql = "INSERT INTO $server->loginDatabase.$support_tickets VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, 0)";
		$sth = $server->connection->getStatement($sql);
		$bind = array(
			$account_id,
			$email,
			$char_id,
			$subject,
			$department,
			$priority,
			$message,
			date(Flux::config('DateTimeFormat')),
			1,
			date(Flux::config('DateTimeFormat')),
			$subscribe,
			$account_id,
		);
		$sth->execute($bind);

		if ( ! $sth->rowCount())
		{
			$errorMessage = sprintf(Flux::message('SupportError'), 3);
		} else {
			$sql = "SELECT id FROM $server->loginDatabase.$support_tickets WHERE account_id = ? ORDER BY id DESC LIMIT 1";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($account_id));
			$ticket_id = $sth->fetch()->id;


			if (Flux::config('EnableSubscribing'))
			{
				$sql = "SELECT * FROM $server->loginDatabase.login WHERE $group_col >= ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array(AccountLevel::LOWGM));
				$account_res = $sth->fetchAll();

				foreach ($account_res as $row)
				{
					if (isSubscribed($ticket_id, $row->account_id, $server))
					{
						$sent = $mail->send($row->email, "[Ticket ID: {$ticket_id}] {$subject}", 'ticket_staff', 
							array(
								'Message' => $message,
								'Subject' => htmlspecialchars($subject), 
								'Priority' => getPriority($priority),
								'Status' => getStatus($status),
								'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
							)
						);
					}
				}
			}

			if (Flux::config('EnableSubscribing') && $subscribe == 1)
			{
				$sent = $mail->send($email, "[Ticket ID: {$ticket_id}] {$subject}", 'ticket_open', 
					array(
						'Message' => Flux::config('TicketMailMessage'),
						'Subject' => htmlspecialchars($subject), 
						'Priority' => getPriority($priority),
						'Status' => getStatus($status),
						'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
					)
				);

				if ($sent)
					$this->redirect(getURL($ticket_id, $this->url('support', 'view')));
				else
					$errorMessage = sprintf(Flux::message('SupportError'), 4);

			} else {
				$this->redirect(getURL($ticket_id, $this->url('support', 'view')));
			}
		}
	}
}

if (Flux::config('TicketDelay') > 0)
{
	// check if already submitted a ticket
	$sql = "SELECT datetime_submitted FROM $server->loginDatabase.$support_tickets WHERE account_id = ? ORDER BY id DESC LIMIT 1";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array((int) $account_id));

	if ($sth->rowCount())
	{
		$timestamp = $sth->fetch()->datetime_submitted;

		if (strtotime("+".Flux::config('TicketDelay').' hours', strtotime($timestamp)) > time())
			$unavailable = true;
	}
}

// get all chars
$sql = "SELECT * FROM $server->loginDatabase.char WHERE account_id = ?";
$sth = $server->connection->getStatement($sql);
$sth->execute(array((int) $account_id));
$char_res = $sth->fetchAll();
?>