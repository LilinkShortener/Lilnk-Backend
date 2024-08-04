CREATE DATABASE shortener;

USE shortener;

CREATE TABLE users (
    id INT(5) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    registration_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_earnings DECIMAL(10, 2) DEFAULT 0.00
);

CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_url VARCHAR(10) UNIQUE NOT NULL,
    original_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    access_count INT DEFAULT 0,
    last_accessed TIMESTAMP NULL,
    user_id INT,
    with_ads TINYINT(1) DEFAULT 1,
    earnings DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    iban VARCHAR(34) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
