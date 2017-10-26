CREATE TABLE IF NOT EXISTS `b_serialnumbers` (
  `id` int not null auto_increment,
  `number` varchar(255) NOT NULL,
  `active` varchar(1)  NOT NULL,
  `date_create` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `user_id` int NOT NULL,
  primary key (id)
);