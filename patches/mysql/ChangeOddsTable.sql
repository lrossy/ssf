ALTER TABLE `27_ssf`.`new_odds`     ADD COLUMN `sportsbook_name` VARCHAR(255) NULL AFTER `pinnacle_feedtime`;
UPDATE `27_ssf`.`new_odds` SET `sportsbook_name`='Pinnacle';