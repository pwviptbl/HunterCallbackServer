CREATE TABLE `interactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `interaction_id` VARCHAR(100) NOT NULL,
  `protocol` ENUM('HTTP', 'HTTPS') NOT NULL,
  `source_ip` VARCHAR(45) NOT NULL,
  `request_data` JSON NOT NULL,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_interaction_id` ON `interactions` (`interaction_id`);
CREATE INDEX `idx_timestamp` ON `interactions` (`timestamp`);
