
-- buyers

create table buyers (
  id bigserial not null,
  name d_text_short unique not null,
  contact d_text_short,
  address d_text_medium,
  email d_text_short,
  phone d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint buyers_pkey primary key (id),
  constraint buyers_user_id_fkey foreign key (user_id) references users (id) on update cascade
);


-- invoice fees

create domain d_fee_type as character varying(1) check (value ~ E'(F|P)');

create domain d_tax_code as character varying(16) check (value ~ E'^(((CBL)|R|T)-)?[0-9]{3,4}(-[0-9]{2})?$');

create table fees (
  id bigserial not null,
  type d_fee_type not null,
  value d_measurement_float not null,
  name d_text_short unique not null,
  tax_code d_tax_code unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint fees_pkey primary key (id),

  constraint fees_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table invoice_fees (
  id bigserial not null,
  invoice_id d_id not null,
  fee_id d_id not null,
  amount d_money not null,

  -- constraint invoice_data_fees_pkey primary key (id),

  constraint invoice_fees_invoice_data_id_fkey foreign key (invoice_id) references invoices (id) on update cascade on delete cascade,
  constraint invoice_fees_unique unique(invoice_id,fee_id)
);

create table invoice_data_fees (
  id bigserial not null,
  invoice_data_id d_id not null,
  fee_id d_id not null,
  amount d_money not null,

  -- constraint invoice_data_fees_pkey primary key (id),

  constraint invoice_data_fees_invoice_data_id_fkey foreign key (invoice_data_id) references invoice_data (id) on update cascade on delete cascade,
  constraint invoice_data_fees_unique unique(invoice_data_id,fee_id)
);

