--
-- TekBot Project Tables
-- Setup Script Order: 2
--
-- 
--
CREATE TABLE IF NOT EXISTS equipment_category (
    id INT NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(128) NOT NULL,

    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS equipment_health (
    id INT NOT NULL AUTO_INCREMENT,
    health_name VARCHAR(128) NOT NULL,

    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS equipment (
    id CHAR(16) NOT NULL,
    equipment_name VARCHAR(256) NOT NULL,
    description TEXT,
    health_id INT NOT NULL,
    usage_instructions TEXT,
    notes TEXT,
    return_check TEXT,
    number_parts INT,
    part_list TEXT,
    location TEXT,
    category_id INT,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    is_archived BOOLEAN NOT NULL DEFAULT FALSE,
    date_updated DATETIME,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (health_id) REFERENCES equipment_health(id),
    FOREIGN KEY (category_id) REFERENCES equipment_category(id)
);

CREATE TABLE IF NOT EXISTS equipment_image (
    id CHAR(16) NOT NULL,
    equipment_id CHAR(16) NOT NULL,
    image_name VARCHAR(128) NOT NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    
    PRIMARY KEY (id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);

