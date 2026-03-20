CREATE TABLE tx_newsletter_domain_model_newsletter (
    uid int NOT NULL AUTO_INCREMENT,
    pid int DEFAULT '0' NOT NULL,
    tstamp int DEFAULT '0' NOT NULL,
    crdate int DEFAULT '0' NOT NULL,
    cruser_id int DEFAULT '0' NOT NULL,
    deleted tinyint DEFAULT '0' NOT NULL,
    hidden tinyint DEFAULT '0' NOT NULL,
    subject varchar(255) DEFAULT '' NOT NULL,
    content mediumtext,
    status int DEFAULT '0' NOT NULL,
    scheduled_at int DEFAULT '0' NOT NULL,
    statistics mediumtext,
    target_lists int DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_newsletter_domain_model_subscriber (
    uid int NOT NULL AUTO_INCREMENT,
    pid int DEFAULT '0' NOT NULL,
    tstamp int DEFAULT '0' NOT NULL,
    crdate int DEFAULT '0' NOT NULL,
    cruser_id int DEFAULT '0' NOT NULL,
    deleted tinyint DEFAULT '0' NOT NULL,
    hidden tinyint DEFAULT '0' NOT NULL,
    email varchar(255) DEFAULT '' NOT NULL,
    fe_user_uid int DEFAULT '0' NOT NULL,
    interest_tags varchar(255) DEFAULT '' NOT NULL,
    token varchar(255) DEFAULT '' NOT NULL,
    confirmed tinyint DEFAULT '0' NOT NULL,
    confirmed_at int DEFAULT '0' NOT NULL,
    deleted_at int DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_newsletter_domain_model_subscriberlist (
    uid int NOT NULL AUTO_INCREMENT,
    pid int DEFAULT '0' NOT NULL,
    tstamp int DEFAULT '0' NOT NULL,
    crdate int DEFAULT '0' NOT NULL,
    cruser_id int DEFAULT '0' NOT NULL,
    deleted tinyint DEFAULT '0' NOT NULL,
    hidden tinyint DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    description text,
    interest_tag varchar(255) DEFAULT '' NOT NULL,
    subscribers int DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_newsletter_subscriberlist_subscriber_mm (
    uid_local int DEFAULT '0' NOT NULL,
    uid_foreign int DEFAULT '0' NOT NULL,
    sorting int DEFAULT '0' NOT NULL,
    sorting_foreign int DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_newsletter_newsletter_subscriberlist_mm (
    uid_local int DEFAULT '0' NOT NULL,
    uid_foreign int DEFAULT '0' NOT NULL,
    sorting int DEFAULT '0' NOT NULL,
    sorting_foreign int DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);
