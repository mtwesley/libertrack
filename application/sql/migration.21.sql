
-- fixes for error checks

alter domain d_error_type drop constraint d_error_type_check;
alter domain d_error_type add check (value ~ E'^[EWS]$');

-- changes to tolerances

alter table tolerances drop column form_fields;
alter table tolerances rename column type to "check";

alter table tolerances drop constraint tolerances_unique;
alter table tolerances add constraint tolerances_unique unique(form_type,"check");

-- clear out tolerances

delete from tolerances;

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_survey_line',2,20);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_length',2,10);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_volume',0.2,2);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_volume',0.2,2);

-- extra

alter table files alter column operation_type type character varying(6);
drop domain d_operation_type;
create domain d_operation_type as character varying(6) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|CHECKS|EPR|PJ|INV|UNKWN)$');

