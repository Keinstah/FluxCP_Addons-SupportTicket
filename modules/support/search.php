<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');
require_once 'Flux/Mailer.php';

$title 			= Flux::message('SupportSearchTitle');
$support_tickets= Flux::config('FluxTables.support_tickets');
$support_dep 	= Flux::config('FluxTables.support_dep');
$tableName		= "$server->loginDatabase.$support_tickets";
$tableName3		= "$server->loginDatabase.$support_dep";
$errorMessage	= NULL;
$search_res		= NULL;
$group_col 		= getGroupCol($server);

if (isset($_POST['take_action']))
{
	$ticket_ids  = $params->get('ticket_id');
	$action 	= $params->get('take_action');
	$account_id = $session->account->account_id;

	if ( ! count($ticket_ids))
	{
		$errorMessage = Flux::message('NoSelectedTicket');
	} else {
		foreach ($ticket_ids->toArray() as $ticket_id)
		{
			$ticket_id = (int) $ticket_id;
			switch ($action)
			{
				// unsubscribing to the ticket
				case "unsubscribe":
					// user is not unsubscribed
					if (!isSubscribed($ticket_id, $account_id, $server))
					{
						continue;
					} else {

						$sql = "SELECT unsubscribe, account_id FROM $tableName WHERE id = ?";
						$sth = $server->connection->getStatement($sql);
						$sth->execute(array($ticket_id));
						$res = $sth->fetch();

						// not owner of the ticket
						if ($sth->rowCount())
						{
							if ($res->account_id != $account_id)
							{
								$unsubscribe = $res->unsubscribe.",".$account_id;

								$sql = "UPDATE $tableName SET unsubscribe = ? WHERE id = ?";
								$sth = $server->connection->getStatement($sql);
								$sth->execute(array($unsubscribe, $ticket_id));

								if ( ! $sth->rowCount())
								{
									$successMessage = Flux::message('UnsubFailed');
									break;
								} else {
									$successMessage = Flux::message('UnsubSuccess');
								}
							} else {
								$sql = "UPDATE $tableName SET subscribe = 0 WHERE id = ?";
								$sth = $server->connection->getStatement($sql);
								$sth->execute(array($ticket_id));

								if ( ! $sth->rowCount())
								{
									$successMessage = Flux::message('UnsubFailed');
									break;
								} else {
									$successMessage = Flux::message('UnsubSuccess');
								}
							}
						} else {
							$errorMessage = sprintf(Flux::message('SupportError'), 1);
							break;
						}
					}
				break;

				case "subscribe":
					// subscribing to the ticket
					if (isSubscribed($ticket_id, $account_id, $server))
					{
						continue;
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
									break;
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
									break;
								} else {
									$successMessage = Flux::message('SubSuccess');
								}
							}
							
						} else {
							$errorMessage = sprintf(Flux::message('SupportError'), 1);
							break;
						}
					}
				break;

				case "open":
					// opening the support ticket
					$sql = "SELECT status, subscribe, subject, email, priority FROM $tableName WHERE id = ? AND status != 1 LIMIT 1";
					$sth = $server->connection->getStatement($sql);
					$sth->execute(array($ticket_id));

					$res = $sth->fetch();

					if ($session->account->$group_col < Flux::config('TicketOpenGroup'))
					{
						$errorMessage = Flux::message('InsufficientPermission');
						break;
					} else

					if ($sth->rowCount() === 0)
					{
						continue;
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
							break;
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

					if ($session->account->$group_col < Flux::config('TicketCloseGroup'))
					{
						$errorMessage = Flux::message('InsufficientPermission');
						break;
					} else

					if ($sth->rowCount() === 0)
					{
						continue;
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
							break;
						} else {
							$successMessage = Flux::message('TicketCloseSuccess');
						}
					}
				break;

				case "delete":
					// deleting the support ticket
					if ($session->account->$group_col < Flux::config('TicketDelGroup'))
					{
						$errorMessage = Flux::message('InsufficientPermission');
						break;
					} else {
						$sql = "SELECT email, priority, subscribe, subject FROM $tableName WHERE id = ?";
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
							break;
						} else {
							// deleting ticket reply
							$sql = "DELETE FROM $tableName2 WHERE ticket_id = ?";
							$sth = $server->connection->getStatement($sql);
							$sth->execute(array($ticket_id));

							$successMessage = Flux::message('TicketDeleteSuccess');
						}
					}
				break;

				case "resolve":
					$sql = "SELECT status, subscribe, subject, email, priority FROM $tableName WHERE id = ? AND status != 2 LIMIT 1";
					$sth = $server->connection->getStatement($sql);
					$sth->execute(array($ticket_id));

					$res = $sth->fetch();

					if ($session->account->$group_col < Flux::config('TicketResolveGroup'))
					{
						$errorMessage = Flux::message('InsufficientPermission');
						break;
					} else

					if ($sth->rowCount() === 0)
					{
						continue;
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
							break;
						} else {
							$successMessage = Flux::message('TicketResolveSuccess');
						}
					}
				break;

				default:
				break;
			}
		}
	}
}

if (isset($_GET['q']))
{
	$search_query = rawurldecode($params->get('q'));
	$account_id = NULL;

	if (strlen($search_query) < Flux::config('TicketSearchMinLen'))
	{
		$errorMessage = sprintf(Flux::message('TicketSearchMin'), Flux::config('TicketSearchMinLen'));
	} else {

		if ($session->account->$group_col < Flux::config('TicketStaffSearch')) $account_id = (int) $session->account->account_id;
	
		$sql = "WHERE";
		$bind = array();

		$i = 0;
		foreach (explode(" ", $search_query) as $q)
		{
			if ($i != 0) $sql .= " OR";

			$sql .= " subject LIKE ? OR id LIKE ?";
			$count_sql = " subject LIKE ? OR id LIKE ?";

			if ($session->account->$group_col >= Flux::config('TicketStaffSearch'))
			{
				$sql .= " OR account_id LIKE ? OR char_id LIKE ? OR email LIKE ?";
				$count_sql .= " OR account_id LIKE ? OR char_id LIKE ? OR email LIKE ?";
			}

			$qnum = count(explode("LIKE", $count_sql));

			for ($j = 1; $j < $qnum; $j++)
			{
				$bind[] = "%$q%";
			}
			
			$i ++;
		}

		if (!is_null($account_id))
		{
			$sql .= " AND account_id = ?";
			$bind[] = $account_id;
		} else {

			$sqlpartial = "SELECT id FROM $tableName3 WHERE group_id > ?";
			$sth = $server->connection->getStatement($sqlpartial);
			$sth->execute(array($session->account->$group_col));
			$group_res = $sth->fetchAll();

			if ($sth->rowCount() > 0)
			{
				foreach ($group_res as $row)
				{
					$sql .= " AND department != ?";
					$bind[] = $row->id;
				}
			}
		}

		// Get total count and feed back to the paginator.
		$sth = $server->connection->getStatement("SELECT COUNT($support_tickets.id) AS total FROM $tableName $sql");
		$sth->execute($bind);

		Flux::config('ResultsPerPage', Flux::config('TicketPerPage'));
		$paginator = $this->getPaginator($sth->fetch()->total);
		$paginator->setSortableColumns(array('datetime_updated' => 'desc', 'subject', 'status' => 'desc', 'department', 'datetime_submitted', 'priority' => 'desc'));

		// fetch all ticket by user
		$sql  = $paginator->getSQL("SELECT * FROM $tableName $sql");
		$sth  = $server->connection->getStatement($sql);
		$sth->execute($bind);
		$search_res = $sth->fetchAll();

		if ($sth->rowCount() == 0) $search_res = NULL;
	}
}

?>