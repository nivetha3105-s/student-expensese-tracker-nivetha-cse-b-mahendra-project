-- Run this in the MySQL editor (not in PHP).
-- Table is named expense_users because your ByteXL DB already has a "users" table
-- (from the internship portal) with a different structure.
CREATE TABLE IF NOT EXISTS expense_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('income','expense') NOT NULL,
  category ENUM('Food','Travel','Books','Hostel','Entertainment','Education','Other') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  description VARCHAR(200),
  txn_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_date (user_id, txn_date),
  CONSTRAINT fk_txn_user FOREIGN KEY (user_id) REFERENCES expense_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
