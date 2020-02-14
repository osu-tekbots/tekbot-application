--
-- User Tables
-- Setup Script Order: 1
--
CREATE TABLE IF NOT EXISTS user_access_level (
    id INT NOT NULL AUTO_INCREMENT,
    user_access_name VARCHAR(128),

    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS user (
    id CHAR(16) NOT NULL,
    first_name VARCHAR(128),
    last_name VARCHAR(128),
    email VARCHAR(256),
    phone VARCHAR(16),
    onid VARCHAR(32),
    access_level_id INT NOT NULL,
    last_login_date DATETIME,
    date_updated DATETIME,
    date_created DATETIME NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (access_level_id) REFERENCES user_access_level(id)

);