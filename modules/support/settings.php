<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');

$title = Flux::message('SupportSettingsTitle');

$support_tickets 		= Flux::config('FluxTables.support_tickets');
$support_settings 	= Flux::config('FluxTables.support_settings');
$errorMessage		= NULL;

if (isset($_POST['account_id']))
{
	$account_id = (int) $params->get('account_id');
	$nickname	= $params->get('nickname');
	$email		= $params->get('email');
	$subscribe	= ($params->get('subscribe') === 'on' ? 1 : 0);
	$updated 	= NULL;

	if ($nickname === "~") $nickname = $session->account->userid;
	if ($email === "~") $email = $session->account->email;

	// updating nickname
	if ($nickname !== "")
	{
		$sql = "SELECT nickname FROM $server->loginDatabase.$support_settings WHERE nickname = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($nickname));
		$nickname_exists = $sth->rowCount();

		$sql = "SELECT nickname FROM $server->loginDatabase.$support_settings WHERE nickname = ? AND account_id = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($nickname, $account_id));
		$same_nickname = $sth->rowCount();

		// nickname is taken
		if ($same_nickname === 0 && $nickname_exists === 1)
		{
			$errorMessage = Flux::message('NicknameAlreadyTaken');
		} else

		// nickname too short
		if (strlen($nickname) < Flux::config('NicknameMinLen'))
		{
			$errorMessage = sprintf(Flux::message('NicknameMin'), Flux::config('NicknameMinLen'));
		} else

		// nickname too long
		if (strlen($nickname) > Flux::config('NicknameMaxLen'))
		{
			$errorMessage = sprintf(Flux::message('NicknameMax'), Flux::config('NicknameMaxLen'));
		} else

		// nickname invalid chars
		if (!preg_match(Flux::config('NicknameChar'), $nickname))
		{
			$errorMessage = Flux::message('NicknameChar');
		} else 

		// nickname is not the same
		if ($same_nickname === 0)
		{

			// updating the value
			$sql = "UPDATE $server->loginDatabase.$support_settings SET nickname = ?, last_updated = ? WHERE account_id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($nickname, date(Flux::config('DateTimeFormat')), $account_id));

			// user doesn't have a record
			if ($sth->rowCount() === 0)
			{
				// inserting new record intead
				$sql = "INSERT INTO $server->loginDatabase.$support_settings VALUES (NULL, ?, ?, NULL, ?, NULL)";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($account_id, $nickname, date(Flux::config('DateTimeFormat'))));

				// there's something wrong
				if ($sth->rowCount() === 0)
				{
					$errorMessage = sprintf(Flux::message('SupportError'), 1);
				} else {
					$updated = 1;
				}
			} else {
				$updated = 1;
			}
		}
	}

	// updating email
	if ($email !== "" && is_null($errorMessage))
	{
		$sql = "SELECT email FROM $server->loginDatabase.$support_settings WHERE email = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($email));
		$email_exists = $sth->rowCount();

		$sql = "SELECT email FROM $server->loginDatabase.$support_settings WHERE email = ? AND account_id = ? LIMIT 1";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($email, $account_id));
		$same_email = $sth->rowCount();

		// email is already taken
		if ($same_email === 0 && $email_exists === 1)
		{
			$errorMessage = Flux::message('EmailAlreadyRegistered');
		} else

		// email is invalid
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$errorMessage = Flux::message('EmailInvalid');
		} else 

		// email is not the same
		if ($same_email === 0)
		{

			// updating the value
			$sql = "UPDATE $server->loginDatabase.$support_settings SET email = ?, last_updated = ? WHERE account_id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($email, date(Flux::config('DateTimeFormat')), $account_id));

			// user doesn't have a record
			if ($sth->rowCount() === 0)
			{
				// inserting new record instead
				$sql = "INSERT INTO $server->loginDatabase.$support_settings VALUES (NULL, ?, NULL, NULL, ?, ?)";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($account_id, date(Flux::config('DateTimeFormat')), $email));

				// there's something wrong
				if ($sth->rowCount() === 0)
				{
					$errorMessage = sprintf(Flux::message('SupportError'), 2);
				} else {
					$updated = 1;
				}
			} else {
				$updated = 1;
			}
		}
	}

	if (is_null($errorMessage))
	{
		$sql = "SELECT id FROM $server->loginDatabase.$support_settings WHERE account_id = ? AND subscribe = ?";
		$sth = $server->connection->getStatement($sql);
		$sth->execute(array($account_id, $subscribe));

		// subscibe has changed or record doesn't exists
		if ($sth->rowCount() === 0)
		{
			// updating the value
			$sql = "UPDATE $server->loginDatabase.$support_settings SET subscribe = ?, last_updated = ? WHERE account_id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($subscribe, date(Flux::config('DateTimeFormat')), $account_id));

			// user doesn't have a record
			if ($sth->rowCount() === 0)
			{
				// inserting new record instead
				$sql = "INSERT INTO $server->loginDatabase.$support_settings VALUES (NULL, ?, NULL, ?, ?, NULL)";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array($account_id, $subscribe, date(Flux::config('DateTimeFormat'))));

				// there's something wrong
				if ($sth->rowCount() === 0)
				{
					$errorMessage = sprintf(Flux::message('SupportError'), 3);
				} else {
					$updated = 1;
				}
			} else {
				$updated = 1;
			}
		}
	}

	if (is_null($errorMessage) && $updated)
	{
		$successMessage = Flux::message('SupportSettingsUpdated');
	}
}

$account_id			= (int) $session->account->account_id;
$settings_res		= NULL;
$nickname 	= NULL;
$email 		= NULL;
$subscribe 	= NULL;
$last_updated = NULL;

$sql = "SELECT * FROM $server->loginDatabase.$support_settings WHERE account_id = ? LIMIT 1";
$sth = $server->connection->getStatement($sql);
$sth->execute(array($account_id));

if ($sth->rowCount() !== 0)
{
	$settings_res = $sth->fetch();

	if (!is_null($settings_res->nickname)) 
	{
		$nickname = $settings_res->nickname;
	}

	if (!is_null($settings_res->email)) 
	{
		$email = $settings_res->email;
	}

	if (!is_null($settings_res->subscribe)) 
	{
		$subscribe = $settings_res->subscribe;
	}

	if (!is_null($settings_res->last_updated))
	{
		$last_updated = $settings_res->last_updated;
	}
}

?>