CREATE TABLE tx_mainewsletter_subscriber (
    uid             int(11) NOT NULL auto_increment,
    pid             int(11) NOT NULL DEFAULT 0,
    hidden          tinyint(4) NOT NULL DEFAULT 0,
    deleted         tinyint(4) NOT NULL DEFAULT 0,
    tstamp          int(11) NOT NULL DEFAULT 0,
    crdate          int(11) NOT NULL DEFAULT 0,

    email           varchar(255) NOT NULL DEFAULT '',
    status          varchar(16) NOT NULL DEFAULT 'pending',
    token           varchar(128) NOT NULL DEFAULT '',
    confirmed_at    int(11) NOT NULL DEFAULT 0,
    unsubscribed_at int(11) NOT NULL DEFAULT 0,
    site            varchar(100) NOT NULL DEFAULT '',
    fe_user         int(11) NOT NULL DEFAULT 0,

    PRIMARY KEY (uid),
    KEY pid (pid),
    UNIQUE KEY email_site (email, site),
    KEY status (status),
    KEY token (token),
    KEY fe_user (fe_user)
);

CREATE TABLE tx_mainewsletter_campaign (
    uid             int(11) NOT NULL auto_increment,
    pid             int(11) NOT NULL DEFAULT 0,
    hidden          tinyint(4) NOT NULL DEFAULT 0,
    deleted         tinyint(4) NOT NULL DEFAULT 0,
    tstamp          int(11) NOT NULL DEFAULT 0,
    crdate          int(11) NOT NULL DEFAULT 0,

    sys_language_uid int(11) NOT NULL DEFAULT 0,
    l10n_parent      int(11) NOT NULL DEFAULT 0,
    l10n_diffsource  mediumblob,

    title           varchar(255) NOT NULL DEFAULT '',
    subject         varchar(255) NOT NULL DEFAULT '',
    body            mediumtext,
    status          varchar(16) NOT NULL DEFAULT 'draft',
    scheduled_at    int(11) NOT NULL DEFAULT 0,
    sent_at         int(11) NOT NULL DEFAULT 0,
    recipient_count int(11) NOT NULL DEFAULT 0,

    PRIMARY KEY (uid),
    KEY pid (pid),
    KEY status (status),
    KEY scheduled_at (scheduled_at),
    KEY language (l10n_parent, sys_language_uid)
);
