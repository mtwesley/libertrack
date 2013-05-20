create table capabilities (
  id bigserial not null,
  role_id d_id,
  name d_text_short unique not null,
  description d_text_long not null,

  constraint capabilities_pkey primary key (id),
  constraint capabilities_role_id_fkey foreign key (role_id) references roles (id) on update cascade on delete cascade
);

create table capabilities_users (
  id bigserial not null,
  capability_id d_id not null,
  user_id d_id not null,

  constraint capability_users_pkey primary key (id),
  constraint capability_users_capability_id foreign key (capability_id) references capabilities (id),
  constraint capability_users_user_id foreign key (user_id) references users (id)
);

create unique index capabilities_users_unique on capabilities_users (capability_id,user_id);
