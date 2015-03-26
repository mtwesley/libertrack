
-- updating file operation types

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|VERIFY|EXP|EPT|CERT|INV|DOC|PJ|TAG|UNKWN)$');

