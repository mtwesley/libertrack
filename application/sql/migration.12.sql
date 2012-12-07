
-- reduce junk from database, mainly unncecessary indexes

drop trigger t_barcodes_locks on ldf_data;

alter table roles_users drop constraint roles_users_pkey cascade;
alter table user_tokens drop constraint user_tokens_pkey cascade;
alter table barcode_hops_cached drop constraint barcode_hops_cached_pkey cascade;
alter table barcode_coc_status drop constraint barcode_coc_status_pkey cascade;
alter table invoice_data drop constraint invoice_data_pkey cascade;
alter table csv_errors drop constraint csv_errors_pkey cascade;
alter table csv_errors drop constraint csv_errors_pkey cascade;
alter table csv_duplicates drop constraint csv_duplicates_pkey cascade;
alter table errors drop constraint errors_pkey cascade;
alter table tolerances drop constraint tolerances_pkey cascade;


-- coc status

alter table barcodes drop column coc_status cascade;

create table barcode_coc_status (
  id bigserial not null,
  barcode_id d_id not null,
  status d_coc_status default 'P' not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint barcode_coc_status_pkey primary key (id),
  constraint barcode_coc_status_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_coc_status_unique unique(barcode_id,status)
);

create index barcode_coc_status_barcode_id_status on barcode_coc_status (barcode_id,status);


-- make errors optional

alter table csv_errors alter column error drop not null;
alter table errors alter column error drop not null;

