SELECT
    `ei`.`ei_id` AS `ei_id`,
    `ei`.`et_id` AS `et_id`,
    `ei`.`notes` AS `notes`,
    `ei`.`location` AS `location`,
    `ei`.`is_public` AS `is_public`,
    `ei`.`is_deleted` AS `is_deleted`,
    `ei`.`date_created` AS `date_created`,
    `ei`.`date_updated` AS `date_updated`,
    CASE
        WHEN `active_checkout`.`ec_id` IS NOT NULL              THEN 'Checked out' 
        WHEN `last_reservation`.`er_id` IS NOT NULL
            AND `last_reservation_checkout`.`ec_id` IS NULL     THEN 'Reserved'
                                                                ELSE 'Available'
    END AS `checkout_status`,
    COALESCE(`latest_health_status`.`name`, `dh`.`name`) AS `health_status`
FROM `tekbots`.`equipment_item` `ei`
    LEFT JOIN (
        SELECT `ec`.`ec_id` AS `ec_id`, `ec`.`ei_id` AS `ei_id`
        FROM `tekbots`.`equipment_checkout` `ec`
            JOIN (
                SELECT
                    `tekbots`.`equipment_checkout`.`ei_id` AS `ei_id`,
                    MAX(`tekbots`.`equipment_checkout`.`ec_id`) AS `ec_id`
                FROM `tekbots`.`equipment_checkout`
                WHERE `tekbots`.`equipment_checkout`.`date_returned` IS NULL
                GROUP BY `tekbots`.`equipment_checkout`.`ei_id`
            ) `latest` ON (`latest`.`ec_id` = `ec`.`ec_id`)
    ) `active_checkout` ON `active_checkout`.`ei_id` = `ei`.`ei_id`

    LEFT JOIN (
        SELECT `er`.`er_id` AS `er_id`, `er`.`ei_id` AS `ei_id`
        FROM `tekbots`.`equipment_reservation` `er`
            JOIN (
                SELECT
                    `tekbots`.`equipment_reservation`.`ei_id` AS `ei_id`,
                    MAX(
                        `tekbots`.`equipment_reservation`.`er_id`
                    ) AS `er_id`
                FROM `tekbots`.`equipment_reservation`
                WHERE
                    `tekbots`.`equipment_reservation`.`is_employee_dismissed` = 0
                    AND `tekbots`.`equipment_reservation`.`date_reserved` >= NOW() - INTERVAL 1 HOUR
                GROUP BY `tekbots`.`equipment_reservation`.`ei_id`
            ) `latest` ON `latest`.`er_id` = `er`.`er_id`
    ) `last_reservation` ON (`last_reservation`.`ei_id` = `ei`.`ei_id`)
    LEFT JOIN `tekbots`.`equipment_checkout` `last_reservation_checkout` ON `last_reservation_checkout`.`er_id` = `last_reservation`.`er_id`

    LEFT JOIN (
        SELECT `ehl`.`ei_id` AS `ei_id`, `eho`.`name` AS `name`
        FROM `tekbots`.`equipment_health_log` `ehl`
            JOIN `tekbots`.`equipment_health_option` `eho` ON `eho`.`eho_id` = `ehl`.`eho_id`
            JOIN(
                SELECT
                    `tekbots`.`equipment_health_log`.`ei_id` AS `ei_id`,
                    MAX(
                        `tekbots`.`equipment_health_log`.`ehl_id`
                    ) AS `ehl_id`
                FROM `tekbots`.`equipment_health_log`
                GROUP BY `tekbots`.`equipment_health_log`.`ei_id`
            ) `latest` ON `latest`.`ehl_id` = `ehl`.`ehl_id`
    ) `latest_health_status` ON `latest_health_status`.`ei_id` = `ei`.`ei_id`
    LEFT JOIN `tekbots`.`equipment_health_option` `dh` ON `dh`.`eho_id` = 1;
