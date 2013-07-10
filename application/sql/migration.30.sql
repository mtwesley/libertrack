
-- block approval

create domain d_block_status as character(1) check (value ~ E'^[PAR]$');

alter table blocks add column status d_block_status default 'P' not null;


-- tolerances

alter table tolerances alter column form_type type d_operation_type;

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDFV','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDFV','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDFV','is_matching_volume',0.2,2);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDFV','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDFV','is_matching_length',2,10);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SSFV','is_matching_survey_line',2,20);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SSFV','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SSFV','is_matching_height',2,10);

