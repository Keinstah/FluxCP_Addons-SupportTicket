FluxCP Addon [Support Tickets]
=====================

Features:
- Title Notification (ex. Flux Control Panel (1) - means you have 1 unread support ticket.)
- Sidebar Notification (ex. View Tickets [3] - means you have 3 unread support ticket)
- You can easily subscribe/unsubscribe to a ticket even a staff member can unsubscribe to a support ticket.
- Subscribing (0 to disable the subscribing or 1 to enable. This will affect everyone.)
- Ticket Delay (0 to disable this feature. They have to wait for 'TicketDelay' hours to open a new ticket.)
- Priority (Low, Medium or High. To help organize the priority - Can be edited by the staff if it's misleading)
- Status (Closed, Open, Resolved, Replied)
- Department (You can add, edit or delete a Department. Group is for the level of the department that can be handled by the staff members.)
- Reply (Staff Reply will be highlighted and will have an icon to avoid the confusion.)
- WYSIWYG
- Settings (For staff only)
	- Nickname (Use ~ character if you want to use your username. By default it will use your username when replying to a ticket.)
	- Email (This email will only be used for support ticketing. Use ~ character if you want to use your account email. By default it will use your account email when receiving emails.)
	- Subcription (Receive email notifications when someone opened or updated a support ticket.)
	- 'Last updated on' will show right next to Save Button.
- Character affected (Select the character where the problem occurred. Disabled if the account doesn't a character yet.)
- Search Engine (If your account is a staff member you can search using their account id, email, char id)
- Ticket Editing (Only the staff can edit a support ticket.)
- Ticket Listing (List of all support tickets. For staff only.)
- Unread ticket will be highlighted in the list.
- Normal Player can open/close their own ticket and only a staff member can resolve/delete a ticket.
- Player/Staff can use @checkunread/@cu in-game to check if they have an unread support ticket.
- PM me if i miss something.

Compability:
- Tested on Xantara's FluxCP for rAthena - https://github.com/m...ntara/fluxcp-rA

Rules:
- Do not steal the credit of this work.

How to Install:
- Make a folder named support in addons folder.
- Extract the files to support folder.
- Copy the file inside the addons/support/support_templates and paste it to data/templates folder.
- Add the line below

	- themes/default/header.php -

	Before:  
		```<?php if (!defined('FLUX_ROOT')) exit;
		```

	After:
		```<?php if (!defined('FLUX_ROOT')) exit; 
		require_once(FLUX_ROOT.'/'.FLUX_ADDON_DIR.'/support/modules/support/function.php');
		```



	- themes/default/main/sidebar.php -

	Before: (You will get 2 results. You will have to do it twice too.)
		```<span><?php echo htmlspecialchars($menuItem['name']) ?></span>
		```

	After:
		```<?php if ($menuItem['module'] == 'support'): ?>
		<span><?php echo sprintf($menuItem['name'], " [<strong".($unread > 0 ? " style='color:#d84646'" : "").">".$unread."</strong>") ?>]</span>
		<?php else: ?>
		<span><?php echo htmlspecialchars($menuItem['name']) ?></span>
		<?php endif ?>
		```

- Update your database and make sure cp_support_tickets, cp_support_reply, cp_support_dep and cp_support_settings does exists
- Add a Department first so players can open a ticket.
- Copy addons/support/npc/ticket_notification.txt to your server npc/custom folder or whataver folder you want.
- Paste this line npc: npc/custom/ticket_notification.txt inside your npc/scripts_custom.conf
- You can find all the configuration in addons/support/config/addon.php
- Done.




If you find a bug, please contact me.

Github: http://github.com/Feefty  
Email: kingfeefty@gmail.com  
rAthena: Feefty

Feel free to buy me a coffee  
Paypal: keinstah@gmail.com
