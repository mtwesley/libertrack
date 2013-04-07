
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
  constraint qrcodes_user_id_fkey foreign key (user_id) references users (id) on update cascade,
);

create index qrcodes_type on qrcodes (id,type);
create index qrcodes_qrcode_type on qrcodes (id,qrcode,type);

create table documents (
  id bigserial not null,
  type d_document_type not null,
  operator_id d_id,
  site_id d_id,
  qrcode_id d_id not null,
  number d_document_number,
  is_draft d_bool default true not null,
  values d_text_long,
  file_id d_id unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint documents_pkey primary key (id),

  constraint documents_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null))),
  constraint documents_check check (not(operator_id is null) and (site_id is null) and (qrcode_id is null))
);

create table documents_data (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  document_id d_id not null,

  -- constraint document_data_pkey primary key (id),
  constraint document_data_document_id foreign key (document_id) references documents (id) on update cascade on delete cascade,

  constraint document_data_unique unique(form_type,form_data_id,document_id)
);

alter table invoices add constraint invoices_check check (not(operator_id is null) and (site_id is null));
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


create or replace function rebuild_barcode_hops(x_barcode_id d_id)
  returns void as
$$
  declare x_id d_id;
  declare x_hops d_positive_int;
begin
  if (x_barcode_id is null) then
    truncate barcode_hops_cached;

    for x_id in select id from barcodes where parent_id is not null loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  else
    delete from barcode_hops_cached where barcode_id = x_barcode_id;
    select parent_id from barcodes where id = x_barcode_id and parent_id is not null into x_id;

    x_hops = 1;
    while x_id is not null loop
      insert into barcode_hops_cached(barcode_id,parent_id,hops)
      values(x_barcode_id,x_id,x_hops);
      x_hops = x_hops + 1;
      select parent_id from barcodes where id = x_id into x_id;
    end loop;

    for x_id in select id from barcodes where parent_id = x_barcode_id loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  end if;
end
$$ language 'plpgsql';

