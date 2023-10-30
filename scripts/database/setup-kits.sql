--
-- Kit Checkout
-- Setup Script Order: 1
--
CREATE TABLE IF NOT EXISTS kit_enrollment_status (
    id INT NOT NULL AUTO_INCREMENT,
    status_name VARCHAR(128),

    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS kit_enrollment (
    kit_id CHAR(16) NOT NULL,
    fml_name VARCHAR(128),
    osu_id VARCHAR(128),
    onid VARCHAR(12),
    course_code VARCHAR(10),
    term_id VARCHAR(6),
    kit_status_id INT NOT NULL,
    date_updated DATETIME,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (kit_id),
    FOREIGN KEY (kit_status_id) REFERENCES kit_enrollment_status(id)

);

INSERT INTO kit_enrollment_status (id, status_name) VALUES 
    (1, 'Ready'), (2, 'Handed Out'), (3, 'Refunded');