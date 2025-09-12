-- Create tb_admins table
CREATE TABLE `tb_admins` (
  `admin_id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `admin_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add approved_by_admin_id to bookings table
ALTER TABLE `bookings`
ADD COLUMN `approved_by_admin_id` INT(11) NULL DEFAULT NULL AFTER `status`,
ADD CONSTRAINT `fk_approved_by_admin` FOREIGN KEY (`approved_by_admin_id`) REFERENCES `tb_admins`(`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Optional: Insert a default admin user (password is 'adminpass', please change it immediately after setup)
-- You should hash this password properly in a real application.
INSERT INTO `tb_admins` (`username`, `password_hash`, `admin_name`) VALUES
('admin', '$2y$10$e.g.hashedpasswordhere', 'Default Admin');
-- Note: The password_hash above is a placeholder. You should generate a real hash for 'adminpass'
-- using password_hash('adminpass', PASSWORD_DEFAULT) in PHP.