-- CC Vault Sprint 2: run this once in phpMyAdmin on an existing installation.
-- It adds safe card-management fields. It does not alter card numbers or passwords.

ALTER TABLE cards
    ADD COLUMN billing_day TINYINT UNSIGNED NULL AFTER annual_fee,
    ADD COLUMN renewal_month TINYINT UNSIGNED NULL AFTER billing_day,
    ADD COLUMN renewal_year SMALLINT UNSIGNED NULL AFTER renewal_month,
    ADD COLUMN notes TEXT NULL AFTER renewal_year;
