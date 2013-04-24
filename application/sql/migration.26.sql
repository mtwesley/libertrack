
-- csv type
-- INSTEAD OF USING CSV_TYPE, JUST CHECK IF TYPE IS IN ARRAY OF $FORM_DATA_TYPE OR $FORM_VALIDATION_TYPE
-- create domain d_csv_type as character varying(1) check (value ~ E'^(D|V)$');
-- alter table csv add column type d_csv_type default 'D' not null;
-- alter table csv alter column type drop default;


-- remove csv id

alter table csv drop column other_csv_id;
alter table ssf_data drop column csv_id;
alter table tdf_data drop column csv_id;
alter table ldf_data drop column csv_id;
alter table specs_data drop column csv_id;
alter table mof_data drop column csv_id;
alter table mif_data drop column csv_id;


-- remove is_deleted

alter table users drop column is_deleted;
alter table species drop column is_deleted;
alter table operators drop column is_deleted;
alter table sites drop column is_deleted;
alter table blocks drop column is_deleted;


-- operation

alter domain d_operation drop constraint d_operation_check;
update files set operation = 'D' where operation = 'E';
update files set operation = 'U' where operation not in ('U','D');
update csv set operation = 'D' where operation = 'E';
update csv set operation = 'U' where operation not in ('U','D');
alter domain d_operation add check (value ~ E'^[UD]$');


-- domains

create domain d_form_data_type as character varying(5) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS)$');
create domain d_form_verification_type as character varying(6) check (value ~ E'^(SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV)$');

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|EXP|INV|DOC|PJ|UNKWN)$');

alter table csv alter column csv type d_operation_type;


-- original volumes

alter table ldf_data add column original_volume d_volume;
alter table specs_data add column original_volume d_volume;
alter table mif_data add column original_volume d_volume not null;
alter table mof_data add column original_volume d_volume not null;

alter table ldf_data disable trigger all;
update ldf_data set original_volume = volume;
update ldf_data set volume = (pi() * ((((top_min + top_max + bottom_min + bottom_max)::real / 4) / 100) / 2)^2 * length);
alter table ldf_data enable trigger all;

alter table specs_data disable trigger all;
update specs_data set original_volume = volume;
update specs_data set volume = (pi() * ((((top_min + top_max + bottom_min + bottom_max)::real / 4) / 100) / 2)^2 * length);
alter table specs_data enable trigger all;

alter table ldf_data alter column original_volume set not null;
alter table specs_data alter column original_volume set not null;


-- tables

create table ssf_verification (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  survey_line d_survey_line not null,
  cell_number d_positive_int not null,
  diameter d_diameter not null,
  height d_length not null,
  inspected_by d_text_short,
  inspection_date d_date not null,
  inspection_label d_text_short not null,
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

create table tdf_verification (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  block_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  top_min d_diameter not null,
  top_max d_diameter not null,
  bottom_min d_diameter not null,
  bottom_max d_diameter not null,
  length d_length not null,
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

create table ldf_verification (
  id bigserial not null,
  site_id d_id not null,
  operator_id d_id not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
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

create table mif_verification (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
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

  constraint mif_verification_pkey primary key (id),
  constraint mif_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mif_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mif_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mif_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table mof_verification (
  id bigserial not null,
  operator_id d_id not null,
  conversion_factor d_conversion_factor not null,
  barcode_id d_id unique not null,
  species_id d_id not null,
  batch_number d_positive_int not null,
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
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint mof_verification_pkey primary key (id),
  constraint mof_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint mof_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint mof_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint mof_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);

create table specs_verification (
  id bigserial not null,
  operator_id d_id not null,
  barcode_id d_id not null,
  species_id d_id not null,
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

  constraint specs_verification_pkey primary key (id),
  constraint specs_verification_operator_id_fkey foreign key (operator_id) references operators (id) on update cascade,
  constraint specs_verification_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint specs_verification_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint specs_verification_user_id_fkey foreign key (user_id) references users (id) on update cascade
);




-- locations

alter table barcode_activity add constraint barcode_activity_user_id_fkey foreign key (user_id) references users (id) on update cascade;

create domain d_location_type as character(1) check (value ~ E'^[P]$');

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


-- updates to locks and hops

create or replace function check_barcode_locks()
  returns trigger as
$$
  declare x_is_locked d_bool;
begin
  select true from barcode_locks where barcode_id = old.barcode_id limit 1 into x_is_locked;

  if (tg_op = 'UPDATE') and (x_is_locked = true) and (old.status = 'A') then
    raise notice 'barcode id(%) is locked and connot be updated', old.barcode_id;
    return null;
  else
    return new;
  end if;

  if (tg_op = 'DELETE') and (x_is_locked = true) then
    raise notice 'barcode id(%) is locked and connot be deleted', old.barcode_id;
    return null;
  else
    return old;
  end if;

end
$$ language 'plpgsql';

create or replace function rebuild_barcode_hops(x_barcode_id d_id)
  returns void as
$$
  declare x_id d_id;
  declare x_hops d_positive_int;
begin
  if (x_barcode_id is null) then
    truncate barcode_hops_cached;

    for x_id in select id from barcodes where parent_id is not null loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  else
    delete from barcode_hops_cached where barcode_id = x_barcode_id;
    select parent_id from barcodes where id = x_barcode_id and parent_id is not null into x_id;

    begin
      x_hops = 1;
      while x_id is not null loop
        insert into barcode_hops_cached(barcode_id,parent_id,hops)
        values(x_barcode_id,x_id,x_hops);
        x_hops = x_hops + 1;
        select parent_id from barcodes where id = x_id into x_id;
      end loop;
    exception
      when integrity_constraint_violation then
        raise warning 'barcode id(%) hierarchy cannot be determined', x_id;
        delete from barcode_hops_cached where barcode_id = x_id or parent_id = x_id;
        return;
    end;

    for x_id in select id from barcodes where parent_id = x_barcode_id loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  end if;
end
$$ language 'plpgsql';

