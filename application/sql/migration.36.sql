
-- updating file operation types

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|VERIFY|EXP|EPT|CERT|INV|DOC|PJ|TAG|UNKWN)$');

-- typed print jobs

create domain d_printjob_type as character(1) check (value ~ E'^[TL]$');
alter table printjobs add column type d_barcode_type;
create index printjobs_type on printjobs (id,type);

-- operator short names

create domain d_text_tiny as character varying(25);
alter table operators add column short_name d_text_tiny unique;

