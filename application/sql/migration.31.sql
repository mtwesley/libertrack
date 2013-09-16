
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

update tdf_data set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);
update ldf_data set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);
update mif_data set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);
update specs_data set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);

update tdf_verification set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);
update ldf_verification set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);
update mif_verification set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);

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
  model d_text_short not null,
  tables d_text_long,
  fields d_text_long,
  filters d_text_long,
  offset d_positive_int,
  limit d_positive_int,
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


-- fixing barcodes

create or replace function ssf_data_update_barcodes()
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

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.barcode_id is not null then
      update barcodes set type = 'T' where id = new.barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function tdf_data_update_barcodes()
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

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.barcode_id is not null then
      update barcodes set type = 'F' where id = new.barcode_id;

      if new.tree_barcode_id is not null then
        update barcodes set parent_id = new.tree_barcode_id where id = new.barcode_id;

        if new.stump_barcode_id is not null then
          update barcodes set parent_id = new.tree_barcode_id where id = new.stump_barcode_id;
        end if;
      end if;
    end if;

    if new.stump_barcode_id is not null then
      update barcodes set type = 'S' where id = new.stump_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function ldf_data_update_barcodes()
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

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where id = new.barcode_id;

      if new.parent_barcode_id is not null then
        update barcodes set parent_id = new.parent_barcode_id where id = new.barcode_id;
      end if;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function mif_data_update_barcodes()
  returns trigger as
$$
begin
  return null;
end
$$ language 'plpgsql';


create or replace function mof_data_update_barcodes()
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

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.barcode_id is not null then
      update barcodes set type = 'B' where id = new.barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function specs_data_update_barcodes()
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

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.specs_barcode_id is not null then
      update barcodes set type = 'H' where id = new.specs_barcode_id;
    end if;

    if new.exp_barcode_id is not null then
      update barcodes set type = 'E' where id = new.exp_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function wb_data_update_barcodes()
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

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') and (new.status <> 'R') then
    if new.wb_barcode_id is not null then
      update barcodes set type = 'W' where id = new.wb_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


-- some tiny change

create or replace function barcode_activity_update_barcodes()
  returns trigger as
$$
  declare x_locked d_bool;
begin
  select exists(select lock from barcode_locks where barcode_id = new.barcode_id) into x_locked;

  case new.activity
    when 'S' then delete from barcode_activity where activity = 'E' and barcode_id = new.barcode_id;
    else null;
  end case;

  if (x_locked = false) and (new.activity in ('H','T','X','E','S','Y','A','L','Z')) then
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (new.barcode_id,'BRCODE',new.barcode_id,new.user_id);
  end if;

  return null;
end
$$ language 'plpgsql';


-- barcode activity

alter domain d_barcode_activity drop constraint d_barcode_activity_check;
alter domain d_barcode_activity add check (value ~ E'^[PIHTXDNEOSYALZC]$');

create or replace function barcode_activity_update_barcodes()
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


-- draft reports

alter table reports add column is_draft d_bool default true not null;

alter table reports add constraint documents_final_check check (not((is_draft = false and number is not null) and (is_draft <> false and number is null)));

create domain d_schedule_type as character(1) check (value ~ E'^[ODWMQY]$');

create table report_schedule (
  id bigserial not null,
  report_id d_id not null,
  type d_schedule_type not null,
  minute d_measurement_int[],
  hour d_measurement_int[],
  day d_positive_int[],
  week d_positive_int[],
  month d_positive_int[],
  quarter d_positive_int[],
  year d_positive_int[],
  created_date d_date not null,
  start_date d_date not null,
  end_date d_date,
  last_timestamp d_timestamp not null,
  user_id d_id not null,

  constraint reports_pkey primary key (id),
  constraint reports_report_id_fkey foreign key (report_id) references reports (id) on update cascade,
  constraint reports_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

