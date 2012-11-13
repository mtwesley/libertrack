
-- tolerances

create table tolerances (
  id bigserial not null,
  type d_text_short not null,
  form_type d_form_type not null,
  form_fields d_text_short not null,
  accuracy_range d_measurement_float default 0 not null,
  tolerance_range d_measurement_float default 0 not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint tolerances_pkey primary key (id),
  constraint revisions_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint tolerances_unique unique(form_type,form_fields)
);

insert into roles (name,description) values ('tolerances','Accuracy and Tolerance Management');

insert into tolerances (form_type,form_fields,accuracy_range,tolerance_range) values ('TDF','survey_line',2,20);
insert into tolerances (form_type,form_fields,accuracy_range,tolerance_range) values ('TDF','diameter',5,40);
insert into tolerances (form_type,form_fields,accuracy_range,tolerance_range) values ('TDF','length',2,10);

insert into tolerances (form_type,form_fields,accuracy_range,tolerance_range) values ('LDF','diameter',5,30);
insert into tolerances (form_type,form_fields,accuracy_range,tolerance_range) values ('LDF','length',0.5,2);
insert into tolerances (form_type,form_fields,accuracy_range,tolerance_range) values ('LDF','volume',0.2,2);


-- fix barcodes

alter domain d_barcode_type drop constraint d_barcode_type_check;
alter domain d_barcode_type add check (value ~ E'^[PTFSLRHE]$');


-- grades

alter domain d_grade drop constraint d_grade_check;
alter domain d_grade add check (value ~ E'^(LM|A|AB|B|BC|C|D|FAS|CG|1|2|3)$');


-- errors

create domain d_error_type as character(1) check (value ~ E'^[EW]$');

alter table errors add column type d_error_type default 'E' not null;

