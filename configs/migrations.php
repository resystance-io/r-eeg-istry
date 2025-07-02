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
    "ALTER TABLE `dashboards` ADD `sort` VARCHAR(32)  NULL  DEFAULT NULL  AFTER `colorconfig`;",
    "UPDATE `temporary` SET `value1` = '3' WHERE `feature` = 'database_version';"
];

$database_migrations[4] = [
    "ALTER TABLE `meters` ADD `meter_state` ENUM('new','onboarding','active','suspended','deactivated','refused')  NULL  DEFAULT 'new'  AFTER `meter_addr_city`;",
    "UPDATE meters SET meter_state = 'active';",    // Existing meters that are already imported should be considered 'active' instead of 'new'
    "ALTER TABLE `meters` ADD `meter_date_added` INT(11)  NULL  DEFAULT NULL  AFTER `meter_state`;",
    "ALTER TABLE `meters` ADD `meter_date_requested` INT(11)  NULL  DEFAULT NULL  AFTER `meter_date_added`;",
    "ALTER TABLE `meters` ADD `meter_date_accepted` INT(11)  NULL  DEFAULT NULL  AFTER `meter_date_requested`;",
    "ALTER TABLE `meters` ADD `meter_date_deactivated` INT(11)  NULL  DEFAULT NULL  AFTER `meter_date_accepted`;",
    "ALTER TABLE `meters` ADD `meter_date_refused` INT(11)  NULL  DEFAULT NULL  AFTER `meter_date_deactivated`;",
    "UPDATE `temporary` SET `value1` = '4' WHERE `feature` = 'database_version';"
];

$database_migrations[5] = [
    "ALTER TABLE `meters` ADD `meter_estimated_consumption` VARCHAR(8)  NULL  DEFAULT NULL  AFTER `meter_feedlimit`;",
    "ALTER TABLE `registrations` ADD `banking_institute` VARCHAR(64)  NULL  DEFAULT NULL  AFTER `banking_iban`;",
    "ALTER TABLE `registrations` ADD `banking_mandate_reference` VARCHAR(64)  NULL  DEFAULT NULL  AFTER `banking_consent`;",
    "ALTER TABLE `registrations` ADD `banking_debit_type` ENUM('core','b2b','none')  NULL  DEFAULT NULL  AFTER `banking_mandate_reference`;",
    "UPDATE `temporary` SET `value1` = '5' WHERE `feature` = 'database_version';"
];

$database_migrations[6] = [
    "INSERT INTO `dashboard_columns` (`id`, `name`, `nicename`, `compute`, `source`, `searchable`, `filterable`, `sortable`, `visible`) VALUES (NULL, 'meter_state', 'Z&auml;hlerstatus', 'meter_state', 'meters', 'y', 'y', 'y', 'y');",
    "UPDATE `temporary` SET `value1` = '6' WHERE `feature` = 'database_version';"
];
