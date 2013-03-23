<?php if (!defined('FLUX_ROOT')) exit;
return array(		
	'TicketDelay'		=> 6, // delay for opening a new ticket in hours
	'SubjectMinLen'		=> 6, // minimum char length for subject
	'SubjectMaxLen'		=> 25, // maximum char length for subject
	'SubjectChar'		=> '/[a-zA-Z0-9-_ ]/', // characters for subject
	'MessageMinLen'		=> 15, // minimum char length for message
	'MessageMaxLen'		=> 250, // maximum char length for message
	'EnableSubscribing'	=> true, // enable global subscribing - subscribing doesn't affect the browser notification
	'ReplyMinLen'		=> 6, // minimum char length for reply
	'ReplyMaxLen'		=> 200, // maximum char length for reply
	"NicknameMinLen"	=> 4, // minimum char length for nickname
	"NicknameMaxLen"	=> 23, // maximum length for nickname
	'NicknameChar'		=> '/[a-zA-Z0-9-_ ]/', // characters for nickname
	'ReplyPerPage'		=> 10, // number of reply to show in a page for pagination
	'TicketPerPage'		=> 10, // number of ticket to show in a page for pagination
	'DepNameMinLen'		=> 6, // minimum char length for department name
	'DepNameMaxLen'		=> 23, // maximum char length for department name
	'TicketSearchMinLen'=> 1, // minimum char length for search query
	'SupportFromName'	=> "FluxCP Staff Team", // name of the from to send in mail

	'TicketDelGroup'	=> AccountLevel::HIGHGM, // group level who can delete a ticket
	'TicketEditGroup'	=> AccountLevel::HIGHGM, // group level who can edit a ticket
	'TicketResolveGroup'=> AccountLevel::LOWGM, // group level who can resolve a ticket
	'TicketOpenGroup'	=> AccountLevel::NORMAL, // group level who can open a closed a ticket
	'TicketCloseGroup'	=> AccountLevel::NORMAL, // group level who can close a ticket
	'TicketShowUsername'=> AccountLevel::HIGHGM, // group level who can see the username link of the ticket author
	'TicketShowChar'	=> AccountLevel::LOWGM, // group level who can see the character link of the author
	'TicketStaffSearch'	=> AccountLevel::LOWGM, // group level who can search for the whole ticket

	'MenuItems'		=> array(
		'Other'		=> array(
			'View Tickets%s' => array(
				'module' => 'support'
			)
		)
	),

	'SubMenuItems'	=> array(
		'support'	=> array(
			'index' => 'My Tickets',
			'add' => 'Open Ticket',
			'list' => 'Ticket List',
			'settings' => 'Settings',
			'department' => 'Departments',
		)
	),

	// Do not touch for developers only
	'FluxTables'	=> array(
		'support_tickets' 	=> 'cp_support_tickets',
		'support_reply' 	=> 'cp_support_reply',
		'support_settings' 	=> 'cp_support_settings',
		'support_dep' 		=> 'cp_support_dep',
	)
)
?>