
-- users

alter table users add column name d_text_medium unique;
alter table users add column timestamp d_timestamp default current_timestamp not null;
alter table users add column last_timestamp d_timestamp;
alter table users drop column logins;


-- sessions

alter table sessiosn add column cookie d_text_short unique;
alter table sessions alter column user_id drop not null;
alter table sessions drop column last_active;


-- roles

delete from roles_users;
delete from roles;

insert into roles (name, description) values ('login', 'Login');
insert into roles (name, description) values ('data', 'Data Entry');
insert into roles (name, description) values ('analysis', 'Data Analysis');
insert into roles (name, description) values ('reports', 'Reporting');
insert into roles (name, description) values ('management', 'Project Management');
insert into roles (name, description) values ('admin', 'Administration');

insert into roles_users (role_id, user_id) values (lookup_role_id('login'), lookup_user_id('sgs'));
insert into roles_users (role_id, user_id) values (lookup_role_id('data'), lookup_user_id('sgs'));
insert into roles_users (role_id, user_id) values (lookup_role_id('admin'), lookup_user_id('sgs'));

-- roles for users

alter table roles_users add column id serial not null;

alter table roles_users alter column user_id set not null;
alter table roles_users alter column role_id set not null;

