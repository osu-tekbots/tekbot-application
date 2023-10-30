--
-- Tekbot Equipment Checkout | Reservation | Contract tables
-- Setup Script Order: 3
--
-- This script assumes that there already exists a `user` table and a `tekbot-equipment` table
--
CREATE TABLE IF NOT EXISTS equipment_reservation (
    id CHAR(16) NOT NULL,
    equipment_id CHAR(16) NOT NULL,
    user_id CHAR(16) NOT NULL,
    datetime_reserved DATETIME,
    datetime_expired DATETIME,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    PRIMARY KEY (id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    FOREIGN KEY (user_id) REFERENCES user(id) 
);

CREATE TABLE IF NOT EXISTS contract_type (
    id INT NOT NULL AUTO_INCREMENT,
    type_name VARCHAR(128) NOT NULL,

    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS contract (
    id INT NOT NULL AUTO_INCREMENT,
    contract_type_id INT NOT NULL,
    duration TIME,
    title VARCHAR(128) NOT NULL,
    description TEXT,

    PRIMARY KEY (id),
    FOREIGN KEY (contract_type_id) REFERENCES contract_type(id) 
);


CREATE TABLE IF NOT EXISTS equipment_checkout_status (
    id INT NOT NULL AUTO_INCREMENT,
    status_name VARCHAR(128) NOT NULL,

    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS equipment_checkout (
    id CHAR(16) NOT NULL,
    user_id CHAR(16) NOT NULL,
    reservation_id CHAR(16) NOT NULL,
    checkout_status_id INT NOT NULL,
    contract_id INT NOT NULL,
    pickup_time DATETIME NOT NULL,
    return_time DATETIME,
    return_deadline DATETIME NOT NULL,
    notes TEXT,
    date_updated DATETIME,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user (id),
    FOREIGN KEY (reservation_id) REFERENCES equipment_reservation (id),
    FOREIGN KEY (checkout_status_id) REFERENCES  equipment_checkout_status(id)
    
);
