--
-- 3D Printer Tables
-- 
--
CREATE TABLE IF NOT EXISTS 3d_printers (
    3dprinter_id INT NOT NULL AUTO_INCREMENT,
    3dprinter_name VARCHAR(128),
    description TEXT,
    location VARCHAR(128),

    PRIMARY KEY (3dprinter_id)
);

CREATE TABLE IF NOT EXISTS 3d_print_type (
    3dprinter_type_id INT NOT NULL AUTO_INCREMENT,
    3dprint_type_name VARCHAR(128) NOT NULL,
    print_type_description TEXT,
    3dprinter_id INT NOT NULL,
    head_size FLOAT,
    3dprinter_precision VARCHAR(128),
    build_plate_size VARCHAR(128),
    cost_per_gram FLOAT,

    PRIMARY KEY (3dprinter_type_id),
    FOREIGN KEY (3dprinter_id) REFERENCES 3d_printers(3dprinter_id)
);

CREATE TABLE IF NOT EXISTS course_print_allowance (
    allowance_id INT NOT NULL AUTO_INCREMENT,
    course_name VARCHAR(128) NOT NULL,
    number_allowed_3dprints INT,
    number_allowed_lasercuts INT,

    PRIMARY KEY (allowance_id)
);

CREATE TABLE IF NOT EXISTS course_group (
    course_group_id CHAR(16) NOT NULL,
    group_name VARCHAR(128) NOT NULL,
    allowance_id INT NOT NULL,
    term_code INT,
    date_expiration DATETIME NOT NULL,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (course_group_id),
    FOREIGN KEY (allowance_id) REFERENCES course_print_allowance(allowance_id)
);


CREATE TABLE IF NOT EXISTS course_student (

    course_student_id INT NOT NULL AUTO_INCREMENT,
    course_group_id CHAR(16) NOT NULL,
    onid VARCHAR(128) NOT NULL,
    user_id CHAR(16),

    PRIMARY KEY (course_student_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);

CREATE TABLE IF NOT EXISTS voucher_code (
    voucher_id CHAR(8) NOT NULL,
    date_used DATETIME,

    PRIMARY KEY (voucher_id)
);

CREATE TABLE IF NOT EXISTS message_group (
    message_group_id CHAR(16) NOT NULL,

    PRIMARY KEY (message_group_id)
);


CREATE TABLE IF NOT EXISTS messages (
    message_id CHAR(16) NOT NULL,
    message_group_id CHAR(16),
    user_id CHAR(16) NOT NULL,
    message_text TEXT,
    is_employee BOOLEAN NOT NULL,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (message_id),
    FOREIGN KEY (message_group_id) REFERENCES message_group(message_group_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)

);


CREATE TABLE IF NOT EXISTS 3d_jobs (
    3d_job_id CHAR(16) NOT NULL,
    user_id CHAR(16) NOT NULL,
    3dprinter_id INT NOT NULL,
    3dprinter_type_id INT NOT NULL,
    db_filename VARCHAR(128) NOT NULL,
    stl_file_name VARCHAR(128) NOT NULL,
    payment_method VARCHAR(16),
    course_group_id INT,
    voucher_code VARCHAR(16),
    date_created DATETIME NOT NULL,
    valid_print_date DATETIME,
    user_confirm_date DATETIME,
    complete_print_date DATETIME,
    employee_notes TEXT, 
    message_group_id CHAR(16),
    pending_customer_response BOOLEAN NOT NULL DEFAULT FALSE,
    date_updated DATETIME,

    PRIMARY KEY (3d_job_id),
    FOREIGN KEY (3dprinter_id) REFERENCES 3d_printers(3dprinter_id),
    FOREIGN KEY (3dprinter_type_id) REFERENCES 3d_print_type(3dprinter_type_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (3dprinter_id) REFERENCES 3d_printers(3dprinter_id),
    FOREIGN KEY (voucher_code) REFERENCES voucher_code(voucher_id)

);




CREATE TABLE IF NOT EXISTS 3d_job_fees (
    3d_fee_id CHAR(16) NOT NULL,
    3d_job_id CHAR(16) NOT NULL,
    user_id CHAR(16) NOT NULL,
    customer_notes TEXT,
    date_created DATETIME NOT NULL,
    payment_info VARCHAR(32),
    is_pending BOOLEAN NOT NULL DEFAULT FALSE,
    is_paid BOOLEAN NOT NULL DEFAULT FALSE,
    date_updated DATETIME,

    PRIMARY KEY (3d_fee_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (3d_job_id) REFERENCES 3d_jobs(3d_job_id)

);




