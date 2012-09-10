
-- trigger updates

create or replace function ssf_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = NULL where barcodes.id = old.barcode_id;
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


create or replace function tdf_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = NULL where barcodes.id = old.barcode_id;
    end if;
    if old.stump_barcode_id is not null then
      update barcodes set type = 'P', parent_id = NULL where barcodes.id = old.stump_barcode_id;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where barcodes.id = new.barcode_id;

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


create or replace function ldf_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    if old.barcode_id is not null then
      update barcodes set type = 'P', parent_id = NULL where barcodes.id = old.barcode_id;
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
      update barcodes set type = 'P', parent_id = NULL where barcodes.id = old.barcode_id;
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


-- barcodes

alter table barcodes add column coc_status d_coc_status default 'P' not null;
alter table tdf_data drop column coc_status;
alter table ldf_data drop column coc_status;


-- status

create domain d_csv_status as character(1) check (value ~ E'^[PARDU]$');
create domain d_data_status as character(1) check (value ~ E'^[PARD]$');

alter table csv alter column status type d_csv_status;

alter table ssf_data add column status d_data_status default 'P' not null;
alter table tdf_data add column status d_data_status default 'P' not null;
alter table ldf_data add column status d_data_status default 'P' not null;
alter table mof_data add column status d_data_status default 'P' not null;
alter table mif_data add column status d_data_status default 'P' not null;
alter table specs_data add column status d_data_status default 'P' not null;
alter table epr_data add column status d_data_status default 'P' not null;

