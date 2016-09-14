-- Upgrades the table from v1.2 to v1.3 --
-- IMPORTANT: BackUp your Database before running this script --
CREATE TABLE IF NOT EXISTS UserTokens (
  ID INT PRIMARY KEY AUTO_INCREMENT,
  UID INT(7) NOT NULL ,
  Token VARCHAR(255) NOT NULL,
  Type  TINYINT,
  CreateTime DATETIME NOT NULL,
  ExpirationTime DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS UserLog (
  ID INT PRIMARY KEY AUTO_INCREMENT,
  UID INT(7) NOT NULL,
  EventType TINYINT NOT NULL,
  EventData VARCHAR(255) NOT NULL DEFAULT '',
  EventTime TIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- TODO: Confirm the type for the confirmation tokens will be 1
INSERT INTO UserTokens(UID, Token, Type, ExpirationTime)
    SELECT ID, Confirmation, 1, NOW() + INTERVAL 3 DAY_SECOND
    FROM Users;
