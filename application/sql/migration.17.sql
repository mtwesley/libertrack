
-- sequences

create sequence s_specs_number minvalue 1;
create sequence s_epr_number minvalue 1;


-- domains

create domain d_specs_number as character(6) check (value ~ E'[0-9]{6}');
create domain d_epr_number as character(6) check (value ~ E'[0-9]{6}');

alter sequence s_invoices_reference_number rename to s_invoices_number;

-- exporting

create table specs (
  id bigserial not null,
  number d_specs_number not null,
  is_draft d_bool default true not null,
  file_id d_id unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint specs_pkey primary key (id),

  constraint specs_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null)))
);


create table epr (
  id bigserial not null,
  number d_epr_number not null,
  is_draft d_bool default true not null,
  file_id d_id unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint epr_pkey primary key (id),

  constraint epr_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null)))
);

-- shipping numbers

alter table specs_data add column specs_id d_id;
alter table specs_data add constraint specs_data_specs_id_fkey foreign key (specs_id) references specs (id) on update cascade;
alter table specs_data add column epr_id d_id;
alter table specs_data add constraint epr_data_epr_id_fkey foreign key (epr_id) references epr (id) on update cascade;

alter table specs_data add constraint specs_data_specs_check check (specs_id is not null or specs_barcode_id is not null);
alter table specs_data add constraint specs_data_epr_check check (epr_id is not null or epr_barcode_id is not null);

alter table specs_data alter column specs_barcode_id drop not null;
alter table specs_data alter column epr_barcode_id drop not null;


-- invoices

create domain d_invoice_number as numeric(6) check ((value > 100000) and (value < 200000));

alter table invoices rename column reference_number to number;
alter table invoices alter column number type d_invoice_number;

alter table invoices add constraint invoices_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null)));

