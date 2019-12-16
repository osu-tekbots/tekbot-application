CREATE VIEW reservation_equipment_user AS 
SELECT first_name, last_name, email, phone, onid, access_level_id, user_access_name, equipment_name, 
equipment.health_id, health_name, usage_instructions, equipment.notes, return_check, number_parts, part_list, 
location, equipment.category_id, category_name, is_public, is_archived, eqreservation_id, equipment_reservation.equipment_id, 
equipment_reservation.user_id 
FROM user, user_access_level, equipment, equipment_reservation, equipment_category, equipment_health
WHERE equipment_reservation.equipment_id = equipment.equipment_id
    AND equipment_reservation.user_id = user.user_id
    AND user.access_level_id = user_access_level.user_access_level_id
    AND equipment.health_id = equipment_health.health_id
    AND equipment.category_id = equipment_category.category_id;

