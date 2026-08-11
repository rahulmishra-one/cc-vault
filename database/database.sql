-- Import this file into the database you created in Hostinger hPanel.
-- Do not add CREATE DATABASE or USE statements here: Hostinger database names have an account prefix.

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    card_name VARCHAR(160) NOT NULL,
    issuer VARCHAR(120) NOT NULL,
    network ENUM('Visa', 'Mastercard', 'RuPay', 'Amex', 'Other') NOT NULL DEFAULT 'Other',
    last_four CHAR(4) NOT NULL,
    annual_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'inactive', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cards_user_status (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE merchants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    merchant_name VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL,
    website VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_merchants_category (category)
) ENGINE=InnoDB;

CREATE TABLE card_benefits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    card_id INT UNSIGNED NOT NULL,
    benefit_type VARCHAR(80) NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    value_amount DECIMAL(10,2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_benefits_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE,
    INDEX idx_benefits_card_type (card_id, benefit_type)
) ENGINE=InnoDB;

CREATE TABLE recommendations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    card_id INT UNSIGNED NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    rationale TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recommendations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_recommendations_card FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE,
    INDEX idx_recommendations_user_score (user_id, score)
) ENGINE=InnoDB;

-- Development user. Password: password (change immediately after first login).
INSERT INTO users (full_name, email, password_hash) VALUES
('CC Vault Admin', 'admin@ccvault.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.zda0J0eMp8NpVT/2');

INSERT INTO cards (user_id, card_name, issuer, network, last_four, annual_fee) VALUES
(1, 'Regalia Gold', 'HDFC Bank', 'Visa', '3421', 2500.00),
(1, 'Sapphiro', 'ICICI Bank', 'Mastercard', '8790', 6500.00),
(1, 'Atlas', 'Axis Bank', 'Visa', '1208', 5000.00);
