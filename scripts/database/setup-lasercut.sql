--
-- Laser Cut Tables
-- 
--

CREATE TABLE IF NOT EXISTS laser_cutters (
    laser_cutter_id INT NOT NULL AUTO_INCREMENT,
    laser_cutter_name VARCHAR(128),
    description TEXT,
    location VARCHAR(128),

    PRIMARY KEY (laser_cutter_id)
);

CREATE TABLE IF NOT EXISTS laser_cut_material (
    laser_cut_material_id INT NOT NULL AUTO_INCREMENT,
    laser_cut_material_name VARCHAR(128) NOT NULL,
    cut_material_description TEXT,
    cost_per_sheet FLOAT,

    PRIMARY KEY (laser_cut_material_id)
);

CREATE TABLE IF NOT EXISTS laser_jobs (
    laser_job_id CHAR(16) NOT NULL,
    user_id CHAR(16) NOT NULL,
    laser_cutter_id INT NOT NULL,
    laser_cut_material_id INT NOT NULL,
    quantity INT NOT NULL,
    db_filename VARCHAR(128) NOT NULL,
    dxf_file_name VARCHAR(128) NOT NULL,
    payment_method VARCHAR(16),
    course_group_id INT,
    voucher_code VARCHAR(16),
    date_created DATETIME NOT NULL,
    valid_cut_date DATETIME,
    user_confirm_date DATETIME,
    complete_cut_date DATETIME,
    employee_notes TEXT, 
    customer_notes TEXT, 
    message_group_id CHAR(16),
    pending_customer_response BOOLEAN NOT NULL DEFAULT FALSE,
    date_updated DATETIME,

    PRIMARY KEY (laser_job_id),
    FOREIGN KEY (laser_cutter_id) REFERENCES laser_cutters(laser_cutter_id),
    FOREIGN KEY (laser_cut_material_id) REFERENCES laser_cut_material(laser_cut_material_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (voucher_code) REFERENCES voucher_code(voucher_id)
);