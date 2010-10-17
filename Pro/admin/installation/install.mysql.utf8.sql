CREATE TABLE IF NOT EXISTS `#__btusers` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `username` varchar(50) default NULL,
  `password` varbinary(500) default NULL,
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__btsearches` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `search` varchar(50) default NULL,
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__btposts` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `post` varchar(140) default NULL,
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
);
