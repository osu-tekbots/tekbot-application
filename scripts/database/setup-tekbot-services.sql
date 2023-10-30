CREATE TABLE IF NOT EXISTS voucher_code (
    voucher_id CHAR(8) NOT NULL,
    date_used DATETIME,
    user_id CHAR(16),
    date_created DATETIME NOT NULL,
    date_expired DATETIME NOT NULL,
    service_id INT NOT NULL,

    PRIMARY KEY (voucher_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (service_id) REFERENCES tekbot_services(service_id)

);

CREATE TABLE IF NOT EXISTS tekbot_services (
    service_id INT NOT NULL AUTO_INCREMENT,
    service_name VARCHAR(128) NOT NULL,

    PRIMARY KEY (service_id)
);