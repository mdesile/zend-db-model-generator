create database testzdmg;

use testzdmg;

CREATE TABLE `user` (
  `id` INT UNSIGNED NOT NULL,
  `name` varchar(20)  NOT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = MyISAM;

insert into `user` values (1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5'),(6,'name6'),(7,'name7'),(8,'name8'),(9,'name9'),(10,'name10'),
                          (11,'name11'),(12,'name12'),(13,'name13'),(14,'name14'),(15,'name15'),(16,'name16'),(17,'name17'),(18,'name18'),(19,'name19'),(20,'name20');

CREATE TABLE accounts (
 account_name      VARCHAR(100) NOT NULL PRIMARY KEY
) ENGINE=INNODB;

CREATE TABLE products (
 product_id        INTEGER NOT NULL PRIMARY KEY,
 product_name      VARCHAR(100)
) ENGINE=INNODB;

CREATE TABLE bugs (
  bug_id            INTEGER NOT NULL PRIMARY KEY,
  bug_description   VARCHAR(100),
  bug_status        VARCHAR(20),
  reported_by       VARCHAR(100),
  assigned_to       VARCHAR(100),
  verified_by       VARCHAR(100),
 FOREIGN KEY (reported_by) REFERENCES accounts(account_name),
 FOREIGN KEY (assigned_to) REFERENCES accounts(account_name),
 FOREIGN KEY (verified_by) REFERENCES accounts(account_name)
 ) ENGINE=INNODB;

CREATE TABLE bugs_products (
  bug_id            INTEGER NOT NULL,
  product_id        INTEGER NOT NULL,
  PRIMARY KEY       (bug_id, product_id),
 FOREIGN KEY (bug_id) REFERENCES bugs(bug_id),
 FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=INNODB;

