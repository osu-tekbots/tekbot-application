SELECT
    `eu`.`eu_id` AS `eu_id`,
    `eu`.`eu_et_id` AS `et_id`,
    `eu`.`eu_notes` AS `eu_notes`,
    `eu`.`eu_location` AS `eu_location`,
    `eu`.`eu_is_public` AS `eu_is_public`,
    `eu`.`eu_is_deleted` AS `eu_is_deleted`,
    `eu`.`eu_date_created` AS `eu_date_created`,
    `eu`.`eu_date_updated` AS `eu_date_updated`,
    CASE
        WHEN `active_checkout`.`ec_id` IS NOT NULL              THEN 'Checked out' 
        WHEN `last_reservation`.`er_id` IS NOT NULL
            AND `last_reservation_checkout`.`ec_id` IS NULL     THEN 'Reserved'
                                                                ELSE 'Available'
    END AS `checkout_status`,
    COALESCE(`latest_health_status`.`name`, `default_health`.`eho_name`) AS `health_status`
FROM `tekbots`.`equipment_unit` `eu`
    LEFT JOIN (
        SELECT `ec`.`ec_id` AS `ec_id`, `ec`.`ec_eu_id` AS `eu_id`
        FROM `tekbots`.`equipment_checkout` `ec`
            JOIN (
                SELECT
                    `tekbots`.`equipment_checkout`.`ec_eu_id` AS `eu_id`,
                    MAX(`tekbots`.`equipment_checkout`.`ec_id`) AS `ec_id`
                FROM `tekbots`.`equipment_checkout`
                WHERE `tekbots`.`equipment_checkout`.`ec_date_returned` IS NULL
                GROUP BY `tekbots`.`equipment_checkout`.`ec_eu_id`
            ) `latest` ON (`latest`.`ec_id` = `ec`.`ec_id`)
    ) `active_checkout` ON `active_checkout`.`eu_id` = `eu`.`eu_id`

    LEFT JOIN (
        SELECT `er`.`er_id` AS `er_id`, `er`.`er_eu_id` AS `eu_id`
        FROM `tekbots`.`equipment_reservation` `er`
            JOIN (
                SELECT
                    `tekbots`.`equipment_reservation`.`er_eu_id` AS `eu_id`,
                    MAX(
                        `tekbots`.`equipment_reservation`.`er_id`
                    ) AS `er_id`
                FROM `tekbots`.`equipment_reservation`
                WHERE
                    `tekbots`.`equipment_reservation`.`er_is_employee_dismissed` = 0
                    AND `tekbots`.`equipment_reservation`.`er_date_reserved` >= NOW() - INTERVAL 1 HOUR
                GROUP BY `tekbots`.`equipment_reservation`.`er_eu_id`
            ) `latest` ON `latest`.`er_id` = `er`.`er_id`
    ) `last_reservation` ON (`last_reservation`.`eu_id` = `eu`.`eu_id`)
    LEFT JOIN `tekbots`.`equipment_checkout` `last_reservation_checkout` ON `last_reservation_checkout`.`ec_er_id` = `last_reservation`.`er_id`

    LEFT JOIN (
        SELECT `ehl`.`ehl_eu_id` AS `eu_id`, `eho`.`eho_name` AS `name`
        FROM `tekbots`.`equipment_health_log` `ehl`
            JOIN `tekbots`.`equipment_health_option` `eho` ON `eho`.`eho_id` = `ehl`.`ehl_eho_id`
            JOIN(
                SELECT
                    `tekbots`.`equipment_health_log`.`ehl_eu_id` AS `eu_id`,
                    MAX(
                        `tekbots`.`equipment_health_log`.`ehl_id`
                    ) AS `ehl_id`
                FROM `tekbots`.`equipment_health_log`
                GROUP BY `tekbots`.`equipment_health_log`.`ehl_eu_id`
            ) `latest` ON `latest`.`ehl_id` = `ehl`.`ehl_id`
    ) `latest_health_status` ON `latest_health_status`.`eu_id` = `eu`.`eu_id`
    LEFT JOIN `tekbots`.`equipment_health_option` `default_health` ON `default_health`.`eho_id` = 1;
