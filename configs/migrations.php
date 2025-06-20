<?php

$database_migrations[1] = [
    "INSERT INTO `temporary` (`id`, `feature`, `value1`, `value2`, `value3`) VALUES (NULL, 'database_version', '1', NULL, NULL);"
];

$database_migrations[2] = [
    "ALTER TABLE `registrations` CHANGE `member_id` `member_id` INT(11)  UNSIGNED  NULL  DEFAULT NULL;",
    "UPDATE `temporary` SET `value1` = '2' WHERE `feature` = 'database_version';"
];

$database_migrations[3] = [
    "UPDATE `dashboard_columns` SET `compute` = 'registration_date' WHERE `name` = 'registration_date';",
    "UPDATE `dashboard_columns` SET `compute` = 'migration_date' WHERE `name` = 'migration_date';",
    "UPDATE `dashboard_columns` SET `compute` = 'delivery_date' WHERE `name` = 'delivery_date';",
    "UPDATE `temporary` SET `value1` = '3' WHERE `feature` = 'database_version';"
];