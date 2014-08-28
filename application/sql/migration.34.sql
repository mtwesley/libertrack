
-- adding owner information for sites

alter table sites add column owner d_text_short;

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|VERIFY|EXP|EPT|CERT|INV|DOC|PJ|UNKWN)$');

alter domain d_document_type drop constraint d_document_type_check;
alter domain d_document_type add check (value ~ E'(SPECS|EXP|EPT|CERT)');
