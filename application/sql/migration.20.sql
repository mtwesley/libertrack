
-- export fee invoice

alter domain d_invoice_type drop constraint d_invoice_type_check;
alter domain d_invoice_type add check (value ~ E'(ST|EXF)');

-- constraints and indexes

alter table invoices drop constraint invoices_reference_number_key;

drop index invoices_reference_number;
create index invoices_number on invoices (id,type,number);

-- sequences

alter sequence s_invoices_number rename to s_invoices_st_number;
create sequence s_invoices_exf_number minvalue 100100;

-- operator id

alter table invoices add column operator_id d_id;
alter table invoices alter column site_id drop not null;

-- migration

update invoices set operator_id = sites.operator_id from (
  select id, operator_id from sites
) as sites where sites.id = invoices.site_id;

alter table invoices alter column operator_id set not null;