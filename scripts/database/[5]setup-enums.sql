--
-- Tekbots Checkout Seed
-- Setup Script Order: 5
--
-- This script assumes that all database table setup scripts for the capstone website have been
-- executed and that the corresponding tables exist in the database
--

-- INSERT INTO equipment_category (id, category_name) VALUES


INSERT INTO user_access_level (id, user_access_name) VALUES 
    (1, 'Student'), (2, 'Employee'), (3, 'Admin');

INSERT INTO equipment_health (id, health_name) VALUES 
    (1, 'Fully Functional'), (2, 'Partial Functionality'), (3, 'Broken');

INSERT INTO equipment_checkout_status (id, status_name) VALUES 
    (1, 'Checked Out'), (2, 'Late'), (3, 'Returned'), (5, 'Cancelled');

INSERT INTO contract_type (id, type_name) VALUES
    (1, 'Equipment Checkout'), (2, 'Locker Checkout');




