
-- waybills

alter domain d_form_type drop constraint d_form_type_check;
alter domain d_form_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB)$');

alter domain d_form_data_type drop constraint d_form_data_type_check;
alter domain d_form_data_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB)$');

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|WB|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|VERIFY|EXP|INV|DOC|PJ|UNKWN)$');

alter domain d_barcode_type drop constraint d_barcode_type_check;
alter domain d_barcode_type add check (value ~ E'^[PTFSLRHEW]$');


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


create function wb_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.wb_barcode_id) then
      return null;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where barcodes.id = new.barcode_id;
    end if;

    if new.wb_barcode_id is not null then
      update barcodes set type = 'W' where barcodes.id = new.wb_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


-- barcode locks

alter table barcode_locks add column comment d_text_long;
alter table barcode_activity add column comment d_text_long;


-- barcode activity

create or replace function barcode_activity_update_barcodes()
  returns trigger as
$$
  declare x_locked d_bool;
begin
  select exists(select lock from barcode_locks where barcode_id = new.barcode_id) into x_locked;

  case new.activity
    when 'S' then delete from barcode_activity where activity = 'E';
    else null;
  end case;

  if (x_locked != true) and (new.activity in ('H','T','X','E','S','Y','A','L','Z')) then
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (new.barcode_id,'BRCODE',new.barcode_id,new.user_id);
  end if;

  return null;
end
$$ language 'plpgsql';

create trigger t_barcode_activity_update_barcodes
  after insert or update on barcode_activity
  for each row
  execute procedure barcode_activity_update_barcodes();


-- site name update

create or replace function sites_parse_type()
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


alter table sites alter column type type character varying(4);
drop domain d_site_type;

create domain d_site_type as character varying(4) check (value ~ E'^(TSC|PUP|FMC|CFMA)$');
alter table sites alter column type type d_site_type;

alter domain d_site_name drop constraint d_site_name_check;
alter domain d_site_name add check (value ~ E'^(TSC|PUP|FMC|CFMA)[\\s_-]*[A-Z0-9]{1,9}$');

