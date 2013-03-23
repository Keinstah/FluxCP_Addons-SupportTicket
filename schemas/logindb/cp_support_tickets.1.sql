CREATE TABLE IF NOT EXISTS `cp_support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `email` varchar(155) NOT NULL,
  `char_id` int(11) DEFAULT '0',
  `subject` varchar(55) NOT NULL,
  `department` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `datetime_submitted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(11) NOT NULL DEFAULT '1',
  `datetime_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subscribe` int(11) NOT NULL,
  `ticket_read` longtext,
  `unsubscribe` longtext,
  `unread` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;