
-- coc status updates

alter table barcode_coc_activity add column trigger d_text_short default 'none';
alter table barcode_coc_activity add column user_id d_id default 1 not null;

alter domain d_coc_status drop constraint d_coc_status_check;
alter domain d_coc_status add check (value ~ E'^[PIHTXDESYALZ]$');
