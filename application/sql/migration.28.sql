

-- barcode activity comments

alter table barcode_activity add column comment d_text_long;

alter domain d_barcode_activity drop constraint d_barcode_activity_check;
alter domain d_barcode_activity add check (value ~ E'^[PIHTXDNESYALZC]$');


-- override status and comments

create table status_activity (
  id bigsearial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  old_status d_data_status not null,
  new_status d_data_status not null,
  comment d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint status_activity_pkey primary key (id),
  constraint status_activity_user_id_fkey foreign key (user_id) references users (id) on update cascade
);



