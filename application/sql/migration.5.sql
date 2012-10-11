
-- domains

create domain d_duplicate_type as character(1) check (value ~ E'^[BP]$');

-- tables

create table csv_errors (
  id bigserial not null,
  csv_id d_id not null,
  field d_text_short not null,
  error d_text_short not null,
  params d_text_long,
  is_ignored d_bool default false not null,

  constraint csv_errors_pkey primary key (id),
  constraint csv_errors_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade,

  constraint csv_errors_unique unique(csv_id,field,error)
);

create table csv_duplicates (
  id bigserial not null,
  csv_id d_id not null,
  duplicate_csv_id d_id not null,
  field d_text_short,

  constraint csv_duplicates_pkey primary key (id),
  constraint csv_duplicates_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade,
  constraint csv_duplicates_duplicate_csv_id_fkey foreign key (duplicate_csv_id) references csv (id) on update cascade on delete cascade,

  constraint csv_duplicates_check check (csv_id < duplicate_csv_id)
);

create table errors (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  field d_text_short not null,
  error d_text_short not null,
  params d_text_long,
  is_ignored d_bool default false not null,

  constraint errors_pkey primary key (id),

  constraint errors_unique unique(form_type,form_data_id,field,error)
);

create index csv_errors_field on csv_errors (csv_id,field);
create index csv_errors_error on csv_errors (csv_id,error);

create index csv_duplicates_duplicate_csv_id on csv_duplicates (csv_id,duplicate_csv_id);
create index csv_duplicates_csv_id_type on csv_duplicates (csv_id,field);
create index csv_duplicates_duplicate_csv_id_type on csv_duplicates (duplicate_csv_id,field);

create index errors_form_type_data_id on errors (form_type,form_data_id);
create index errors_field on errors (form_type,form_data_id,field);
create index errors_errors on errors (form_type,form_data_id,error);

-- csv data hases

alter table csv add column content_md5 d_text_short;

alter table csv drop column errors;
alter table csv drop column suggestions;
alter table csv drop column duplicates;

alter table ssf_data drop column errors;
alter table tdf_data drop column errors;
alter table ldf_data drop column errors;
alter table mof_data drop column errors;
alter table mif_data drop column errors;
alter table specs_data drop column errors;
alter table epr_data drop column errors;
