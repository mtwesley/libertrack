
-- coc status updates

alter table barcode_coc_status add column trigger d_text_short default 'none';
alter table barcode_coc_status add column user_id d_id default 1 not null;

