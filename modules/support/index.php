<?php if (!defined('FLUX_ROOT')) exit;
$this->loginRequired();
require_once('function.php');

$title = Flux::message('SupportTitle');

$support_tickets 	= Flux::config('FluxTables.support_tickets');
$tableName			= "{$server->loginDatabase}.{$support_tickets}";
$group_col 			= getGroupCol($server);

$sqlpartial = "WHERE account_id = ?";
$bind = array((int) $session->account->account_id);

// Get total count and feed back to the paginator.
$sth = $server->connection->getStatement("SELECT COUNT($support_tickets.id) AS total FROM $tableName $sqlpartial");
$sth->execute($bind);

Flux::config('ResultsPerPage', Flux::config('TicketPerPage'));
$paginator = $this->getPaginator($sth->fetch()->total);
$paginator->setSortableColumns(array('datetime_updated' => 'desc', 'subject', 'status', 'department', 'datetime_submitted'));

// fetch all ticket by user
$sql  = $paginator->getSQL("SELECT * FROM $tableName $sqlpartial");
$sth  = $server->connection->getStatement($sql);
$sth->execute($bind);
$ticket_res = $sth->fetchAll();
?>