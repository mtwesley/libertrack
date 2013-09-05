
-- start

alter table tdf_data disable trigger all;
alter table ldf_data disable trigger all;
alter table mif_data disable trigger all;
alter table specs_data disable trigger all;

alter table tdf_verification disable trigger all;
alter table ldf_verification disable trigger all;
alter table mif_verification disable trigger all;


-- diameters

alter table tdf_data add column diameter d_diameter;
alter table ldf_data add column diameter d_diameter;
alter table mif_data add column diameter d_diameter;
alter table specs_data add column diameter d_diameter;

alter table tdf_verification add column diameter d_diameter;
alter table ldf_verification add column diameter d_diameter;
alter table mif_verification add column diameter d_diameter;

update tdf_data set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);
update ldf_data set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);
update mif_data set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);
update specs_data set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);

update tdf_verification set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);
update ldf_verification set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);
update mif_verification set diameter = ((top_min + top_max + bottom_min + bottom_max) / 4);

alter table tdf_data alter column diameter set not null;
alter table ldf_data alter column diameter set not null;
alter table mif_data alter column diameter set not null;
alter table specs_data alter column diameter set not null;

alter table tdf_verification alter column diameter set not null;
alter table ldf_verification alter column diameter set not null;
alter table mif_verification alter column diameter set not null;


-- volumes

alter table tdf_data add column volume d_volume;
alter table tdf_verification add column volume d_volume;

update tdf_data set volume = (pi() * power(((diameter / 2) / 100), 2) * length);
update ldf_data set volume = (pi() * power(((diameter / 2) / 100), 2) * length);
update mif_data set volume = (pi() * power(((diameter / 2) / 100), 2) * length);
update specs_data set volume = (pi() * power(((diameter / 2) / 100), 2) * length);

update tdf_verification set volume = (pi() * power(((diameter / 2) / 100), 2) * length);
update ldf_verification set volume = (pi() * power(((diameter / 2) / 100), 2) * length);
update mif_verification set volume = (pi() * power(((diameter / 2) / 100), 2) * length);

alter table tdf_data alter column volume set not null;
alter table ldf_data alter column volume set not null;
alter table mif_data alter column volume set not null;
alter table specs_data alter column volume set not null;

alter table tdf_verification alter column volume set not null;
alter table ldf_verification alter column volume set not null;
alter table mif_verification alter column volume set not null;


-- done

alter table tdf_data enable trigger all;
alter table ldf_data enable trigger all;
alter table mif_data enable trigger all;
alter table specs_data enable trigger all;

alter table tdf_verification enable trigger all;
alter table ldf_verification enable trigger all;
alter table mif_verification enable trigger all;


-- reporting

create domain d_report_type as character varying(7) check (value ~ E'(CSV|DATA|SUMMARY)');

create domain d_report_number as numeric(6) check (value > 0);

create table reports (
  id bigserial not null,
  type d_report_type not null,
  name d_text_short not null unique,
  description d_text_long,
  number d_report_number,
  created_date d_date not null,
  tables d_text_long,
  fields d_text_long,
  filters d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint reports_pkey primary key (id),
  constraint reports_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table report_files (
  id bigserial not null,
  report_id d_id not null,
  file_id d_id unique not null,

  constraint report_files_pkey primary key (id),
  constraint report_files_file_id_fkey foreign key (file_id) references files (id) on update cascade,
  constraint report_files_report_id_fkey foreign key (report_id) references reports (id) on update cascade
);

create index reports_type on reports (id,type);
create index reports_number on reports (id,number);
create unique index report_type_number on reports (id,type,number);

create sequence s_report_csv_number minvalue 1;
create sequence s_report_data_number minvalue 1;
create sequence s_report_summary_number minvalue 1;


-- some house cleaning

alter table documents add constraint documents_user_id_fkey foreign key (user_id) references users (id) on update cascade;
