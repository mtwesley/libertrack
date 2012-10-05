
-- domains

create domain d_duplicate_type as character(1) check (value ~ E'^[FP]$');

-- tables

create table csv_errors (
  csv_id d_id not null,
  field d_text_short not null,
  error d_text_short not null,

  constraint csv_errors_pkey primary key (csv_id,field),
  constraint csv_errors_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade
);

create table csv_duplicates (
  csv_id d_id not null,
  duplicate_csv_id d_id not null,
  type d_duplicate_type not null,

  constraint csv_duplicates_pkey (csv_id,duplicate_csv_id
);

create index csv_errors_field on csv_errors (csv_id,field);
create index csv_errors_error on csv_errors (csv_id,error);

create index csv_duplicates_duplicate_csv_id on csv_duplicates (csv_id,duplicate_csv_id);
create index csv_duplicates_csv_id_type on csv_duplicates (csv_id,type);
create index csv_duplicates_duplicate_csv_id_type on csv_duplicates (duplicate_csv_id,type);

create index errors_form_type_data_id on errors (form_type,form_data_id);
create index errors_field on errors (form_type,form_data_id,field);
create index errors_errors on errors (form_type,form_data_id,error);

