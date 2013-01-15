
-- domains

create domain d_id as bigint check (value > 0);

create domain d_int as integer;

create domain d_positive_int as integer check (value > 0);

create domain d_text_short as character varying(50);

create domain d_text_medium as character varying(500);

create domain d_text_long as text;

create domain d_bool as boolean;

create domain d_money as numeric(16,2);

create domain d_date as date;

create domain d_timestamp as timestamp without time zone;

create domain d_measurement_int as int check (value >= 0);

create domain d_measurement_float as real check (value >= 0);

create domain d_password as character(32) check (value ~ E'[0-9abcdef]');

create domain d_file_type as character varying(100);

create domain d_operation as character(1) check (value ~ E'^[IEAU]$');

create domain d_operation_type as character varying(5) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|EPR|PJ|INV|UNKWN)$');

create domain d_species_code as character varying(5) check (value ~ E'^[A-Z]{3,5}$');

create domain d_species_class as character(1) check (value ~ E'^[ABC]$');

create domain d_site_type as character(3) check (value ~ E'^[A-Z]{3}$');

create domain d_site_name as character varying(10) check (value ~ E'^[A-Z]{3}[\\s_-]*[A-Z0-9]{1,10}$');

create domain d_operator_tin as bigint check (value > 0);

create domain d_survey_line as numeric(2) check ((value > 0) and (value <= 20));

create domain d_form_type as character varying(5) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|EPR)$');

create domain d_duplicate_type as character(1) check (value ~ E'^[BP]$');

create domain d_grade as character varying(3) check (value ~ E'^(LM|A|AB|B|BC|C|D|FAS|CG|1|2|3)$');

create domain d_barcode as character varying(13) check (value ~ E'^[0123456789ACEFHJKLMNPRYXW]{8}(-[0123456789ACEFHJKLMNPRYXW]{4})?$');

create domain d_barcode_type as character(1) check (value ~ E'^[PTFSLRHE]$');

create domain d_error_type as character(1) check (value ~ E'^[EW]$');

create domain d_conversion_factor as numeric(6,4) check ((value > 0) and (value < 1));

create domain d_block_name as character varying(7) check (value ~ E'^[A-Z]{1,4}[0-9]{1,3}$');

create domain d_csv_status as character(1) check (value ~ E'^[PARDU]$');

create domain d_data_status as character(1) check (value ~ E'^[PARD]$');

create domain d_coc_status as character(1) check (value ~ E'^[PIHETXZYALZ]$');

create domain d_username as character varying(24) check (value ~ E'^[0-9A-Za-z_]{3,24}$');

create domain d_ip_address as inet;

create domain d_oid as oid;

create domain d_invoice_type as character varying(3) check (value ~ E'(ST)');


-- tables

create table roles (
  id bigserial,
  name d_text_short unique not null,
  description d_text_long not null,

  constraint roles_pkey primary key (id)
);

create table users (
  id bigserial,
  email d_text_short unique,
  name d_text_medium unique,
  username d_username unique not null,
  password d_password not null,
  is_deleted d_bool default false not null,
  last_timestamp d_timestamp,
  timestamp d_timestamp default current_timestamp not null,

  constraint users_pkey primary key (id)
);

create table roles_users (
  id bigserial not null,
  user_id d_int not null,
  role_id d_int not null,

  -- constraint roles_users_pkey primary key (id),
  constraint roles_users_user_id_fkey foreign key (user_id) references users (id) on update cascade,
  constraint roles_users_role_id_fkey foreign key (role_id) references roles (id) on update cascade on delete cascade
);

create table user_tokens (
  id bigserial,
  user_id d_int not null,
  user_agent d_text_short not null,
  token d_text_short unique not null,
  created d_int not null,
  expires d_int not null,

  -- constraint user_tokens_pkey primary key (id),
  constraint user_tokens_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table sessions (
  id bigserial not null,
  cookie d_text_short unique,
  user_id d_id,
  ip_address d_ip_address not null,
  user_agent d_text_medium,
  contents d_text_long not null,
  from_timestamp d_timestamp not null,
  to_timestamp d_timestamp not null,

  constraint sessions_pkey primary key (id),
  constraint sessions_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table species (
  id bigserial not null,
  code d_species_code unique not null,
  class d_species_class not null,
  botanic_name d_text_short unique,
  trade_name d_text_short unique,
  fob_price d_money not null,
  is_deleted d_bool default false not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint species_pkey primary key (id),
  constraint species_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table operators (
  id bigserial not null,
  tin d_operator_tin unique not null,
  name d_text_short unique not null,
  contact d_text_short,
  address d_text_medium,
  email d_text_short,
  phone d_text_short,
  is_deleted d_bool default false not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint operators_pkey primary key (id),
  constraint operators_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table sites (
  id bigserial not null,
  type d_site_type not null,
  name d_site_name unique not null,
  operator_id d_id not null,
  is_deleted d_bool default false not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint sites_pkey primary key (id),
  constraint sites_operator_id foreign key (operator_id) references operators (id) on update cascade on delete cascade,
  constraint sites_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table blocks (
  id bigserial not null,
  site_id d_id not null,
  name d_block_name not null,
  is_deleted d_bool default false not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint blocks_pkey primary key (id),
  constraint blocks_site_id_fkey foreign key (site_id) references sites (id) on update cascade on delete cascade,
  constraint blocks_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint blocks_unique_site_name unique(site_id,name)
);

create table files (
  id bigserial not null,
  name d_text_short not null,
  path d_text_long unique,
  type d_file_type not null,
  size d_int not null,
  operator_id d_id,
  site_id d_id,
  block_id d_id,
  operation d_operation default 'U' not null,
  operation_type d_operation_type default 'UNKWN' not null,
  content d_oid unique,
  content_md5 d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint files_pkey primary key (id),
  constraint files_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint files_unique_name_path unique(name,path)
);

create table printjobs (
  id bigserial not null,
  number d_positive_int unique not null,
  site_id d_id,
  allocation_date d_date default current_timestamp not null,
  file_id d_id,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint printjobs_pkey primary key (id),
  constraint printjobs_file_id_fkey foreign key (file_id) references files (id) on update cascade on delete cascade,
  constraint printjobs_site_id_fkey foreign key (site_id) references sites (id) on update cascade on delete cascade,
  constraint printjobs_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table barcodes (
  id bigserial not null,
  barcode d_barcode unique not null,
  type d_barcode_type default 'P' not null,
  parent_id d_id default null,
  printjob_id d_id not null,
  is_locked d_bool default false not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint barcodes_pkey primary key (id),
  constraint barcodes_parent_id_fkey foreign key (parent_id) references barcodes (id) on update cascade on delete set null,
  constraint barcodes_printjob_id_fkey foreign key (printjob_id) references printjobs (id) on update cascade on delete cascade,
  constraint barcodes_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table barcode_hops_cached (
  id bigserial not null,
  barcode_id d_id not null,
  parent_id d_id not null,
  hops d_measurement_int not null,

  -- constraint barcode_hops_cached_pkey primary key (id),
  constraint barcode_hops_cached_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,
  constraint barcode_hops_cached_parent_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_hops_cached_unique unique(barcode_id,parent_id)
);

create table barcode_coc_activity (
  id bigserial not null,
  barcode_id d_id not null,
  status d_coc_status default 'P' not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint barcode_coc_activity_pkey primary key (id),
  constraint barcode_coc_activity_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_coc_activity_unique unique(barcode_id,status)
);

create table invoices (
  id bigserial not null,
  type d_invoice_type not null,
  site_id d_id not null,
  reference_number d_positive_int unique,
  is_draft d_bool default true not null,
  from_date d_date,
  to_date d_date,
  created_date d_date not null,
  due_date d_date not null,
  file_id d_id unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint invoices_pkey primary key (id),
  constraint invoices_site_id_fkey foreign key (site_id) references sites (id) on update cascade on delete cascade,
  constraint invoices_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table invoice_data (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  invoice_id d_id,

  -- constraint invoice_data_pkey primary key (id),
  constraint invoice_data_invoice_id foreign key (invoice_id) references invoices (id) on update cascade on delete cascade,

  constraint invoice_data_unique unique(form_type,form_data_id,invoice_id)
);

create table csv (
  id bigserial not null,
  file_id d_id not null,
  operation d_operation not null,
  form_type d_form_type not null,
  form_data_id d_id,
  other_csv_id d_id,
  operator_id d_id,
  site_id d_id,
  block_id d_id,
  values d_text_long,
  content_md5 d_text_short unique,
  status d_csv_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint csv_pkey primary key (id),
  constraint csv_file_id_fkey foreign key (file_id) references files (id) on update cascade,
  constraint csv_other_csv_id_fkey foreign key (other_csv_id) references csv (id) on update cascade on delete set null,
  constraint csv_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table csv_revisions (
  id bigserial not null,
  csv_id b_id not null,
  csv_data d_text_long,
  url d_text_long,
  user_id d_id default 1 not null,
  session_id d_id default 1 not null,

  timestamp d_timestamp default current_timestamp not null,

  constraint csv_revisions_pkey primary key (id),
  constraint csv_revisions_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table csv_errors (
  id bigserial not null,
  csv_id d_id not null,
  field d_text_short not null,
  error d_text_short,
  params d_text_long,
  is_ignored d_bool default false not null,

  -- constraint csv_errors_pkey primary key (id),
  constraint csv_errors_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade,

  constraint csv_errors_unique unique(csv_id,field,error)
);

create table csv_duplicates (
  id bigserial not null,
  csv_id d_id not null,
  duplicate_csv_id d_id not null,
  field d_text_short,

  -- constraint csv_duplicates_pkey primary key (id),
  constraint csv_duplicates_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade,
  constraint csv_duplicates_duplicate_csv_id_fkey foreign key (duplicate_csv_id) references csv (id) on update cascade on delete cascade,

  constraint csv_duplicates_unique unique(csv_id,duplicate_csv_id,field),

  constraint csv_duplicates_check check (csv_id < duplicate_csv_id)
);

create table ssf_data (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  survey_line d_survey_line not null,
  cell_number d_positive_int not null,
  tree_map_number d_positive_int not null,
  diameter d_measurement_int not null,
  height d_measurement_float not null,
  is_requested d_bool default true not null,
  is_fda_approved d_bool default true not null,
  fda_remarks d_text_long,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint ssf_data_pkey primary key (id),
  constraint ssf_data_site_id_fkey foreign key (site_id) references sites (id) on update cascade,
  constraint ssf_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint ssf_data_block_id_fkey foreign key (block_id) references blocks (id) on update cascade,
  constraint ssf_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint ssf_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint ssf_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table tdf_data (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  tree_barcode_id d_id not null,
  stump_barcode_id d_id unique not null,
  species_id d_id not null,
  survey_line d_survey_line not null,
  cell_number d_positive_int not null,
  top_min d_measurement_int not null,
  top_max d_measurement_int not null,
  bottom_min d_measurement_int not null,
  bottom_max d_measurement_int not null,
  length d_measurement_float not null,
  action d_text_long,
  comment d_text_long,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint tdf_data_pkey primary key (id),
  constraint tdf_data_site_id_fkey foreign key (site_id) references sites (id) on update cascade,
  constraint tdf_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint tdf_data_block_id_fkey foreign key (block_id) references blocks (id) on update cascade,
  constraint tdf_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint tdf_data_tree_barcode_id_fkey foreign key (tree_barcode_id) references barcodes (id) on update cascade,
  constraint tdf_data_stump_barcode_id_fkey foreign key (stump_barcode_id) references barcodes (id) on update cascade,
  constraint tdf_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint tdf_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table ldf_data (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  barcode_id d_id unique not null,
  parent_barcode_id d_id not null,
  species_id d_id not null,
  top_min d_measurement_int not null,
  top_max d_measurement_int not null,
  bottom_min d_measurement_int not null,
  bottom_max d_measurement_int not null,
  length d_measurement_float not null,
  volume d_measurement_float not null,
  action d_text_long,
  comment d_text_long,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint ldf_data_pkey primary key (id),
  constraint ldf_data_site_id_fkey foreign key (site_id) references sites (id) on update cascade,
  constraint ldf_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint ldf_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint ldf_data_parent_barcode_id_fkey foreign key (parent_barcode_id) references barcodes (id) on update cascade,
  constraint ldf_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint ldf_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mif_data (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
  top_min d_measurement_int not null,
  top_max d_measurement_int not null,
  bottom_min d_measurement_int not null,
  bottom_max d_measurement_int not null,
  length d_measurement_float not null,
  volume d_measurement_float not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mif_data_pkey primary key (id),
  constraint mif_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mif_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mif_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mif_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mof_data (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
  width d_measurement_float not null,
  height d_measurement_float not null,
  length d_measurement_float not null,
  grade d_grade not null,
  volume d_measurement_float not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mof_data_pkey primary key (id),
  constraint mof_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mof_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mof_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mof_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table specs_data (
  id bigserial not null,
  operator_id d_id not null,
  specs_barcode_id d_id not null,
  epr_barcode_id d_id not null,
  contract_number d_text_short,
  barcode_id d_id unique not null,
  species_id d_id not null,
  top_min d_measurement_int not null,
  top_max d_measurement_int not null,
  bottom_min d_measurement_int not null,
  bottom_max d_measurement_int not null,
  length d_measurement_float not null,
  grade d_grade not null,
  volume d_measurement_float not null,
  origin d_text_short,
  destination d_text_short,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint specs_data_pkey primary key (id),
  constraint specs_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint specs_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint specs_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint specs_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table epr_data (
  id bigserial not null,
  request_number d_text_short not null,
  operator_id d_id not null,
  proposed_loading_date d_date not null,
  barcode_id d_id unique not null,
  barcode_type d_barcode_type not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint epr_data_pkey primary key (id),
  constraint epr_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint epr_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint epr_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table errors (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  error d_text_short,
  field d_text_short not null,
  params d_text_long,
  type d_error_type default 'E' not null,
  is_ignored d_bool default false not null,

  -- constraint errors_pkey primary key (id),

  constraint errors_unique unique(form_type,form_data_id,field,error,type)
);

create table revisions (
  id bigserial not null,
  model d_text_short not null,
  model_id d_id not null,
  data d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint revisions_pkey primary key (id),
  constraint revisions_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table tolerances (
  id bigserial not null,
  type d_text_short not null,
  form_type d_form_type not null,
  form_fields d_text_long not null,
  accuracy_range d_measurement_float default 0 not null,
  tolerance_range d_measurement_float default 0 not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint tolerances_pkey primary key (id),
  constraint tolerances_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint tolerances_unique unique(form_type,form_fields)
);

create table settings (
  key d_text_short not null,
  value d_text_long,

  constraint settings_pkey primary key (key)
);


-- sequences

create sequence s_invoices_reference_number minvalue 100100;


-- indexes

create index species_code on species (id,code);
create index species_class on species (id,class);

create index operators_tin on operators (id,tin);

create index sites_name on sites (id,name);
create index sites_operator_id on sites (id,operator_id);

create index printjobs_number on printjobs (id,number);
create index printjobs_site_id on printjobs (id,site_id);

create index barcodes_barcode_id on barcodes (id,barcode);
create index barcodes_printjob_id on barcodes (id,printjob_id);
create index barcodes_parent_id on barcodes (id,parent_id);
create index barcodes_type on barcodes (id,type);
create index barcodes_is_locked on barcodes (id,is_locked);

create index barcode_hops_cached_parent_id on barcode_hops_cached (barcode_id,parent_id);

create index barcode_coc_activity_barcode_id_status on barcode_coc_activity (barcode_id,status);

create index invoices_site_id on invoices (id,site_id);
create index invoices_reference_number on invoices (id,reference_number);
create index invoices_type on invoices (id,type);

create index files_operation on files (id,operation);

create index csv_status on csv (id,status);
create index csv_file_id on csv (id,file_id);
create index csv_operation on csv (id,operation);
create index csv_other_csv_id on csv (id,other_csv_id);
create index csv_form_type_data_id on csv (id,form_type,form_data_id);

create index csv_errors_field on csv_errors (csv_id,field);
create index csv_errors_error on csv_errors (csv_id,error);

create index csv_duplicates_duplicate_csv_id on csv_duplicates (csv_id,duplicate_csv_id);
create index csv_duplicates_csv_id_type on csv_duplicates (csv_id,field);
create index csv_duplicates_duplicate_csv_id_type on csv_duplicates (duplicate_csv_id,field);

create index ssf_data_site_id on ssf_data (id,site_id);
create index ssf_data_operator_id on ssf_data (id,operator_id);
create index ssf_data_block_id on ssf_data (id,block_id);
create index ssf_data_barcode_id on ssf_data (id,barcode_id);
create index ssf_data_species_id on ssf_data (id,species_id);
create index ssf_data_status on ssf_data (id,status);

create index tdf_data_site_id on tdf_data (id,site_id);
create index tdf_data_operator_id on tdf_data (id,operator_id);
create index tdf_data_block_id on tdf_data (id,block_id);
create index tdf_data_barcode_id on tdf_data (id,barcode_id);
create index tdf_data_tree_barcode_id on tdf_data (id,tree_barcode_id);
create index tdf_data_stump_barcode_id on tdf_data (id,stump_barcode_id);
create index tdf_data_species_id on tdf_data (id,species_id);
create index tdf_data_status on tdf_data (id,status);

create index ldf_data_site_id on ldf_data (id,site_id);
create index ldf_data_operator_id on ldf_data (id,operator_id);
create index ldf_data_barcode_id on ldf_data (id,barcode_id);
create index ldf_data_parent_barcode_id on ldf_data (id,parent_barcode_id);
create index ldf_data_species_id on ldf_data (id,species_id);
create index ldf_data_volume on ldf_data (id,volume);
create index ldf_data_status on ldf_data (id,status);

create index mif_data_operator_id on mif_data (id,operator_id);
create index mif_data_barcode_id on mif_data (id,barcode_id);
create index mif_data_species_id on mif_data (id,species_id);
create index mif_data_batch_number on mif_data (id,batch_number);
create index mif_data_volume on mif_data (id,volume);
create index mif_data_status on mif_data (id,status);

create index mof_data_operator_id on mof_data (id,operator_id);
create index mof_data_barcode_id on mof_data (id,barcode_id);
create index mof_data_species_id on mof_data (id,species_id);
create index mof_data_batch_number on mof_data (id,batch_number);
create index mof_data_volume on mof_data (id,volume);
create index mof_data_grade on mof_data (id,grade);
create index mof_data_status on mof_data (id,status);

create index specs_data_operator_id on specs_data (id,operator_id);
create index specs_data_barcode_id on specs_data (id,barcode_id);
create index specs_data_species_id on specs_data (id,species_id);
create index specs_data_volume on specs_data (id,volume);
create index specs_data_grade on specs_data (id,grade);
create index specs_data_status on specs_data (id,status);

create index epr_data_operator_id on epr_data (id,operator_id);
create index epr_data_barcode_id on epr_data (id,barcode_id);
create index epr_data_barcode_type on epr_data (id,barcode_type);
create index epr_data_request_number on epr_data (id,request_number);
create index epr_data_status on epr_data (id,status);

create index errors_form_type_data_id on errors (form_type,form_data_id);
create index errors_field on errors (form_type,form_data_id,field);
create index errors_errors on errors (form_type,form_data_id,error);


-- language

create language plpgsql;


-- functions

create function lookup_barcode_id(x_barcode character varying(13))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from barcodes where barcode = x_barcode limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_printjob_id(x_number int)
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from printjobs where number = x_number limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_species_id(x_code character varying(5))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from species where code = x_code limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_operator_id(x_tin bigint)
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from operators where tin = x_tin limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_site_id(x_name character varying(50))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from sites where name = x_name limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_block_id(x_site_name character varying(10), x_name character varying(6))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from blocks where site_id = lookup_site_id(x_site_name) and name = x_name limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_role_id(x_name character varying(50))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from roles where name = x_name limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_user_id(x_username character varying(50))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from users where username = x_username limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function barcodes_hops()
  returns trigger as
$$
begin
  if tg_op = 'INSERT' then
    perform rebuild_barcode_hops(new.id);
  elseif tg_op = 'UPDATE' then
    delete from barcode_hops_cached where barcode_id = old.id;
    perform rebuild_barcode_hops(new.id);
  elseif tg_op = 'DELETE' then
    delete from barcode_hops_cached where barcode_id = old.id;
  end if;

  return null;
end
$$ language 'plpgsql';


create function rebuild_barcode_hops(x_barcode_id d_id)
  returns void as
$$
  declare x_id d_id;
  declare x_hops d_positive_int;
begin
  if x_barcode_id is null then
    delete from barcode_hops_cached;

    for x_id in select id from barcodes where parent_id is null loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  else

    delete from barcode_hops_cached where barcode_id = x_barcode_id;
    insert into barcode_hops_cached(barcode_id,parent_id,hops) values(x_barcode_id,x_barcode_id,0);
    select parent_id from barcodes where id = x_barcode_id into x_id;

    x_hops = 1;
    while x_id is not null loop
      insert into barcode_hops_cached(barcode_id,parent_id,hops)
      values(x_barcode_id,x_id,x_hops);
      x_hops = x_hops + 1;
      select parent_id from barcodes where id = x_id into x_id;
    end loop;

    for x_id in select id from barcodes where parent_id = x_barcode_id loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  end if;
end
$$ language 'plpgsql';


create function barcodes_locks()
  returns trigger as
$$
begin
  if tg_op = 'INSERT' then
    if new.invoice_id is not null then
      perform rebuild_barcode_locks(new.barcode_id, true);
    end if;
  elseif tg_op = 'UPDATE' then
    if (old.invoice_id is null) and (new.invoice_id is not null) then
      perform rebuild_barcode_locks(new.barcode_id, true);
    elseif (old.invoice_id is not null) and (new.invoice_id is null) then
      perform rebuild_barcode_locks(new.barcode_id, false);
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function rebuild_barcode_locks(x_barcode_id d_id, x_locked d_bool)
  returns void as
$$
  declare x_id d_id;
begin
  for x_id in select barcode_id from barcode_hops_cached where parent_id = x_barcode_id loop
    update barcodes set is_locked = x_locked where id = x_id;
  end loop;

  for x_id in select parent_id from barcode_hops_cached where barcode_id = x_barcode_id loop
    update barcodes set is_locked = x_locked where id = x_id;
  end loop;
end
$$ language 'plpgsql';


create function ssf_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = null where barcodes.id = old.barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'T' where barcodes.id = new.barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function tdf_data_update_barcodes()
  returns trigger as
$$
  declare x_id d_id;
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.stump_barcode_id) or (new.barcode_id = new.tree_barcode_id) or (new.tree_barcode_id = new.stump_barcode_id) then
      return null;
    end if;
  end if;

  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = null where barcodes.id = old.barcode_id;
    end if;
    if old.stump_barcode_id is not null then
      update barcodes set type = 'P', parent_id = null where barcodes.id = old.stump_barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'F' where barcodes.id = new.barcode_id;

      if new.tree_barcode_id is not null then
        update barcodes set parent_id = new.tree_barcode_id where barcodes.id = new.barcode_id;

        if new.stump_barcode_id is not null then
          update barcodes set parent_id = new.tree_barcode_id where barcodes.id = new.stump_barcode_id;
        end if;
      end if;
    end if;

    if new.stump_barcode_id is not null then
      update barcodes set type = 'S' where barcodes.id = new.stump_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function ldf_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.parent_barcode_id) then
      return null;
    end if;
  end if;

  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = null where barcodes.id = old.barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where barcodes.id = new.barcode_id;

      if new.parent_barcode_id is not null then
        update barcodes set parent_id = new.parent_barcode_id where barcodes.id = new.barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function mof_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = null where barcodes.id = old.barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'B' where barcodes.id = new.barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function specs_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.specs_barcode_id is not null then
      update barcodes set type = 'H' where barcodes.id = new.specs_barcode_id;
    end if;
    if new.epr_barcode_id is not null then
      update barcodes set type = 'E' where barcodes.id = new.epr_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function sites_parse_type()
  returns trigger as
$$
  declare x_site text[];
begin
  if new.name is not null then
    select regexp_matches(new.name::text, E'^([A-Z]{3})([\\s_-]*[A-Z0-9]{1,10})?$') into x_site;
    new.type = x_site[1];
  end if;

  return new;
end
$$ language 'plpgsql';


-- create function csv_integrity()
--   returns trigger as
-- $$
-- begin
--   if old.form_data_id is not null then
--     raise exception 'Imported data integrity violation';
--   end if;
--
--   return new;
-- end
-- $$ language 'plpgsql';
--


-- triggers

create trigger t_barcodes_hops
  after insert or delete or update on barcodes
  for each row
  execute procedure barcodes_hops();

create trigger t_ssf_data_update_barcodes
  after insert or update or delete on ssf_data
  for each row
  execute procedure ssf_data_update_barcodes();

create trigger t_tdf_data_update_barcodes
  after insert or update or delete on tdf_data
  for each row
  execute procedure tdf_data_update_barcodes();

create trigger t_ldf_data_update_barcodes
  after insert or update or delete on ldf_data
  for each row
  execute procedure ldf_data_update_barcodes();

create trigger t_mof_data_update_barcodes
  after insert or update or delete on mof_data
  for each row
  execute procedure mof_data_update_barcodes();

create trigger t_specs_data_update_barcodes
  after insert or update or delete on specs_data
  for each row
  execute procedure specs_data_update_barcodes();

create trigger t_barcodes_locks
  after insert or update on ldf_data
  for each row
  execute procedure barcodes_locks();

create trigger t_sites_parse_type
  before insert or update on sites
  for each row
  execute procedure sites_parse_type();

-- create trigger t_csv_integrity
--   before update on csv
--   for each row
--   execute procedure csv_integrity();