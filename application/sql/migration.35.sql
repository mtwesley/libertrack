
-- upgrading CSV duplicates to support corrections

alter domain d_csv_status drop constraint d_csv_status_check;
alter domain d_csv_status add check (value ~ E'^[PARDCU]$');

alter table csv_duplicates add column is_corrected d_bool default false not null;

create index csv_duplicates_corrections on csv_duplicates (csv_id,duplicate_csv_id,is_corrected);


-- monitoring print job and barcode allocations

alter table printjobs add column is_monitored d_bool default false;
alter table printjobs alter column is_monitored set default true;

create index printjobs_monitored on printjobs (id,is_monitored);


-- invoice modification

alter domain d_invoice_type drop constraint d_invoice_check;
alter domain d_invoice_type add check (value ~ E'(ST|EXF|TAG)');

create sequence s_invoices_tag_number minvalue 100100;


-- updated barcodes

alter domain d_barcode drop constraint d_barcode_check ;
alter domain d_barcode add check (value ~ E'^[0123456789ABCDEFGHJKLMNPQRSTVWXYZ]{8}(-[0123456789ACEFHJKLMNPRYXW]{4})?$');
