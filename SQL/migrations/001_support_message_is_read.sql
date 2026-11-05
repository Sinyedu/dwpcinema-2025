-- The support code tracks unread admin replies in SupportMessage.isRead, which SQL/createDB.sql
-- never created. Safe to run more than once (MariaDB 10.0.2+; on MySQL drop "IF NOT EXISTS" and run once).
USE dwpcinemaDB;
ALTER TABLE SupportMessage ADD COLUMN IF NOT EXISTS isRead TINYINT(1) NOT NULL DEFAULT 0;
