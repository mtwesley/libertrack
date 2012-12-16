
-- turn all barcode uniqueness warnings into errors

update errors set type = 'E' where error = 'is_valid_barcode';

alter table revisions add column url d_text_long;
alter table revisions add column session_id d_id default 1 not null;

