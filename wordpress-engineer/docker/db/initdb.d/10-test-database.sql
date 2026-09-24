-- Second schema used only by the PHPUnit suite, so tests never touch the
-- database the site is running on.
CREATE DATABASE IF NOT EXISTS `agronews_test`
	DEFAULT CHARACTER SET utf8mb4
	DEFAULT COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `agronews_test`.* TO 'agronews'@'%';
FLUSH PRIVILEGES;
