
-- fix barcode hops

alter table barcode_hops_cached add constraint barcode_hops_cached_unique unique(barcode_id,parent_id);


-- fix triggers

create or replace function tdf_data_update_barcodes()
  returns trigger as
$$
  declare x_id d_id;
begin
  if (new.barcode_id = new.stump_barcode_id) or (new.barcode_id = new.tree_barcode_id) or (new.tree_barcode_id = new.stump_barcode_id) then
    return null;
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


create or replace function ldf_data_update_barcodes()
  returns trigger as
$$
begin
  if (new.barcode_id = new.parent_barcode_id) then
    return null;
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
