/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for db_oxa
CREATE DATABASE IF NOT EXISTS `db_oxa` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `db_oxa`;


-- Dumping structure for table db_oxa.tbl_urls
DROP TABLE IF EXISTS `tbl_urls`;
CREATE TABLE IF NOT EXISTS `tbl_urls` (
  `id_c` char(6) COLLATE utf8_unicode_ci NOT NULL,
  `longURL_c` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hash_c` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `dateAdded_d` int(10) unsigned NOT NULL,
  `lastAccess_d` int(10) unsigned DEFAULT NULL,
  `secret_c` char(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_c`),
  UNIQUE KEY `longUrl_c` (`longURL_c`),
  KEY `hash_c` (`hash_c`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
