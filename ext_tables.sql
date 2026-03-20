CREATE TABLE tx_newsletter_domain_model_newsletter (
    uid int(11) NOT NULL AUTO_INCREMENT,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    subject varchar(255) DEFAULT '' NOT NULL,
    content mediumtext,
    status int(11) DEFAULT '0' NOT NULL,
    scheduled_at int(11) DEFAULT '0' NOT NULL,
    statistics mediumtext,
    target_lists int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_newsletter_domain_model_subscriber (
    uid int(11) NOT NULL AUTO_INCREMENT,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    email varchar(255) DEFAULT '' NOT NULL,
    fe_user_uid int(11) DEFAULT '0' NOT NULL,
    interest_tags varchar(255) DEFAULT '' NOT NULL,
    token varchar(255) DEFAULT '' NOT NULL,
    confirmed tinyint(4) DEFAULT '0' NOT NULL,
    confirmed_at int(11) DEFAULT '0' NOT NULL,
    deleted_at int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_newsletter_domain_model_subscriberlist (
    uid int(11) NOT NULL AUTO_INCREMENT,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    description text,
    interest_tag varchar(255) DEFAULT '' NOT NULL,
    subscribers int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_newsletter_subscriberlist_subscriber_mm (
    uid_local int(11) DEFAULT '0' NOT NULL,
    uid_foreign int(11) DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    sorting_foreign int(11) DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_newsletter_newsletter_subscriberlist_mm (
    uid_local int(11) DEFAULT '0' NOT NULL,
    uid_foreign int(11) DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    sorting_foreign int(11) DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);
