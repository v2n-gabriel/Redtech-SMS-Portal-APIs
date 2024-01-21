/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  GABRIEL
 * Created: Jul 8, 2019
 */
-- drop table dev_user
-- drop table dev_session
-- drop table dev_sms
-- drop table dev_user

create table dev_user
(
    id              varchar(50) unique,
    category        varchar(50) not null,
    email_address   varchar(100),
    phone_number    varchar(100),
    company_name    varchar(100),
    api_username    varchar(100) unique,
    api_password    varchar(100),
    ip_address      varchar(100),
    portal_username varchar(100),
    portal_password varchar(100),
    status          enum ('Active','Inactive','Disabled') default 'Inactive',
    date_created    timestamp                             default current_timestamp
);

insert into dev_user(id, category, api_username, api_password)
values ('testAccount', 'User', 'test', 'gabriel');

create table dev_session
(
    date_created timestamp default current_timestamp
);


create table dev_sms
(
    unique_id    varchar(100) unique,
    user_id      varchar(100),
    request_id   varchar(100),
    sender_id    varchar(100),
    receiver     varchar(100),
    message      varchar(100),
    date_created timestamp default current_timestamp,
    status       varchar(100) default 'pending',
    sent_time    timestamp,
    dlr_time     timestamp,
    dlr_status   varchar(100) default 'pending'
);


create table dev_email
(
    date_created timestamp default current_timestamp
);