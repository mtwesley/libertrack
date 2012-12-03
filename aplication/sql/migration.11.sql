
-- fix issues with earlier fixes


create or replace function tdf_data_update_barcodes()
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


create or replace function ldf_data_update_barcodes()
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


-- constraints

alter table invoices drop constraint invoices_site_id_fkey;
alter table invoices add constraint invoices_site_id_fkey foreign key (site_id) references sites (id) on update cascade on delete cascade;

alter table invoice_data drop constraint invoice_data_invoice_id;
alter table invoice_data add constraint invoice_data_invoice_id foreign key (invoice_id) references invoices (id) on update cascade on delete cascade;

alter table csv_errors drop constraint csv_errors_csv_id_fkey;
alter table csv_errors add constraint csv_errors_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade;

alter table csv_duplicates drop constraint csv_duplicates_csv_id_fkey;
alter table csv_duplicates add constraint csv_duplicates_csv_id_fkey foreign key (csv_id) references csv (id) on update cascade on delete cascade on delete cascade;

