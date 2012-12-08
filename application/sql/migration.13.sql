
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

