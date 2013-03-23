<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');
require_once 'Flux/Mailer.php';

Flux::config('MailerFromName', Flux::config('SupportFromName'));

// instantiate mailer
$mail = new Flux_Mailer();

$title = Flux::message('SupportViewTitle');

$support_tickets= Flux::config('FluxTables.support_tickets');
$support_reply 	= Flux::config('FluxTables.support_reply');
$tableName		= "$server->loginDatabase.$support_tickets";
$tableName2		= "$server->loginDatabase.$support_reply";
$ticket_id		= (int) $params->get('id');
$errorMessage	= NULL;

if (isset($_POST['reply']))
{
	$ticket_id	= (int) $params->get('ticket_id');
	$account_id = (int) $params->get('account_id');
	$reply		= $params->get('reply');
	$subscribe	= (int) $params->get('subscribe');
	$status 	= (int) $params->get('status');
	$subject 	= $params->get('subject');
	$priority 	= $params->get('priority');
	$email 		= $params->get('email');

	// fetch ticket owner
	$sql = "SELECT account_id FROM $tableName WHERE id = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($ticket_id));
	$owner_id = (int) $sth->fetch()->account_id;

	// reply doesn't meet the minimum length
	if (strlen($reply) < Flux::config('ReplyMinLen'))
	{
		$errorMessage = sprintf(Flux::message('ReplyMin'), Flux::config('ReplyMinLen'));
	} else

	// reply doesn't meet the maximum length
	if (strlen($reply) > Flux::config('ReplyMaxLen'))
	{
		$errorMessage = sprintf(Flux::message('ReplyMax'), Flux::config('ReplyMaxLen'));
	} else {

		// insert new reply
		$sql = "INSERT INTO $tableName2 VALUES (NULL, ?, ?, ?, ?)";
		$sth = $server->connection->getStatement($sql);
		$bind = array(
			$ticket_id,
			$account_id,
			$reply,
			date(Flux::config('DateTimeFormat')),
		);
		$sth->execute($bind);

		// failed to insert new reply
		if ($sth->rowCount() === 0)
		{
			$errorMessage = sprintf(Flux::message('SupportError'), 1);
		} else {
			// updating status and datetime_updated
			$sql = "UPDATE $tableName SET status = ?, datetime_updated = ?, unread = 1, ticket_read = '' WHERE id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($status, date(Flux::config('DateTimeFormat')), $ticket_id));

			// failed to update status and datetime_updated
			if ($sth->rowCount() === 0)
			{
				$errorMessage = sprintf(Flux::message('SupportError'), 2);
			} else {

				// global subscription is enabled
				if (Flux::config('EnableSubscribing'))
				{
					// ticket owner subscription is enabled
					// the one thats replying must not be the owner of the ticket
					if ((int) $subscribe === 1 && $owner_id != $account_id)
					{
						// sending mail
						$from = "<strong>".getNickname($account_id, $server)."</strong> replied to your ticket.";
						$sent = $mail->send($email, "Reply - [Ticket ID: {$ticket_id}] {$subject}", 'ticket_reply', 
							array(
								'Message' => $reply,
								'From' => $from,
								'Subject' => htmlspecialchars($subject), 
								'Priority' => getPriority($priority),
								'Status' => getStatus($status),
								'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
							)
						);

						// failed to send the mail
						if (!$sent) $errorMessage = sprintf(Flux::message('SupportError'), 3);
					}

					// fetch all the gm accounts details
					$sql = "SELECT account_id, email FROM $server->loginDatabase.login WHERE group_id >= ?";
					$sth = $server->connection->getStatement($sql);
					$sth->execute(array(AccountLevel::LOWGM));
					$account_res = $sth->fetchAll();

					if ($sth->rowCount() > 0)
					{
						foreach ($account_res as $row)
						{
							if (isSubscribed($ticket_id, $row->account_id, $server))
							{
								$sql = "SELECT email FROM $server->loginDatabase.cp_support_settings WHERE account_id = ? LIMIT 1";
								$sth = $server->connection->getStatement($sql);
								$sth->execute(array($row->account_id));
								$settings_res = $sth->fetch();

								$email = $row->email;
								if ($sth->rowCount() > 0 && !is_null($settings_res->email)) $email = $settings_res->email;
								$sent = $mail->send($email, "Reply - [Ticket ID: {$ticket_id}] {$subject}", 'ticket_staff', 
									array(
										'Message' => $reply,
										'Subject' => htmlspecialchars($subject), 
										'Priority' => getPriority($priority),
										'Status' => getStatus($status),
										'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
									)
								);
							}
						}
					}
				}
			}
		}
	}
}

// deleting reply
if (isset($_POST['delete_reply']))
{
	$reply_id = (int) $params->get('delete_reply');

	if ($session->account->group_id < AccountLevel::LOWGM)
	{
		$errorMessage = Flux::message('InsufficientPermission');
	} else {

		$sql = "DELETE FROM $tableName2 WHERE id = ?";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($reply_id));

		if ($sth->rowCount() === 0)
		{
			$errorMessage = Flux::message('ReplyDeleteFailed');
		} else {

			$successMessage = Flux::message('ReplyDeleteSuccess');
		}
	}
}

if (isset($_POST['take_action']))
{
	$ticket_id 	= (int) $params->get('ticket_id');
	$account_id	= (int) $session->account->account_id;
	$action 	= $params->get('take_action');

	switch ($action)
	{
		// unsubscribing to the ticket
		case "unsubscribe":
			// user is not unsubscribed
			if (!isSubscribed($ticket_id, $account_id, $server))
			{
				$successMessage = Flux::message('AlreadyUnsubscribed');
			} else {

				$sql = "SELECT unsubscribe, account_id FROM $tableName WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));
				$res = $sth->fetch();

				// not owner of the ticket
				if ($sth->rowCount() === 1)
				{
					if ($res->account_id != $account_id)
					{
						$unsubscribe = $res->unsubscribe.",".$account_id;

						$sql = "UPDATE $tableName SET unsubscribe = ? WHERE id = ?";
						$sth = $server->connection->getStatement($sql);
						$sth->execute(array($unsubscribe, $ticket_id));

						if ($sth->rowCount() === 0)
						{
							$successMessage = Flux::message('UnsubFailed');
						} else {
							$successMessage = Flux::message('UnsubSuccess');
						}
					} else {
						$sql = "UPDATE $tableName SET subscribe = 0 WHERE id = ?";
						$sth = $server->connection->getStatement($sql);
						$sth->execute(array($ticket_id));

						if ($sth->rowCount() === 0)
						{
							$successMessage = Flux::message('UnsubFailed');
						} else {
							$successMessage = Flux::message('UnsubSuccess');
						}
					}
				} else {
					$errorMessage = sprintf(Flux::message('SupportError'), 1);
				}
			}
		break;

		case "subscribe":
			// subscribing to the ticket
			if (isSubscribed($ticket_id, $account_id, $server))
			{
				$successMessage = Flux::message('AlreadySubscribed');
			} else {

				$sql = "SELECT unsubscribe, account_id FROM $tableName WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));
				$res = $sth->fetch();

				// not owner of the ticket
				if ($sth->rowCount() === 1)
				{
					if ($res->account_id != $account_id)
					{
						$unsubscribe = $res->unsubscribe;

						if (preg_match("/,".$account_id."/", $unsubscribe))
						{
							$unsubscribe = str_replace(",".$account_id, "", $unsubscribe);
						} else {
							$unsubscribe = str_replace($account_id, "", $unsubscribe);
						}

						$sql = "UPDATE $tableName SET unsubscribe = ? WHERE id = ?";
						$sth = $server->connection->getStatement($sql);
						$sth->execute(array($unsubscribe, $ticket_id));

						if ($sth->rowCount() === 0)
						{
							$errorMessage = Flux::message('SubFailed');
						} else {
							$successMessage = Flux::message('SubSuccess');
						}
					} else {
						$sql = "UPDATE $tableName SET subscribe = 1 WHERE id = ?";
						$sth = $server->connection->getStatement($sql);
						$sth->execute(array($ticket_id));

						if ($sth->rowCount() === 0)
						{
							$errorMessage = Flux::message('SubFailed');
						} else {
							$successMessage = Flux::message('SubSuccess');
						}
					}
					
				} else {
					$errorMessage = sprintf(Flux::message('SupportError'), 1);
				}
			}
		break;

		case "open":
			// opening the support ticket
			$sql = "SELECT status, subscribe, subject, email, priority FROM $tableName WHERE id = ? AND status != 1 LIMIT 1";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($ticket_id));

			$res = $sth->fetch();

			if ($session->account->group_id < Flux::config('TicketOpenGroup'))
			{
				$errorMessage = Flux::message('InsufficientPermission');
			} else

			if ($sth->rowCount() === 0)
			{
				$successMessage = Flux::message('TicketAlreadyOpen');
			} else {
				// user is subscribed
				if ($res->subscribe == 1)
				{
					$sent = $mail->send($res->email, "[Ticket ID: {$ticket_id}] {$res->subject}", 'ticket_open', 
						array(
							'Message' => sprintf(Flux::message('TicketMarked'), "Opened"),
							'Subject' => htmlspecialchars($res->subject), 
							'Priority' => getPriority($res->priority),
							'Status' => getStatus(1),
							'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
						)
					);
				}

				$sql = "UPDATE $tableName SET status = 1 WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));

				if ($sth->rowCount() === 0)
				{
					$errorMessage = Flux::message('TicketOpenFailed');
				} else {
					$successMessage = Flux::message('TicketOpenSuccess');
				}
			}
		break;

		case "close":
			// closing the support ticket
			$sql = "SELECT status, subscribe, subject, email, priority FROM $tableName WHERE id = ? AND status != 0 LIMIT 1";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($ticket_id));

			$res = $sth->fetch();

			if ($session->account->group_id < Flux::config('TicketCloseGroup'))
			{
				$errorMessage = Flux::message('InsufficientPermission');
			} else

			if ($sth->rowCount() === 0)
			{
				$successMessage = Flux::message('TicketAlreadyClose');
			} else {

				// user is subscribed
				if ($res->subscribe == 1)
				{

					$sent = $mail->send($res->email, "[Ticket ID: {$ticket_id}] {$res->subject}", 'ticket_open', 
						array(
							'Message' => sprintf(Flux::message('TicketMarked'), "Closed"),
							'Subject' => htmlspecialchars($res->subject), 
							'Priority' => getPriority($res->priority),
							'Status' => getStatus(0),
							'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
						)
					);
				}

				$sql = "UPDATE $tableName SET status = 0 WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));

				if ($sth->rowCount() === 0)
				{
					$errorMessage = Flux::message('TicketCloseFailed');
				} else {
					$successMessage = Flux::message('TicketCloseSuccess');
				}
			}
		break;

		case "delete":
			// deleting the support ticket
			if ($session->account->group_id < Flux::config('TicketDelGroup'))
			{
				$errorMessage = Flux::message('InsufficientPermission');
			} else {
				$sql = "SELECT email, priority, subscribe, subject FROM $server->loginDatabase.$tableName WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));
				$res = $sth->fetch();

				// user is subscribed
				if ($res->subscribe == 1)
				{

					$sent = $mail->send($res->email, "[Ticket ID: {$ticket_id}] {$res->subject}", 'ticket_open', 
						array(
							'Message' => Flux::message('TicketDeleted'),
							'Subject' => htmlspecialchars($res->subject), 
							'Priority' => getPriority($res->priority),
							'Status' => getStatus(2),
							'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
						)
					);
				}

				$sql = "DELETE FROM $tableName WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));

				if ($sth->rowCount() === 0)
				{
					$errorMessage = Flux::message('TicketDeleteFailed');
				} else {
					// deleting ticket reply
					$sql = "DELETE FROM $tableName2 WHERE ticket_id = ?";
					$sth = $server->connection->getStatement($sql);
					$sth->execute(array($ticket_id));

					$successMessage = Flux::message('TicketDeleteSuccess');
					$this->redirect($this->url('support'));
				}
			}
		break;

		case "resolve":
			$sql = "SELECT status, subscribe, subject, email, priority FROM $tableName WHERE id = ? AND status != 2 LIMIT 1";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($ticket_id));

			$res = $sth->fetch();

			if ($session->account->group_id < Flux::config('TicketResolveGroup'))
			{
				$errorMessage = Flux::message('InsufficientPermission');
			} else

			if ($sth->rowCount() === 0)
			{
				$successMessage = Flux::message('TicketAlreadyResolve');
			} else {

				// user is subscribed
				if ($res->subscribe == 1)
				{

					$sent = $mail->send($res->email, "[Ticket ID: {$ticket_id}] {$res->subject}", 'ticket_open', 
						array(
							'Message' => sprintf(Flux::message('TicketMarked'), "Closed"),
							'Subject' => htmlspecialchars($res->subject), 
							'Priority' => getPriority($res->priority),
							'Status' => getStatus(0),
							'URL' => "http://".Flux::config('ServerAddress').getURL($ticket_id, $this->url('support', 'view')),
						)
					);
				}

				$sql = "UPDATE $tableName SET status = 2 WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_id));

				if ($sth->rowCount() === 0)
				{
					$errorMessage = Flux::message('TicketResolveFailed');
				} else {
					$successMessage = Flux::message('TicketResolveSuccess');
				}
			}
		break;

		default:
		break;
	}
}

// fetch the ticket by user
$sql = "SELECT * FROM $tableName WHERE id = ?";
$sth = $server->connection->getStatement($sql);
$sth->execute(array($ticket_id));

$ticket_res = $sth->fetch();

if ($sth->rowCount() === 0) $ticket_res = NULL;

// set read
if (!is_null($ticket_res))
{
	if ($ticket_res->account_id != $session->account->account_id)
	{
		if ($session->account->group_id < getDepartment($server, $ticket_res->department)->group_id)
		{
			$ticket_res = NULL;
		} else {

			// check if the ticket has been read yet 
			$ticket_read = explode(",", $ticket_res->ticket_read);
			if (!in_array($session->account->account_id, $ticket_read))
			{
				$ticket_read = $ticket_res->ticket_read.",".$session->account->account_id;

				$sql = "UPDATE $tableName SET ticket_read = ? WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($ticket_read, $ticket_id));
			}
		}
	} else {

		if ($ticket_res->unread != 0)
		{
			$sql = "UPDATE $tableName SET unread = 0 WHERE id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($ticket_id));
		}
	}
}

// set reply
$reply_res = NULL;
if (!is_null($ticket_res))
{
	$sqlpartial = "WHERE ticket_id = ?";
	$bind = array((int) $ticket_res->id);

	// Get total count and feed back to the paginator.
	$sth = $server->connection->getStatement("SELECT COUNT($support_reply.id) AS total FROM $tableName2 $sqlpartial ORDER BY datetime_submitted DESC");
	$sth->execute($bind);

	// set perpage
	Flux::config('ResultsPerPage', Flux::config('ReplyPerPage'));
	$paginator = $this->getPaginator($sth->fetch()->total);

	// fetch all reply by user
	$sql  = $paginator->getSQL("SELECT * FROM $tableName2 $sqlpartial ORDER BY datetime_submitted DESC");
	$sth  = $server->connection->getStatement($sql);
	$sth->execute($bind);
	$reply_res = $sth->fetchAll();
}

?>