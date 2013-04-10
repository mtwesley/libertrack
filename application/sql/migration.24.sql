
-- documents

alter sequence s_specs_number minvalue rename to s_documents_specs_number minvalue;
alter sequence s_epr_number minvalue rename to s_documents_epr_number minvalue;

create domain d_document_type as character varying(5) check (value ~ E'(SPECS|EXP)');
create domain d_document_number as numeric(6) check (value > 0);

alter table files alter column name type d_text_long;

create table qrcodes (
  id bigserial not null,
  qrcode d_qrcode unique not null,
  type d_qrcode_type default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint qrcodes_pkey primary key (id),
  constraint qrcodes_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create index qrcodes_type on qrcodes (id,type);
create index qrcodes_qrcode_type on qrcodes (id,qrcode,type);

create table documents (
  id bigserial not null,
  type d_document_type not null,
  operator_id d_id,
  site_id d_id,
  qrcode_id d_id,
  number d_document_number,
  is_draft d_bool default true not null,
  values d_text_long,
  file_id d_id unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint documents_pkey primary key (id),

  constraint documents_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null))),
  constraint documents_check check (not((operator_id is null) and (site_id is null)))
);

create table document_data (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  document_id d_id not null,

  -- constraint document_data_pkey primary key (id),
  constraint document_data_document_id foreign key (document_id) references documents (id) on update cascade on delete cascade,

  constraint document_data_unique unique(form_type,form_data_id,document_id)
);

alter table invoices add constraint invoices_check check (not((operator_id is null) and (site_id is null)));
alter table invoices add column values d_text_long;
alter table invoices alter column invoice_id set not null;


-- functions

create or replace function lookup_invoice_id(x_type character varying(5), x_number numeric(6))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from invoices where type = x_type and number = x_number limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create or replace function lookup_document_id(x_type character varying(5), x_number numeric(6))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from documents where type = x_type and number = x_number limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create domain d_md5 as character(32);
create domain d_sha as character(64);

create domain d_qrcode as character(64);
create domain d_qrcode_type as character(1) check (value ~ E'^[P]$');

alter table specs_data drop column exp_id;
-- alter table specs_data drop column specs_id;

insert into roles (name, description) values ('exports', 'Export Management');
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('exports'));
