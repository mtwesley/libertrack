
-- fixes for error checks

alter domain d_error_type drop constraint d_error_type_check;
alter domain d_error_type add check (value ~ E'^[EWS]$');

