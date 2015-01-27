
-- upgrading CSV duplicates to support corrections

alter domain d_csv_status drop constraint d_csv_status_check;
alter domain d_csv_status add check (value ~ E'^[PARDCU]$');

alter table csv_duplicates add column is_corrected d_bool default false not null;

create index csv_duplicates_corrections on csv_duplicates (csv_id,duplicate_csv_id,is_corrected);


