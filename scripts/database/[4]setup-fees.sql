--
-- Tekbot Equipment Checkout tables
-- Setup Script Order: 4
--
-- This script assumes that there already exists a `tekbot-user` table and a `tekbot-checkout` table
--

CREATE TABLE IF NOT EXISTS equipment_fee (
    id CHAR(16) NOT NULL UNIQUE,
    checkout_id CHAR(16) NOT NULL,
    user_id CHAR(16) NOT NULL,
    notes TEXT,
    amount FLOAT NOT NULL,
    is_paid BOOLEAN NOT NULL DEFAULT FALSE,
    date_updated DATETIME,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (checkout_id) REFERENCES equipment_checkout (id),
    FOREIGN KEY (user_id) REFERENCES user (id)

);

