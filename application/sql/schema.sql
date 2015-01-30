
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

create domain d_md5 as character(32);

create domain d_sha as character(64);

create domain d_utm as character varying(19) check (value ~ E'^[0-9]{1,2} [0-9]{6}E [0-9]{1,8}N$');

create domain d_timestamp as timestamp without time zone;

create domain d_measurement_int as int check (value >= 0);

create domain d_measurement_float as real check (value >= 0);

create domain d_length as numeric(8,1) check (value >= 0);

create domain d_volume as numeric(8,3) check (value >= 0);

create domain d_diameter as int check (value >= 0);

create domain d_password as character(32) check (value ~ E'[0-9abcdef]');

create domain d_file_type as character varying(100);

create domain d_operation as character(1) check (value ~ E'^[UD]$');

create domain d_operation_type as character varying(6) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|VERIFY|EXP|EPT|CERT|INV|DOC|PJ|UNKWN)$');

create domain d_species_code as character varying(5) check (value ~ E'^[A-Z]{3,5}$');

create domain d_species_class as character(1) check (value ~ E'^[ABC]$');

create domain d_site_type as character varying(4) check (value ~ E'^(TSC|PUP|FMC|CFMA)$');

create domain d_site_name as character varying(10) check (value ~ E'^(TSC|PUP|FMC|CFMA)[\\s_-]*[A-Z0-9]{1,10}$');

create domain d_operator_tin as bigint check (value > 0);

create domain d_survey_line as numeric(2) check ((value > 0) and (value <= 20));

create domain d_form_type as character varying(5) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV)$');

create domain d_form_data_type as character varying(5) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB)$');

create domain d_form_verification_type as character varying(6) check (value ~ E'^(SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV)$');

create domain d_duplicate_type as character(1) check (value ~ E'^[BP]$');

create domain d_grade as character varying(3) check (value ~ E'^(LM|A|AB|B|BC|C|D|FAS|CG|1|2|3)$');

create domain d_barcode as character varying(13) check (value ~ E'^[0123456789ABCDEFGHJKLMNPQRSTVWXYZ]{8}(-[0123456789ACEFHJKLMNPRYXW]{4})?$');

create domain d_barcode_type as character(1) check (value ~ E'^[PTFSLRHEW]$');

create domain d_barcode_activity as character(1) check (value ~ E'^[PIHTXDNEOSYALZC]$');

create domain d_barcode_lock as character varying(6) check (value ~ E'(ADMIN|INV|DOC|BRCODE|VERIFY)');

create domain d_qrcode as character(64);

create domain d_qrcode_type as character(1) check (value ~ E'^[P]$');

create domain d_location_type as character(1) check (value ~ E'^[P]$');

create domain d_schedule as integer[] check (0 <= all(value));

create domain d_schedule_type as character(1) check (value ~ E'^[ODWMQY]$');

create domain d_check_type as character(1) check (value ~ E'^[EWSU]$');

create domain d_error_type as character(1) check (value ~ E'^[EWSU]$');

create domain d_conversion_factor as numeric(6,4) check ((value > 0) and (value < 1));

create domain d_block_name as character varying(7) check (value ~ E'^[A-Z]{1,4}[0-9]{1,3}$');

create domain d_csv_status as character(1) check (value ~ E'^[PARDCU]$');

create domain d_data_status as character(1) check (value ~ E'^[PARD]$');

create domain d_block_status as character(1) check (value ~ E'^[PIAR]$');

create domain d_username as character varying(24) check (value ~ E'^[0-9A-Za-z_]{3,24}$');

create domain d_ip_address as inet;

create domain d_oid as oid;

create domain d_fee_type as character varying(1) check (value ~ E'(F|P)');

create domain d_tax_code as character varying(16) check (value ~ E'^(((CBL)|R|T)-)?[0-9]{3,4}(-[0-9]{2})?$');

create domain d_invoice_type as character varying(3) check (value ~ E'(ST|EXF|TAG)');

create domain d_invoice_number as numeric(6) check ((value > 100000) and (value < 200000));

create domain d_document_type as character varying(5) check (value ~ E'(SPECS|EXP|EPT|CERT)');

create domain d_document_number as numeric(6) check (value > 0);

create domain d_report_type as character varying(7) check (value ~ E'(CSV|DATA|SUMMARY)');

create domain d_report_number as numeric(6) check (value > 0);


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

create table files (
  id bigserial not null,
  name d_text_long not null,
  path d_text_long unique,
  type d_file_type not null,
  size d_int not null,
  operator_id d_id,
  site_id d_id,
  block_id d_id,
  operation d_operation not null,
  operation_type d_operation_type default 'UNKWN' not null,
  content d_oid unique,
  content_md5 d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint files_pkey primary key (id),
  constraint files_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint files_unique_name_path unique(name,path)
);

create table species (
  id bigserial not null,
  code d_species_code unique not null,
  class d_species_class not null,
  botanic_name d_text_short unique,
  trade_name d_text_short unique,
  fob_price d_money not null,
  min_diameter d_diameter not null,
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
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint operators_pkey primary key (id),
  constraint operators_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table buyers (
  id bigserial not null,
  name d_text_short unique not null,
  contact d_text_short,
  address d_text_medium,
  email d_text_short,
  phone d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint buyers_pkey primary key (id),
  constraint buyers_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table sites (
  id bigserial not null,
  type d_site_type not null,
  name d_site_name unique not null,
  owner d_text_short,
  operator_id d_id not null,
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
  utm_origin d_utm,
  utm_east d_utm,
  utm_north_south d_utm,
  utm_west d_utm,
  block_inspection_file_id d_id,
  status d_block_status default 'P' not null,
  file_id d_id,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint blocks_pkey primary key (id),
  constraint blocks_site_id_fkey foreign key (site_id) references sites (id) on update cascade on delete cascade,
  constraint blocks_file_id_fkey foreign key (file_id) references files (id) on update cascade on delete cascade,
  constraint blocks_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint blocks_unique_site_name unique(site_id,name)
);

create table block_inspection_data (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  block_id d_id not null,

  -- constraint block_inspection_data_pkey primary key (id),
  constraint block_inspection_data_block_id foreign key (block_id) references blocks (id) on update cascade on delete cascade,

  constraint block_inspection_data_unique unique(form_type,form_data_id,block_id)
);

create table printjobs (
  id bigserial not null,
  number d_positive_int unique not null,
  site_id d_id,
  allocation_date d_date default current_timestamp not null,
  is_monitored d_bool default false,
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
  barcode d_barcode not null,
  type d_barcode_type default 'P' not null,
  parent_id d_id default null,
  printjob_id d_id not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint barcodes_pkey primary key (id),
  constraint barcodes_parent_id_fkey foreign key (parent_id) references barcodes (id) on update cascade on delete set null,
  constraint barcodes_printjob_id_fkey foreign key (printjob_id) references printjobs (id) on update cascade on delete cascade,
  constraint barcodes_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint barcodes_unique_type unique(barcode,type)
);

create table barcode_hops (
  id bigserial not null,
  barcode_id d_id not null,
  parent_id d_id not null,
  hops d_positive_int not null,

  -- constraint barcode_hops_pkey primary key (id),
  constraint barcode_hops_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,
  constraint barcode_hops_parent_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_hops_unique unique(barcode_id,parent_id),
  constraint barcode_hops_unique_parent unique(barcode_id,hops)
);

create table barcode_locks (
  id bigserial not null,
  barcode_id d_id not null,
  lock d_barcode_lock not null,
  lock_id d_id not null,
  comment d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint barcode_locks_pkey primary key (id),
  constraint barcode_locks_barcode_id foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_locks_unique unique(barcode_id,lock,lock_id)
);

create table barcode_activity (
  id bigserial not null,
  barcode_id d_id not null,
  activity d_barcode_activity default 'P' not null,
  trigger d_text_short default 'system' not null,
  comment d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint barcode_activity_pkey primary key (id),
  constraint barcode_activity_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,
  constraint barcode_activity_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint barcode_activity_comment check (not((activity in ('A','L','Z','C')) and (comment is null)))
);

create table qrcodes (
  id bigserial not null,
  qrcode d_qrcode unique not null,
  type d_qrcode_type default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint qrcodes_pkey primary key (id),
  constraint qrcodes_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table locations (
  id bigserial not null,
  type d_location_type default 'P' not null,
  name d_text_short unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint locations_pkey primary key (id),

  constraint locations_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table location_hops (
  id bigserial not null,
  location_id d_id not null,
  parent_id d_id not null,
  hops d_positive_int not null,

  -- constraint location_hops_cached_pkey primary key (id),
  constraint location_hops_location_id_fkey foreign key (location_id) references locations (id) on update cascade on delete cascade,
  constraint location_hops_parent_id_fkey foreign key (location_id) references locations (id) on update cascade on delete cascade,

  constraint location_hops_unique unique(location_id,parent_id),
  constraint location_hops_unique_parent unique(location_id,hops)
);

create table invoices (
  id bigserial not null,
  type d_invoice_type not null,
  operator_id d_id,
  site_id d_id,
  number d_invoice_number,
  invnumber d_text_short unique,
  is_paid d_bool default false not null,
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
  constraint invoices_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint invoices_number_unique unique(type,number),

  constraint invoices_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null))),
  constraint invoices_check check (not((operator_id is null) and (site_id is null)))
);

create table fees (
  id bigserial not null,
  type d_fee_type not null,
  value d_measurement_float not null,
  name d_text_short unique not null,
  description d_text_medium,
  tax_code d_tax_code unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint fees_pkey primary key (id),

  constraint fees_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table invoice_payments (
  id bigserial not null,
  invoice_id d_id not null,
  number d_text_short unique not null,
  amount d_money not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint invoice_payment_pkey primary key (id),
  constraint invoice_payment_invoice_id foreign key (invoice_id) references invoices (id) on update cascade on delete cascade,
  constraint invoice_payment_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table invoice_data (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  invoice_id d_id not null,

  constraint invoice_data_pkey primary key (id),
  constraint invoice_data_invoice_id foreign key (invoice_id) references invoices (id) on update cascade on delete cascade,

  constraint invoice_data_unique unique(form_type,form_data_id,invoice_id)
);

create table invoice_data_fees (
  id bigserial not null,
  invoice_data_id d_id not null,
  fee_id d_id not null,
  amount d_money not null,

  -- constraint invoice_data_fees_pkey primary key (id),

  constraint invoice_data_fees_invoice_data_id_fkey foreign key (invoice_data_id) references invoice_data (id) on update cascade on delete cascade,
  constraint invoice_data_fees_unique unique(invoice_data_id,fee_id)
);

create table documents (
  id bigserial not null,
  type d_document_type not null,
  operator_id d_id,
  site_id d_id,
  qrcode_id d_id,
  number d_document_number,
  is_draft d_bool default true not null,
  values d_text_long,
  created_date d_date not null,
  file_id d_id unique not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint documents_pkey primary key (id),
  constraint documents_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint documents_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null))),
  constraint documents_check check (not((operator_id is null) and (site_id is null)))
);

create table document_data (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  document_id d_id not null,

  constraint document_data_pkey primary key (id),
  constraint document_data_document_id foreign key (document_id) references documents (id) on update cascade on delete cascade,

  constraint document_data_unique unique(form_type,form_data_id,document_id)
);

create table reports (
  id bigserial not null,
  type d_report_type not null,
  name d_text_short not null unique,
  description d_text_long,
  is_draft d_bool default true not null,
  number d_report_number,
  created_date d_date not null,
  model d_text_short not null,
  tables d_text_long,
  fields d_text_long,
  filters d_text_long,
  "order" d_text_long,
  "offset" d_positive_int,
  "limit" d_positive_int,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint reports_pkey primary key (id),
  constraint reports_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint documents_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null)))
);

create table report_schedules (
  id bigserial not null,
  report_id d_id not null,
  type d_schedule_type not null,
  minute d_schedule,
  hour d_schedule,
  day d_schedule,
  week d_schedule,
  month d_schedule,
  quarter d_schedule,
  year d_schedule,
  created_date d_date not null,
  start_timestamp d_timestamp not null,
  end_timestamp d_timestamp,
  last_timestamp d_timestamp not null,
  user_id d_id not null,

  constraint report_schedules_pkey primary key (id),
  constraint report_schedules_report_id_fkey foreign key (report_id) references reports (id) on update cascade,
  constraint report_schedules_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint report_schedules_minute check ((0 <= all(minute)) and (59 >= all(minute))),
  constraint report_schedules_hour check ((0 <= all(hour)) and (23 >= all(hour))),
  constraint report_schedules_day check (((type in ('O', 'M', 'Y')) and (1 <= all(day)) and (31 >= all(day))) or ((type = 'W') and (1 <= all(day)) and (7 >= all(day)))),
  constraint report_schedules_week check (((type = 'Y') and (1 <= all(week)) and (52 >= all(minute))) or ((type = 'M') and (1 <= all(week)) and (5 >= all(week)))),
  constraint report_schedules_month check ((type in ('O', 'Y')) and (1 <= all(month)) and (12 >= all(month))),
  constraint report_schedules_year check (type = 'Y')
);

create table report_files (
  id bigserial not null,
  report_id d_id not null,
  file_id d_id unique not null,

  constraint report_files_pkey primary key (id),
  constraint report_files_file_id_fkey foreign key (file_id) references files (id) on update cascade,
  constraint report_files_report_id_fkey foreign key (report_id) references reports (id) on update cascade
);

create table csv (
  id bigserial not null,
  file_id d_id not null,
  operation d_operation not null,
  form_type d_operation_type not null,
  form_data_id d_id,
  operator_id d_id,
  site_id d_id,
  block_id d_id,
  original_values d_text_long not null,
  values d_text_long,
  content_md5 d_text_short,
  status d_csv_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint csv_pkey primary key (id),
  constraint csv_file_id_fkey foreign key (file_id) references files (id) on update cascade,
  constraint csv_user_id_fkey foreign key (user_id) references users (id) on update cascade
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
  csv_id d_id,
  duplicate_csv_id d_id,
  field d_text_short,
  is_corrected d_bool default false not null,

  -- constraint csv_duplicates_pkey primary key (id),
  constraint csv_duplicates_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade,
  constraint csv_duplicates_duplicate_csv_id_fkey foreign key (duplicate_csv_id) references csv (id) on update cascade on delete cascade,

  constraint csv_duplicates_unique unique(csv_id,duplicate_csv_id,field),
  constraint csv_duplicates_exist check (csv_id is not null or duplicate_csv_id is not null),

  constraint csv_duplicates_check check (not((csv_id is not null and duplicate_csv_id is not null) and (csv_id > duplicate_csv_id)))
);

create table data (
  id bigserial not null,
  csv_id d_id unique not null,
  form_type d_form_type not null,
  form_data_id d_id unique,
  form_verification_id d_id unique
);

create table ssf_data (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  enumerator d_text_short,
  entered_date d_date,
  entered_by d_text_short,
  checked_date d_date,
  checked_by d_text_short,
  utm_origin d_utm,
  utm_east d_utm,
  utm_north_south d_utm,
  utm_west d_utm,
  survey_line d_survey_line not null,
  cell_number d_positive_int not null,
  tree_map_number d_positive_int not null,
  diameter d_diameter not null,
  height d_length not null,
  volume d_volume not null,
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

create table ssf_verification (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  utm_origin d_utm,
  utm_east d_utm,
  utm_north_south d_utm,
  utm_west d_utm,
  survey_line d_survey_line not null,
  cell_number d_positive_int not null,
  diameter d_diameter not null,
  height d_length not null,
  volume d_volume not null,
  inspected_by d_text_short,
  inspection_date d_date not null,
  inspection_label d_text_short,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint ssf_verification_pkey primary key (id),
  constraint ssf_verification_site_id_fkey foreign key (site_id) references sites (id) on update cascade,
  constraint ssf_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint ssf_verification_block_id_fkey foreign key (block_id) references blocks (id) on update cascade,
  constraint ssf_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint ssf_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint ssf_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
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
  measured_by d_text_short,
  entered_by d_text_short,
  signed_by d_text_short,
  survey_line d_survey_line not null,
  cell_number d_positive_int not null,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  volume d_volume not null,
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

create table tdf_verification (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  volume d_volume not null,
  inspected_by d_text_short,
  inspection_date d_date not null,
  inspection_label d_text_short not null,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint tdf_verification_pkey primary key (id),
  constraint tdf_verification_site_id_fkey foreign key (site_id) references sites (id) on update cascade,
  constraint tdf_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint tdf_verification_block_id_fkey foreign key (block_id) references blocks (id) on update cascade,
  constraint tdf_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint tdf_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint tdf_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table ldf_data (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  barcode_id d_id unique not null,
  parent_barcode_id d_id not null,
  species_id d_id not null,
  measured_by d_text_short,
  entered_by d_text_short,
  form_number d_text_short,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  original_volume d_volume not null,
  volume d_volume not null,
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

create table ldf_verification (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  original_volume d_volume not null,
  volume d_volume not null,
  inspected_by d_text_short,
  inspection_date d_date not null,
  inspection_label d_text_short not null,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint ldf_verification_pkey primary key (id),
  constraint ldf_verification_site_id_fkey foreign key (site_id) references sites (id) on update cascade,
  constraint ldf_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint ldf_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint ldf_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint ldf_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mif_data (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
  batch_start_date d_date,
  batch_end_date d_date,
  production_order_number d_text_short,
  production_line d_text_short,
  product_type d_text_short,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  original_volume d_volume not null,
  volume d_volume not null,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  recorded_by d_text_short,
  submitted_by d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mif_data_pkey primary key (id),
  constraint mif_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mif_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mif_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mif_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mif_verification (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
  batch_start_date d_date,
  batch_end_date d_date,
  production_order_number d_text_short,
  production_line d_text_short,
  product_type d_text_short,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  original_volume d_volume not null,
  volume d_volume not null,
  inspected_by d_text_short,
  inspection_date d_date not null,
  inspection_label d_text_short not null,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  recorded_by d_text_short,
  submitted_by d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mif_verification_pkey primary key (id),
  constraint mif_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mif_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mif_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mif_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mof_data (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
  batch_start_date d_date,
  batch_end_date d_date,
  production_order_number d_text_short,
  production_line d_text_short,
  product_type d_text_short,
  width d_measurement_float not null,
  height d_measurement_float not null,
  length d_length not null,
  grade d_grade not null,
  original_volume d_volume not null,
  volume d_volume not null,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  recorded_by d_text_short,
  submitted_by d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mof_data_pkey primary key (id),
  constraint mof_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mof_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mof_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mof_data_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mof_verification (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
  batch_start_date d_date,
  batch_end_date d_date,
  production_order_number d_text_short,
  production_line d_text_short,
  product_type d_text_short,
  width d_measurement_float not null,
  height d_measurement_float not null,
  length d_length not null,
  grade d_grade not null,
  original_volume d_volume not null,
  volume d_volume not null,
  inspected_by d_text_short,
  inspection_date d_date not null,
  inspection_label d_text_short not null,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  recorded_by d_text_short,
  submitted_by d_text_short,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mof_verification_pkey primary key (id),
  constraint mof_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mof_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mof_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mof_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table specs_data (
  id bigserial not null,
  operator_id d_id not null,
  specs_barcode_id d_id,
  exp_barcode_id d_id,
  contract_number d_text_short,
  barcode_id d_id not null,
  species_id d_id not null,
  loading_date d_date,
  buyer d_text_short,
  submitted_by d_text_short,
  diameter d_diameter not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
  grade d_grade not null,
  original_volume d_volume not null,
  volume d_volume not null,
  origin d_text_short,
  destination d_text_short,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint specs_data_pkey primary key (id),
  constraint specs_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint specs_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint specs_data_specs_barcode_id_fkey foreign key (specs_barcode_id) references barcodes (id) on update cascade,
  constraint specs_data_exp_barcode_id_fkey foreign key (exp_barcode_id) references barcodes (id) on update cascade,
  constraint specs_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint specs_data_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint specs_data_unique_barcode unique(barcode_id,specs_barcode_id)
);

create table wb_data (
  id bigserial not null,
  operator_id d_id not null,
  transport_operator_id d_id not null,
  wb_barcode_id d_id not null,
  barcode_id d_id not null,
  species_id d_id not null,
  diameter d_diameter not null,
  length d_length not null,
  grade d_grade not null,
  original_volume d_volume not null,
  volume d_volume not null,
  origin d_text_short not null,
  origin_date d_date not null,
  destination d_text_short not null,
  destination_date d_date not null,
  unloading_date d_date,
  loading_supervised_by d_text_short,
  receiving_supervised_by d_text_short,
  driver d_text_short,
  truck_number d_text_short,
  entered_by d_text_short,
  comment d_text_long,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint wb_data_pkey primary key (id),
  constraint wb_data_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint wb_data_transport_operator_id_fkey foreign key (transport_operator_id) references operators (id) on update cascade,
  constraint wb_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint wb_data_wb_barcode_id_fkey foreign key (wb_barcode_id) references barcodes (id) on update cascade,
  constraint wb_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint wb_data_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint wb_data_unique_barcode unique(barcode_id,wb_barcode_id)
);

create table checks (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  "check" d_text_short,
  field d_text_short not null,
  params d_text_long,
  type d_error_type default 'E' not null,
  is_ignored d_bool default false not null,

  -- constraint checks_pkey primary key (id),

  constraint checks_unique unique(form_type,form_data_id,field,"check",type)
);

create table verification_checks (
  id bigserial not null,
  form_type d_form_verification_type not null,
  form_verification_id d_id not null,
  "check" d_text_short,
  field d_text_short not null,
  params d_text_long,
  type d_check_type default 'E' not null,
  is_ignored d_bool default false not null,

  -- constraint verification_pkey primary key (id),

  constraint verification_unique unique(form_type,form_verification_id,field,"check",type)
);

create table status_activity (
  id bigserial not null,
  form_type d_form_type not null,
  form_data_id d_id not null,
  old_status d_data_status not null,
  new_status d_data_status not null,
  comment d_text_long,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint status_activity_pkey primary key (id),
  constraint status_activity_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table revisions (
  id bigserial not null,
  model d_text_short not null,
  model_id d_id not null,
  data d_text_long,
  url d_text_long,
  session_id d_id default 1 not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint revisions_pkey primary key (id),
  constraint revisions_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table tolerances (
  id bigserial not null,
  form_type d_form_type not null,
  "check" d_text_short not null,
  accuracy_range d_measurement_float default 0 not null,
  tolerance_range d_measurement_float default 0 not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint tolerances_pkey primary key (id),
  constraint tolerances_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint tolerances_unique unique(form_type,"check")
);

create table settings (
  key d_text_short not null,
  value d_text_long,

  constraint settings_pkey primary key (key)
);


-- sequences

create sequence s_invoices_st_number minvalue 100100;
create sequence s_invoices_exf_number minvalue 100100;
create sequence s_invoices_tag_number minvalue 100100;
create sequence s_documents_specs_number minvalue 1;
create sequence s_documents_exp_number minvalue 1;
create sequence s_documents_cert_number minvalue 250;
create sequence s_report_csv_number minvalue 1;
create sequence s_report_data_number minvalue 1;
create sequence s_report_summary_number minvalue 1;


-- indexes

create index roles_name on roles (id,name);

create index users_username on users (id,username);

create index species_code on species (id,code);
create index species_class on species (id,class);

create index operators_tin on operators (id,tin);

create index sites_name on sites (id,name);
create index sites_type on sites (id,type);

create index files_operation on files (id,operation);
create index files_operation_type on files (id,operation_type);

create index printjobs_number on printjobs (id,number);
create index printjobs_monitored on printjobs (id,is_monitored);

create unique index barcodes_unique on barcodes (barcode) where type not in ('F','L','P');

create index barcode_hops_parent_id on barcode_hops (barcode_id,parent_id);

create index barcodes_barcode on barcodes (id,barcode);
create index barcodes_type on barcodes (id,type);

create index barcode_locks_lock on barcode_locks (barcode_id,lock);

create index barcode_activity_activity on barcode_activity (barcode_id,activity);
create index barcode_activity_activity_trigger on barcode_activity (barcode_id,activity,trigger);
create index barcode_activity_trigger on barcode_activity (barcode_id,trigger);

create index qrcodes_type on qrcodes (id,type);
create index qrcodes_qrcode_type on qrcodes (id,qrcode,type);

create index locations_type on locations (id,type);

create index location_hops_parent_id on location_hops (location_id,parent_id);

create index invoices_type on invoices (id,type);
create index invoices_number on invoices (id,number);
create unique index invoices_type_number on invoices (id,type,number);

create index documents_type on documents (id,type);
create index documents_number on documents (id,number);
create unique index documents_type_number on documents (id,type,number);

create index csv_status on csv (id,status);
create index csv_operation on csv (id,operation);
create index csv_form_type_data_id on csv (id,form_type,form_data_id);

create index csv_errors_field on csv_errors (csv_id,field);
create index csv_errors_error on csv_errors (csv_id,error);

create index csv_duplicates_duplicate_csv_id on csv_duplicates (csv_id,duplicate_csv_id);
create index csv_duplicates_csv_id_type on csv_duplicates (csv_id,field);
create index csv_duplicates_duplicate_csv_id_type on csv_duplicates (duplicate_csv_id,field);
create index csv_duplicates_corrections on csv_duplicates (csv_id,duplicate_csv_id,is_corrected);

create index ssf_data_diameter on ssf_data (id,diameter);
create index ssf_data_height on ssf_data (id,height);
create index ssf_data_volume on ssf_data (id,volume);
create index ssf_data_create_date on ssf_data (id,create_date);
create index ssf_data_status on ssf_data (id,status);

create index ssf_verification_diameter on ssf_verification (id,diameter);
create index ssf_verification_height on ssf_verification (id,height);
create index ssf_verification_create_date on ssf_verification (id,create_date);
create index ssf_verification_status on ssf_verification (id,status);

create index tdf_data_diameter on tdf_data (id,top_min,top_max,bottom_min,bottom_max);
create index tdf_data_length on tdf_data (id,length);
create index tdf_data_volume on tdf_data (id,volume);
create index tdf_data_create_date on tdf_data (id,create_date);
create index tdf_data_status on tdf_data (id,status);

create index tdf_verification_diameter on tdf_verification (id,top_min,top_max,bottom_min,bottom_max);
create index tdf_verification_length on tdf_verification (id,length);
create index tdf_verification_create_date on tdf_verification (id,create_date);
create index tdf_verification_status on tdf_verification (id,status);

create index ldf_data_diameter on ldf_data (id,top_min,top_max,bottom_min,bottom_max);
create index ldf_data_length on ldf_data (id,length);
create index ldf_data_original_volume on ldf_data (id,original_volume,volume);
create index ldf_data_volume on ldf_data (id,volume);
create index ldf_data_create_date on ldf_data (id,create_date);
create index ldf_data_status on ldf_data (id,status);

create index ldf_verification_diameter on ldf_verification (id,top_min,top_max,bottom_min,bottom_max);
create index ldf_verification_length on ldf_verification (id,length);
create index ldf_verification_original_volume on ldf_verification (id,original_volume,volume);
create index ldf_verification_volume on ldf_verification (id,volume);
create index ldf_verification_create_date on ldf_verification (id,create_date);
create index ldf_verification_status on ldf_verification (id,status);

create index mif_data_diameter on mif_data (id,top_min,top_max,bottom_min,bottom_max);
create index mif_data_length on mif_data (id,length);
create index mif_data_original_volume on mif_data (id,original_volume,volume);
create index mif_data_volume on mif_data (id,volume);
create index mif_data_batch_number on mif_data (id,batch_number);
create index mif_data_conversion_factor on mif_data (id,conversion_factor);
create index mif_data_create_date on mif_data (id,create_date);
create index mif_data_status on mif_data (id,status);

create index mif_verification_diameter on mif_verification (id,top_min,top_max,bottom_min,bottom_max);
create index mif_verification_length on mif_verification (id,length);
create index mif_verification_original_volume on mif_verification (id,original_volume,volume);
create index mif_verification_volume on mif_verification (id,volume);
create index mif_verification_batch_number on mif_verification (id,batch_number);
create index mif_verification_conversion_factor on mif_verification (id,conversion_factor);
create index mif_verification_create_date on mif_verification (id,create_date);
create index mif_verification_status on mif_verification (id,status);

create index mof_data_dimension on mof_data (id,length,width,height);
create index mof_data_original_volume on mof_data (id,original_volume,volume);
create index mof_data_volume on mof_data (id,volume);
create index mof_data_batch_number on mof_data (id,batch_number);
create index mof_data_grade on mof_data (id,grade);
create index mof_data_create_date on mof_data (id,create_date);
create index mof_data_status on mof_data (id,status);

create index mof_verification_dimension on mof_verification (id,length,width,height);
create index mof_verification_original_volume on mof_verification (id,original_volume,volume);
create index mof_verification_volume on mof_verification (id,volume);
create index mof_verification_batch_number on mof_verification (id,batch_number);
create index mof_verification_grade on mof_verification (id,grade);
create index mof_verification_create_date on mof_verification (id,create_date);
create index mof_verification_status on mof_verification (id,status);

create index specs_data_diameter on specs_data (id,top_min,top_max,bottom_min,bottom_max);
create index specs_data_origin on specs_data (id,origin);
create index specs_data_destination on specs_data (id,destination);
create index specs_data_original_volume on specs_data (id,original_volume,volume);
create index specs_data_volume on specs_data (id,volume);
create index specs_data_grade on specs_data (id,grade);
create index specs_data_contract_number on specs_data (id,contract_number);
create index specs_data_loading_date on specs_data (id,loading_date);
create index specs_data_create_date on specs_data (id,create_date);
create index specs_data_status on specs_data (id,status);

create index checks_form_type_data_id on checks (form_type,form_data_id);
create index checks_field on checks (form_type,form_data_id,field);
create index checks_check on checks (form_type,form_data_id,"check");
create index checks_type on checks (id,form_type,form_data_id,type);

create index verification_checks_form_type_verification_id on verification_checks (form_type,form_verification_id);
create index verification_checks_field on verification_checks (form_type,form_verification_id,field);
create index verification_checks_check on verification_checks (form_type,form_verification_id,"check");
create index verification_checks_type on verification_checks (id,form_type,form_verification_id,type);

create index revisions_model on revisions (model,model_id);

create index tolerances_form_type_check on tolerances (form_type,"check");


-- language

-- create language plpgsql;


-- functions

create function lookup_barcode_id(x_barcode character varying(13), x_type character varying(1))
  returns d_id as
$$
  declare x_id d_id;
begin

  if x_type is null then
    select id from barcodes where barcode = x_barcode limit 1 into x_id;
  else
    select id from barcodes where barcode = x_barcode and type = x_type limit 1 into x_id;
  end if;

  return x_id;

end
$$ language 'plpgsql';


create function lookup_qrcode_id(x_hash character(64))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from qrcodes where qrcode = x_qrcode limit 1 into x_id;
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


create function lookup_invoice_id(x_type character varying(5), x_number numeric(6))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from invoices where type = x_type and number = x_number limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_document_id(x_type character varying(5), x_number numeric(6))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from documents where type = x_type and number = x_number limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create function lookup_fee_id(x_tax_code character varying(50))
  returns d_id as
$$
  declare x_id d_id;
begin

  select id from fees where tax_code = x_tax_code limit 1 into x_id;
  return x_id;

end
$$ language 'plpgsql';


create or replace function calculate_volume_and_diameter()
  returns trigger as
$$
  declare x_volume d_volume;
  declare x_diameter d_diameter;
begin
  select (3.1426 * power(((((new.top_min + new.top_max + new.bottom_min + new.bottom_max)::real / 4) / 100) / 2), 2) * new.length)::d_volume into x_volume;
  select ((new.top_min + new.top_max + new.bottom_min + new.bottom_max) / 4)::d_diameter into x_diameter;

  new.volume = x_volume;
  new.diameter = x_diameter;

  return new;
end
$$ language 'plpgsql';


create function sites_parse_type()
  returns trigger as
$$
  declare x_site text[];
begin
  if new.name is not null then
    select regexp_matches(new.name::text, E'^(TSC|PUP|FMC|CFMA)([\\s_-]*[A-Z0-9]{1,10})?$') into x_site;
    new.type = x_site[1];
  end if;

  return new;
end
$$ language 'plpgsql';


create function locations_hops()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') then
    perform rebuild_location_hops(new.id);
  elseif (tg_op = 'UPDATE') then
    delete from location_hops where location_id = new.id;
    perform rebuild_location_hops(new.id);
  elseif (tg_op = 'DELETE') then
    delete from location_hops where location_id = old.id;
  end if;

  return null;
end
$$ language 'plpgsql';


create function rebuild_location_hops(x_location_id d_id)
  returns void as
$$
  declare x_id d_id;
  declare x_hops d_positive_int;
begin
  if (x_location_id is null) then
    truncate location_hops;

    for x_id in select id from locations where parent_id is not null loop
      perform rebuild_location_hops(x_id);
    end loop;
  else
    delete from location_hops where location_id = x_location_id;
    select parent_id from locations where id = x_location_id and parent_id is not null into x_id;

    x_hops = 1;
    while x_id is not null loop
      insert into location_hops(location_id,parent_id,hops)
      values(x_location_id,x_id,x_hops);
      x_hops = x_hops + 1;
      select parent_id from locations where id = x_id into x_id;
    end loop;

    for x_id in select id from locations where parent_id = x_location_id loop
      perform rebuild_location_hops(x_id);
    end loop;
  end if;
end
$$ language 'plpgsql';


create function barcodes_hops()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') then
    perform rebuild_barcode_hops(new.id);
  elseif (tg_op = 'UPDATE') then
    delete from barcode_hops where barcode_id = new.id;
    perform rebuild_barcode_hops(new.id);
  elseif (tg_op = 'DELETE') then
    delete from barcode_hops where barcode_id = old.id;
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
  if (x_barcode_id is null) then
    truncate barcode_hops;

    for x_id in select id from barcodes where parent_id is not null loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  else
    delete from barcode_hops where barcode_id = x_barcode_id;
    select parent_id from barcodes where id = x_barcode_id and parent_id is not null into x_id;

    begin
      x_hops = 1;
      while x_id is not null loop
        insert into barcode_hops(barcode_id,parent_id,hops)
        values(x_barcode_id,x_id,x_hops);
        x_hops = x_hops + 1;
        select parent_id from barcodes where id = x_id into x_id;
      end loop;
    exception
      when integrity_constraint_violation then
        raise warning 'barcode id(%) hierarchy cannot be determined', x_id;
        delete from barcode_hops where barcode_id = x_id or parent_id = x_id;
        return;
    end;

    for x_id in select id from barcodes where parent_id = x_barcode_id loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  end if;
end
$$ language 'plpgsql';


create function check_barcode_locks()
  returns trigger as
$$
  declare x_is_locked d_bool;
begin
  select true from barcode_locks where barcode_id = old.barcode_id limit 1 into x_is_locked;

  if (tg_op = 'UPDATE') then
    if (x_is_locked = true) and (old.status = 'A') then
      raise notice 'barcode id(%) is locked and cannot be updated', old.barcode_id;
      return null;
    else
      return new;
    end if;
  elseif (tg_op = 'DELETE') then
    if (x_is_locked = true) then
      raise notice 'barcode id(%) is locked and cannot be deleted', old.barcode_id;
      return null;
    else
      return old;
    end if;
  end if;

end
$$ language 'plpgsql';


create function verification_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    delete from barcode_activity where id in (select id from barcode_activity where barcode_id = old.id and activity = 'N' and trigger = 'verification' limit 1);
    delete from barcode_locks where barcode_id = old.barcode_id and lock = 'VERIFY' and lock_id = old.id;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    insert into barcode_activity (barcode_id,activity,trigger) values (new.id,'N','verification');
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (new.barcode_id,'VERIFY',new.id,new.user_id);
  else  end if;

  return null;
end
$$ language 'plpgsql';


create function barcode_locks_update_locks()
  returns trigger as
$$
  declare x_id d_id;
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    delete from barcode_locks where lock = 'BRCODE' and lock_id = new.barcode_id and barcode_id <> new.barcode_id;
    for x_id in select barcode_id from barcode_hops where parent_id = new.barcode_id loop
      insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_id,'BRCODE',new.barcode_id,new.user_id);
    end loop;

  elseif (tg_op = 'DELETE') then
    delete from barcode_locks where lock = 'BRCODE' and lock_id = old.barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';


create function barcode_activity_update_barcodes()
  returns trigger as
$$
  declare x_locked d_bool;
begin
  select exists(select lock from barcode_locks where barcode_id = new.barcode_id) into x_locked;

  case new.activity
    when 'S' then delete from barcode_activity where activity in ('E','O') and barcode_id = new.barcode_id;
    else null;
  end case;

  if (x_locked = false) and (new.activity in ('H','T','X','E','S','Y','A','L','Z')) then
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (new.barcode_id,'BRCODE',new.barcode_id,new.user_id);
  end if;

  return null;
end
$$ language 'plpgsql';


create function barcodes_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') then
    insert into barcode_activity (barcode_id,activity,trigger) values (new.id,'P','barcodes');
  end if;

  if (tg_op = 'UPDATE') then
    if (old.type = 'P') and (new.type <> 'P') then
      insert into barcode_activity (barcode_id,activity,trigger) values (new.id,'I','barcodes');
    elseif (old.type <> 'P') and (new.type = 'P') then
      insert into barcode_activity (barcode_id,activity,trigger) values (new.id,'P','barcodes');
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function ssf_data_update_barcodes()
  returns trigger as
$$
  declare x_barcode_type d_barcode_type;
begin
  if tg_op = 'DELETE' then
    select type from barcodes where id = old.barcode_id into x_barcode_type;

    if (old.barcode_id is not null) and (x_barcode_type = 'T') then
      update barcodes set type = 'P', parent_id = null where id = old.barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'T' where id = new.barcode_id;
    else
      if old.barcode_id is not null then
        update barcodes set type = 'T' where id = old.barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function tdf_data_update_barcodes()
  returns trigger as
$$
  declare x_barcode_type d_barcode_type;
  declare x_stump_barcode_type d_barcode_type;
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.stump_barcode_id) or (new.barcode_id = new.tree_barcode_id) or (new.tree_barcode_id = new.stump_barcode_id) then
      return null;
    end if;
  end if;

  if tg_op = 'DELETE' then
    select type from barcodes where id = old.barcode_id into x_barcode_type;
    select type from barcodes where id = old.stump_barcode_id into x_stump_barcode_type;

    if (old.barcode_id is not null) and (x_barcode_type = 'F') then
      update barcodes set type = 'P', parent_id = null where id = old.barcode_id;
    end if;
    if (old.stump_barcode_id is not null) and (x_stump_barcode_type = 'S') then
      update barcodes set type = 'P', parent_id = null where id = old.stump_barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'F' where id = new.barcode_id;
    else 
      if old.barcode_id is not null then
        update barcodes set type = 'F' where id = old.barcode_id;
      end if;
    end if;

    if new.tree_barcode_id is not null then
      if new.barcode_id is not null then
        update barcodes set parent_id = new.tree_barcode_id where id = new.barcode_id;
      else
        if old.barcode_id is not null then
          update barcodes set parent_id = new.tree_barcode_id where id = old.barcode_id;
        end if;
      end if;

      if new.stump_barcode_id is not null then
        update barcodes set parent_id = new.tree_barcode_id where id = new.stump_barcode_id;
      else
        if old.stump_barcode is not null then
          update barcodes set parent_id = new.tree_barcode_id where id = old.stump_barcode_id;
        end if;
      end if;
    else
      if old.tree_barcode_id is not null then
        if new.barcode_id is not null then
          update barcodes set parent_id = old.tree_barcode_id where id = new.barcode_id;
        else
          if old.barcode_id is not null then
            update barcodes set parent_id = old.tree_barcode_id where id = old.barcode_id;
          end if;
        end if;

        if new.stump_barcode_id is not null then
          update barcodes set parent_id = old.tree_barcode_id where id = new.stump_barcode_id;
        else
          if old.stump_barcode is not null then
            update barcodes set parent_id = old.tree_barcode_id where id = old.stump_barcode_id;
          end if;
        end if;
      end if;
    end if;

    if new.stump_barcode_id is not null then
      update barcodes set type = 'S' where id = new.stump_barcode_id;
    else
      if old.stump_barcode_id is not null then
        update barcodes set type = 'S' where id = old.stump_barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function ldf_data_update_barcodes()
  returns trigger as
$$
  declare x_barcode_type d_barcode_type;
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.parent_barcode_id) then
      return null;
    end if;
  end if;

  if tg_op = 'DELETE' then
    select type from barcodes where id = old.barcode_id into x_barcode_type;

    if (old.barcode_id is not null) and (x_barcode_type = 'L') then
      update barcodes set type = 'P', parent_id = null where id = old.barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where id = new.barcode_id;

      if new.parent_barcode_id is not null then
        update barcodes set parent_id = new.parent_barcode_id where id = new.barcode_id;
      else
        if new.parent_barcode_id is not null then
          update barcodes set parent_id = old.parent_barcode_id where id = old.barcode_id;
        end if;
      end if;
    else
      if old.barcode_id is not null then
        update barcodes set type = 'L' where id = old.barcode_id;

        if new.parent_barcode_id is not null then
          update barcodes set parent_id = new.parent_barcode_id where id = old.barcode_id;
        else
          if new.parent_barcode_id is not null then
            update barcodes set parent_id = old.parent_barcode_id where id = old.barcode_id;
          end if;
        end if;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function mif_data_update_barcodes()
  returns trigger as
$$
begin
  return null;
end
$$ language 'plpgsql';


create function mof_data_update_barcodes()
  returns trigger as
$$
  declare x_barcode_type d_barcode_type;
begin
  if tg_op = 'DELETE' then
    select type from barcodes where id = old.barcode_id into x_barcode_type;

    if (old.barcode_id is not null) and (x_barcode_type = 'B') then
      update barcodes set type = 'P', parent_id = null where id = old.barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'B' where id = new.barcode_id;
    else
      if old.barcode_id is not null then
        update barcodes set type = 'B' where id = old.barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function specs_data_update_barcodes()
  returns trigger as
$$
  declare x_specs_barcode_type d_barcode_type;
  declare x_exp_barcode_type d_barcode_type;
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.specs_barcode_id) or (new.barcode_id = new.exp_barcode_id) or (new.exp_barcode_id = new.specs_barcode_id) then
      return null;
    end if;
  end if;

  if tg_op = 'DELETE' then
    select type from barcodes where id = old.specs_barcode_id into x_specs_barcode_type;
    select type from barcodes where id = old.exp_barcode_id into x_exp_barcode_type;

    if (old.specs_barcode_id is not null) and (x_specs_barcode_type = 'H') then
      update barcodes set type = 'P', parent_id = null where id = old.specs_barcode_id;
    end if;
    if (old.exp_barcode_id is not null) and (x_exp_barcode_type = 'E') then
      update barcodes set type = 'P', parent_id = null where id = old.exp_barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
        update barcodes set type = 'H' where id = new.specs_barcode_id;
    else
      if old.barcode_id is not null then
        update barcodes set type = 'H' where id = old.specs_barcode_id;
      end if;
    end if;

    if new.exp_barcode_id is not null then
      update barcodes set type = 'E' where id = new.exp_barcode_id;
    else
      if old.exp_barcode_id is not null then
        update barcodes set type = 'E' where id = old.exp_barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function wb_data_update_barcodes()
  returns trigger as
$$
  declare x_wb_barcode_type d_barcode_type;
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.wb_barcode_id) then
      return null;
    end if;
  end if;

  if tg_op = 'DELETE' then
    select type from barcodes where id = old.wb_barcode_id into x_wb_barcode_type;

    if (old.wb_barcode_id is not null) and (x_wb_barcode_type = 'W') then
      update barcodes set type = 'P', parent_id = null where id = old.wb_barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'W' where id = new.wb_barcode_id;
    else
      if old.barcode_id is not null then
        update barcodes set type = 'W' where id = old.wb_barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function invoices_update_data()
  returns trigger as
$$
begin
  update invoice_data set form_data_id = form_data_id where invoice_id = new.id;
  return null;
end
$$ language 'plpgsql';


create function invoice_data_update_barcodes()
  returns trigger as
$$
  declare x_data record;
  declare x_invoice record;
  declare x_form_type d_form_type;
  declare x_form_data_id d_id;
begin

  if (tg_op = 'DELETE') then
    select old.form_type into x_form_type;
    select old.form_data_id into x_form_data_id;
  else
    select new.form_type into x_form_type;
    select new.form_data_id into x_form_data_id;
  end if;

  case x_form_type
    when 'SSF'   then select barcode_id,user_id from ssf_data where id = x_form_data_id into x_data;
    when 'TDF'   then select barcode_id,user_id from tdf_data where id = x_form_data_id into x_data;
    when 'LDF'   then select barcode_id,user_id from ldf_data where id = x_form_data_id into x_data;
    when 'MIF'   then select barcode_id,user_id from mif_data where id = x_form_data_id into x_data;
    when 'MOF'   then select barcode_id,user_id from mof_data where id = x_form_data_id into x_data;
    when 'SPECS' then select barcode_id,user_id from specs_data where id = x_form_data_id into x_data;
    else null;
  end case;

  if x_data is null then
    return null;
  end if;

  if (tg_op = 'DELETE') then
    select type,number,is_draft from invoices where id = old.invoice_id into x_invoice;
    case x_invoice.type
      when 'ST'  then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('T') and trigger = 'invoice_data');
      when 'EXF' then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('X') and trigger = 'invoice_data');
      else null;
    end case;
    delete from barcode_locks where barcode_id = x_data.barcode_id and lock = 'INV' and lock_id = old.invoice_id;
  else
    if (tg_op = 'INSERT') then
      insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_data.barcode_id,'INV',new.invoice_id,x_data.user_id);
    end if;

    select type,number,is_draft from invoices where id = new.invoice_id into x_invoice;
    if (x_invoice.is_draft = false) then
      case x_invoice.type
        when 'ST'  then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'T','invoice_data');
        when 'EXF' then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'X','invoice_data');
        else null;
      end case;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create function documents_update_data()
  returns trigger as
$$
begin
  update document_data set form_data_id = form_data_id where document_id = new.id;
  return null;
end
$$ language 'plpgsql';


create function document_data_update_barcodes()
  returns trigger as
$$
  declare x_data record;
  declare x_document record;
  declare x_form_type d_form_type;
  declare x_form_data_id d_id;
begin
  if (tg_op = 'DELETE') then
    select old.form_type into x_form_type;
    select old.form_data_id into x_form_data_id;
  else
    select new.form_type into x_form_type;
    select new.form_data_id into x_form_data_id;
  end if;

  case x_form_type
    when 'SSF'   then select barcode_id,user_id from ssf_data where id = x_form_data_id into x_data;
    when 'TDF'   then select barcode_id,user_id from tdf_data where id = x_form_data_id into x_data;
    when 'LDF'   then select barcode_id,user_id from ldf_data where id = x_form_data_id into x_data;
    when 'MIF'   then select barcode_id,user_id from mif_data where id = x_form_data_id into x_data;
    when 'MOF'   then select barcode_id,user_id from mof_data where id = x_form_data_id into x_data;
    when 'SPECS' then select barcode_id,user_id from specs_data where id = x_form_data_id into x_data;
    else null;
  end case;

  if x_data is null then
    return null;
  end if;

  if (tg_op = 'DELETE') then
    select type,number,is_draft from documents where id = old.document_id into x_document;
    case x_document.type
      when 'EXP' then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('E') and trigger = 'document_data' limit 1);
      when 'SPECS' then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('D') and trigger = 'document_data' limit 1);
      else null;
    end case;
    delete from barcode_locks where barcode_id = x_data.barcode_id and lock = 'DOC' and lock_id = old.document_id;
  else
    if (tg_op = 'INSERT') then
      insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_data.barcode_id,'DOC',new.document_id,x_data.user_id);
    end if;

    select type,number,is_draft from documents where id = new.document_id into x_document;
    if (x_document.is_draft = false) then
      case x_document.type
        when 'EXP' then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'E','document_data');
        when 'SPECS' then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'D','document_data');
        else null;
      end case;
    end if;
  end if;

  return null;
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

create trigger t_tdf_data_volume_and_diameter
  before insert or update on tdf_data
  for each row
  execute procedure calculate_volume_and_diameter();

create trigger t_tdf_data_volume_and_diameter
  before insert or update on tdf_verification
  for each row
  execute procedure calculate_volume_and_diameter();

create trigger t_ldf_data_volume_and_diameter
  before insert or update on ldf_data
  for each row
  execute procedure calculate_volume_and_diameter();

create trigger t_ldf_data_volume_and_diameter
  before insert or update on ldf_verification
  for each row
  execute procedure calculate_volume_and_diameter();

create trigger t_specs_data_volume_and_diameter
  before insert or update on specs_data
  for each row
  execute procedure calculate_volume_and_diameter();

create trigger t_sites_parse_type
  before insert or update on sites
  for each row
  execute procedure sites_parse_type();

create trigger t_barcodes_hops
  after insert or delete or update on barcodes
  for each row
  execute procedure barcodes_hops();

create trigger t_locations_hops
  after insert or delete or update on barcodes
  for each row
  execute procedure locations_hops();

create trigger t_barcodes_update_barcodes
  after insert or update or delete on barcodes
  for each row
  execute procedure barcodes_update_barcodes();

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

create trigger t_mif_data_update_barcodes
  after insert or update or delete on mif_data
  for each row
  execute procedure mif_data_update_barcodes();

create trigger t_mof_data_update_barcodes
  after insert or update or delete on mof_data
  for each row
  execute procedure mof_data_update_barcodes();

create trigger t_specs_data_update_barcodes
  after insert or update or delete on specs_data
  for each row
  execute procedure specs_data_update_barcodes();

create trigger t_ssf_verification_update_barcodes
  after insert or update or delete on ssf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_tdf_verification_update_barcodes
  after insert or update or delete on tdf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_ldf_verification_update_barcodes
  after insert or update or delete on ldf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_mif_verification_update_barcodes
  after insert or update or delete on mif_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_mof_verification_update_barcodes
  after insert or update or delete on mof_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_check_barcode_locks
  before update or delete on ssf_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before update or delete on tdf_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before update or delete on ldf_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before update or delete on mof_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before update or delete on mif_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before update or delete on specs_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_invoice_data_update_barcodes
  after insert or update or delete on invoice_data
  for each row
  execute procedure invoice_data_update_barcodes();

create trigger t_document_data_update_barcodes
  after insert or update or delete on document_data
  for each row
  execute procedure document_data_update_barcodes();

create trigger t_barcode_locks_update_locks
  after insert or update or delete on barcode_locks
  for each row
  execute procedure barcode_locks_update_locks();

create trigger t_barcode_activity_update_barcodes
  after insert or update on barcode_activity
  for each row
  execute procedure barcode_activity_update_barcodes();

create trigger t_invoices_update_data
  after update on invoices
  for each row
  execute procedure invoices_update_data();

create trigger t_documents_update_data
  after update on documents
  for each row
  execute procedure documents_update_data();

-- create trigger t_csv_integrity
--   before update on csv
--   for each row
--   execute procedure csv_integrity();
