<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');

$title = Flux::message('SupportDepartTitle');

$support_tickets= Flux::config('FluxTables.support_tickets');
$support_reply 	= Flux::config('FluxTables.support_reply');
$support_dep 	= Flux::config('FluxTables.support_dep');
$tableName		= "$server->loginDatabase.$support_tickets";
$tableName2		= "$server->loginDatabase.$support_reply";
$tableName3		= "$server->loginDatabase.$support_dep";
$errorMessage	= NULL;
$group_col 		= getGroupCol($server);

$groups = array("LOWGM" => AccountLevel::LOWGM, "HIGHGM" => AccountLevel::HIGHGM, "ADMIN" => AccountLevel::ADMIN);

if (isset($_POST['add']))
{
	$name 		= $params->get('name');
	$group_id	= $params->get('group_id');
	$action 	= $params->get('take_action');

	$sql = "SELECT id FROM $tableName3 WHERE name = ?";
	$sth = $server->connection->getStatement($sql);
	$sth->execute(array($name));

	if ($sth->rowCount())
		$errorMessage = Flux::message('DepNameExists');
	else
	if (strlen($name) < Flux::config('DepNameMinLen'))
		$errorMessage = sprintf(Flux::message('DepNameMin'), Flux::config('DepNameMinLen'));
	else
	if (strlen($name) > Flux::config('DepNameMaxLen'))
		$errorMessage = sprintf(Flux::message('DepNameMax'), Flux::config('DepNameMaxLen'));
	else {

		if ($action == 'new')
		{
			$sql = "INSERT INTO $tableName3 VALUES (NULL, ?, ?, ?, ?)";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($name, $group_id, date(Flux::config('DateTimeFormat')), date(Flux::config('DateTimeFormat'))));

			if ( ! $sth->rowCount())
				$errorMessage = Flux::message('DepNameFailed');
			else
				$successMessage = Flux::message('DepNameSuccess');
		} else {

			$dep_id = (int) $action;

			$sql = "UPDATE $tableName3 SET name = ?, group_id = ?, datetime_updated = ? WHERE id = ?";
			$sth = $server->connection->getStatement($sql);
			$sth->execute(array($name, $group_id, date(Flux::config('DateTimeFormat')), $dep_id));

			if ( ! $sth->rowCount())
				$errorMessage = Flux::message('DepUpdateFailed');
			else
				$successMessage = Flux::message('DepUpdateSuccess');
		}
	}
}

if (isset($_POST['take_action']))
{
	$action 	= $params->get('take_action');
	$dep_id		= $params->get('dep_id');

	if ($action == 'delete')
	{
		if ( ! count($dep_id))
		{
			$errorMessage = Flux::message('NoDepSelected');
		} else {
			foreach ($dep_id->toArray() as $id)
			{
				$sql = "DELETE FROM $tableName3 WHERE id = ?";
				$sth = $server->connection->getStatement($sql);
				$sth->execute(array((int)$id));

				if ( ! $sth->rowCount())
				{
					$errorMessage = Flux::message('FailedToDelDep');
					break;
				} else {
					$successMessage = Flux::message('SuccessToDelDep');
				}
			}
		}
	}
}

$sqlpartial = "WHERE 1";

// Get total count and feed back to the paginator.
$sth = $server->connection->getStatement("SELECT COUNT($support_dep.id) AS total FROM $tableName3 $sqlpartial");
$sth->execute();

Flux::config('ResultsPerPage', Flux::config('TicketPerPage'));
$paginator = $this->getPaginator($sth->fetch()->total);
$paginator->setSortableColumns(array('datetime_submitted', 'name', 'id' => 'asc', 'group_id'));

$sqlfull = "SELECT * FROM $tableName3 $sqlpartial";
// fetch in paginate
$sql  = $paginator->getSQL($sqlfull);
$sth  = $server->connection->getStatement($sql);
$sth->execute();
$dep_res = $sth->fetchAll();

if ( ! $sth->rowCount())
	$dep_res = NULL;

// fetch all
$sth  = $server->connection->getStatement($sql);
$sth->execute();
$all_dep_res = $sth->fetchAll();

if ( ! $sth->rowCount())
	$all_dep_res = NULL;
?>