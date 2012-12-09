
-- fix contraint naming issue

alter table tolerances drop constraint revisions_user_id_fkey;
alter table tolerances add constraint tolerances_user_id_fkey foreign key (user_id) references users (id) on update cascade;


-- creating better (universal) revisions

drop index revisions_form_type_data_id;

drop table revisions;

create table revisions (
  id bigserial not null,
  model d_text_short not null,
  model_id d_id not null,
  data d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint revisions_pkey primary key (id),
  constraint revisions_user_id_fkey foreign key (user_id) references users (id) on update cascade
);


-- rename table for coc status

drop table barcode_coc_status;

create table barcode_coc_activity (
  id bigserial not null,
  barcode_id d_id not null,
  status d_coc_status default 'P' not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint barcode_coc_activity_pkey primary key (id),
  constraint barcode_coc_activity_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_coc_activity_unique unique(barcode_id,status)
);

create index barcode_coc_activity_barcode_id_status on barcode_coc_activity (barcode_id,status);


