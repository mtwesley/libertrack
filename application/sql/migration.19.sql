
alter table specs alter column number drop not null;
alter table epr alter column number drop not null;

-- better specs handling

create index specs_data_specs_barcode_id on specs_data (id,specs_barcode_id);
create index specs_data_epr_barcode_id on specs_data (id,epr_barcode_id);
create index specs_data_specs_id on specs_data (id,specs_id);
create index specs_data_epr_id on specs_data (id,epr_id);

create or replace function specs_data_update_barcodes()
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

    update barcodes set is_locked = false where id = new.barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';


-- some early epr stuff

create function epr_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    update barcodes set is_locked = false where id = new.barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';



