
CREATE TABLE `taxonomy` (
  `id` int(10) unsigned NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(110) CHARACTER SET latin1 DEFAULT NULL,
  `level` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `is_shown` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id_index` (`parent_id`) USING BTREE,
  KEY `name_index` (`name`),
  KEY `level_index` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8