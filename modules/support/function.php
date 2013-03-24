<?php if (!defined('FLUX_ROOT')) exit;

/*
 * @desc - To get the time left of the timestamp
 * @params (string) $ts
 */
if (!function_exists("getTimeLeft"))
{
	function getTimeLeft($ts)
	{
		if (is_numeric($ts) && $ts < time()) return FALSE;

		if (!is_numeric($ts)) $ts = strtotime($ts);

		$time = $ts - time();
		$temp = $time / 86400;

		// set days
		$days = floor($temp);
		$temp = 24*($temp-$days);

		// set hours
		$hours = floor($temp);
		$temp = 60*($temp-$hours);

		// set minutes
		$minutes = floor($temp);
		$temp = 60*($temp-$minutes);

		// set seconds
		$seconds = floor($temp);

		if ($days > 0)
		{
			return $days.($days > 1 ? " days left" : " day left");
		} else
		if ($hours > 0)
		{
			return $hours.($hours > 1 ? " hours left" : " hour left");
		} else
		if ($minutes > 0)
		{
			return $minutes.($minutes > 1 ? " minutes left" : " minute left");
		} else {
			return $seconds.($seconds > 1 ? " seconds left" : " second left");
		}
	}
}

/*
 * @desc - To get the priority of the ticket
 * @params (int) $id
 */
if (!function_exists("getPriority"))
{
	function getPriority($id)
	{
		switch ($id)
		{
			case 0:
				return "Low";
				break;
			case 1:
				return "Medium";
				break;
			case 2:
				return "High";
				break;
			default:
				return FALSE;
				break;
		}
	}
}

/*
 * @desc - To get the status of a ticket
 * @params (int) $id
 */
if (!function_exists("getStatus"))
{
	function getStatus($id)
	{
		switch ($id)
		{
			case 0:
				return "<span style='color:red;font-weight:bold'>Closed</span>";
				break;
			case 1:
				return "<span style='color:#3fd03f;font-weight:bold'>Open</span>";
				break;
			case 2:
				return "<span style='color:#666;font-weight:bold'>Resolved</span>";
				break;
			case 3:
				return "<span style='color:#f7c763;font-weight:bold'>Replied</span>";
				break;
			default:
				return FALSE;
				break;
		}
	}
}

/*
 * @desc - To get the url with id
 * @params (int) $id, (string) $url
 */
if (!function_exists("getURL"))
{
	function getURL($id, $url)
	{
		if (Flux::config('UseCleanUrls'))
		{
			$url .= "?id=".$id;
		} else {
			$url .= "&id=".$id;
		}

		return $url;
	}
}

/*
 * @desc - To get the department name
 * @params (int) $id
 */
if (!function_exists("getDepartment"))
{
	function getDepartment($server, $id = NULL)
	{
		$support_dep 	= Flux::config('FluxTables.support_dep');
		$tableName3		= "$server->loginDatabase.$support_dep";

		$sql = "SELECT * FROM $tableName3";

		if (!is_null($id))
		{
			$sql .= " WHERE id = ?";
			$bind = array((int)$id);
		} else {
			$bind = array();
		}

		$sth = $server->connection->getStatement($sql);
		$sth->execute($bind);

		if ($sth->rowCount() === 0) return FALSE;
		if (!is_null($id) && $sth->rowCount() === 1) return $sth->fetch()	;
		return $sth->fetchAll();
	}
}

/*
 * @desc - To get the Group Name
 * @params (int) $id
 */
if (!function_exists("getGroupName"))
{
	function getGroupName($id)
	{
		$groups = array(AccountLevel::LOWGM => "LOWGM", AccountLevel::HIGHGM => "HIGHGM", AccountLevel::ADMIN => "ADMIN");

		return $groups[$id];
	}
}

/*
 * @desc - To get the nickname of the account_id
 * @params (int) $account_id, (object) $server
 */
if (!function_exists("getNickname"))
{
	function getNickname($account_id, $server)
	{
		$support_settings = Flux::config('FluxTables.support_settings');

		$sql = "SELECT nickname FROM $server->loginDatabase.$support_settings WHERE account_id = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($account_id));

		if ($sth->rowCount() === 0)
		{
			$sql = "SELECT userid FROM $server->loginDatabase.login WHERE account_id = ? LIMIT 1";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($account_id));

			if ($sth->rowCount() === 0)
			{
				return "None";
			} else {

				return $sth->fetch()->userid;	
			}
		} else {

			return $sth->fetch()->nickname;
		}
	}
}

/*
 * @desc - To check if the ticket has been read yet
 * @params (int) $account_id, (int) $ticket_id, (object) $server
 */
if (!function_exists("isRead"))
{
	function isRead($account_id, $group_id, $ticket_id, $server)
	{
		$support_tickets= Flux::config('FluxTables.support_tickets');
		$support_reply 	= Flux::config('FluxTables.support_reply');
		$support_dep 	= Flux::config('FluxTables.support_dep');
		$tableName		= "$server->loginDatabase.$support_tickets";
		$tableName2		= "$server->loginDatabase.$support_reply";
		$tableName3		= "$server->loginDatabase.$support_dep";

		$sql = "SELECT * FROM $tableName WHERE id = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$bind = array((int) $ticket_id);
		$sth->execute($bind);
		$ticket_res = $sth->fetch();

		if ($ticket_res->account_id != $account_id && $group_id >= AccountLevel::LOWGM)
		{
			$sql_group = "SELECT id FROM $tableName3 WHERE group_id <= ? AND id = ?";
			$sth = $server->connection->getStatement($sql_group);
			$sth->execute(array($group_id, $ticket_res->department));

			if ($sth->rowCount() === 0)
			{
				return TRUE;
			} else {
				$ticket_read = explode(",", $ticket_res->ticket_read);

				if (count($ticket_read) !== 0)
				{
					if (in_array($account_id, $ticket_read)) return TRUE;
					return FALSE;
				}
			}
		} else {
			if ($ticket_res->unread == 1) return FALSE;
			return TRUE;
		}
	}
}

/*
 * @desc - To get the char name of the affected row
 * @params (int) $char_id, (object) $server
 */
if (!function_exists("getCharAffected"))
{
	function getCharAffected($char_id, $server)
	{
		// char_id is invalid
		if ((int)$char_id === 0) return FALSE;

		$sql = "SELECT name FROM $server->loginDatabase.char WHERE char_id = ?";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array((int) $char_id));

		// row doesn't exists
		if ($sth->rowCount() === 0) return FALSE;

		// return the char name
		return $sth->fetch()->name; 
	}
}

/*
 * @desc - To get the group_id of account_id
 * @params (int) $account_id, (object) $server
 */
if (!function_exists("getGroupID"))
{
	function getGroupID($account_id, $server)
	{
		// account_id is invalid
		if ((int)$account_id === 0) return FALSE;

		$col = ($server->isRenewal ? "group_id" : "level");
		$sql = "SELECT $col FROM $server->loginDatabase.login WHERE account_id = ?";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array((int) $account_id));

		// row doesn't exists
		if ($sth->rowCount() === 0) return FALSE;

		// return the char name
		return $sth->fetch()->$col; 
	}
}

/*
 * @desc - To check if the user is subscribed to the ticket
 * @params (int) $ticket_id, (int) $account_id, (object) $server
 */
if (!function_exists("isSubscribed"))
{
	function isSubscribed($ticket_id, $account_id, $server)
	{
		$support_tickets = Flux::config('FluxTables.support_tickets');
		$support_dep 	 = Flux::config('FluxTables.support_dep');

		// account_id is invalid
		if ((int)$account_id === 0) return FALSE;
		if ((int)$ticket_id === 0) return FALSE;

		$sql = "SELECT group_id FROM $server->loginDatabase.login WHERE account_id = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array((int) $account_id));

		if ($sth->rowCount() === 0) return FALSE;

		if ($sth->fetch()->group_id >= AccountLevel::LOWGM)
		{
			$sql = "SELECT subscribe FROM $server->loginDatabase.$support_dep WHERE account_id = ? LIMIT 1";
			$sth->execute(array((int) $account_id));

			if ($sth->rowCount() === 0) return FALSE;
			if ($sth->fetch()->subscribe == 1) return TRUE;
		}

		$sql = "SELECT account_id, unsubscribe, subscribe FROM $server->loginDatabase.$support_tickets WHERE id = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array((int) $ticket_id));

		if ($sth->rowCount() === 1)
		{
			$res = $sth->fetch();
			if ($res->account_id == $account_id)
			{
				if ($res->subscribe == 1)
				{
					return TRUE;
				} else {
					return FALSE;
				}
			}
			
			if (is_null($res->unsubscribe)) return TRUE;

			$unsubscribe = explode(",", $res->unsubscribe);
			if (in_array($account_id, $unsubscribe)) return FALSE;

			return TRUE;
		} else {

			return FALSE;
		}
		

	}
}

function getUnread($account_id, $server, $group_id) {
	$support_tickets= Flux::config('FluxTables.support_tickets');
	$support_reply 	= Flux::config('FluxTables.support_reply');
	$support_dep 	= Flux::config('FluxTables.support_dep');
	$tableName		= "$server->loginDatabase.$support_tickets";
	$tableName2		= "$server->loginDatabase.$support_reply";
	$tableName3		= "$server->loginDatabase.$support_dep";


	// fetch all ticket id
	$sql = "SELECT id FROM $tableName WHERE ";
	$bind = array();
	if ($group_id < AccountLevel::LOWGM)
	{
		$sql .= "account_id = ?";
		$bind[] = $account_id;
	} else {
		$sql .= "1";
	}
	$sth = $server->connection->getStatement($sql);
	$sth->execute($bind);

	if ($sth->rowCount() === 0) return "0";
	$num = 0;
	
	foreach ($sth->fetchAll() as $row)
	{
		if (!isRead($account_id, $group_id, $row->id, $server))
		{
			$num++;
		}
	}

	return (int) $num;
};
$unread = 0;
if ($session->isLoggedIn()) $unread = getUnread($session->account->account_id, $session->loginAthenaGroup, $session->account->group_id);

?>