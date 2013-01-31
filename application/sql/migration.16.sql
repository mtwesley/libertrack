
-- drop old stuff

drop trigger t_barcodes_locks on ldf_data;
drop function barcodes_locks();
drop function rebuild_barcode_locks(x_barcode_id d_id, x_locked d_bool);


-- barcode locks functions

create or replace function check_barcode_locks()
  returns trigger as
$$
  declare x_is_locked d_bool;
begin
  select is_locked from barcodes where id = old.barcode_id into x_is_locked;

  if x_is_locked = true then
    raise exception 'Preventing action due to barcode locks';
    return null;

  elseif (tg_op = 'UPDATE') then
    return new;

  elseif (tg_op = 'DELETE') then
    return old;

  end if;

end
$$ language 'plpgsql';


create or replace function barcodes_hops()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') then
    perform rebuild_barcode_hops(new.id);
  elseif (tg_op = 'UPDATE') and (old.parent_id <> new.parent_id) then
    delete from barcode_hops_cached where barcode_id = old.id;
    perform rebuild_barcode_hops(new.id);
  elseif (tg_op = 'DELETE') then
    delete from barcode_hops_cached where barcode_id = old.id;
  end if;

  return null;
end
$$ language 'plpgsql';

create or replace function barcodes_locks()
  returns trigger as
$$
begin
  if (old.is_locked <> new.is_locked) and (new.is_locked = true) then
    perform rebuild_barcode_locks(new.id, true);
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function rebuild_barcode_locks(x_barcode_id d_id, x_lock d_bool)
  returns void as
$$
  declare x_id d_id;
  declare x_is_locked d_bool;
begin
  if (x_barcode_id is null) and (x_lock is null) then
    for x_id in select id from barcodes loop
      perform rebuild_barcode_locks(x_id, true);
    end loop;

  else
    for x_id in select barcode_id from barcode_hops_cached where parent_id = x_barcode_id loop
      update barcodes set is_locked = x_lock where id = x_id;
    end loop;
  end if;
end
$$ language 'plpgsql';


create or replace function invoice_data_update_barcodes()
  returns trigger as
$$
  declare x_barcode_id d_id;
  declare x_record record;
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
    when 'SSF'   then select barcode_id from ssf_data where id = x_form_data_id into x_barcode_id;
    when 'TDF'   then select barcode_id from tdf_data where id = x_form_data_id into x_barcode_id;
    when 'LDF'   then select barcode_id from ldf_data where id = x_form_data_id into x_barcode_id;
    when 'MIF'   then select barcode_id from mif_data where id = x_form_data_id into x_barcode_id;
    when 'MOF'   then select barcode_id from mof_data where id = x_form_data_id into x_barcode_id;
    when 'SPECS' then select barcode_id from specs_data where id = x_form_data_id into x_barcode_id;
    when 'EPR'   then select barcode_id from epr_data where id = x_form_data_id into x_barcode_id;
    else return null;
  end case;

  if (tg_op = 'DELETE') then
    update barcodes set is_locked = false where id = x_barcode_id;
  else
    update barcodes set is_locked = true where id = x_barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';


-- barcode locks triggers

create trigger t_barcodes_locks
  after insert or update on barcodes
  for each row
  execute procedure barcodes_locks();

create trigger t_check_barcode_locks
  before delete on ssf_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before delete on tdf_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before delete on ldf_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before delete on mof_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before delete on mif_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before delete on specs_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_check_barcode_locks
  before delete on epr_data
  for each row
  execute procedure check_barcode_locks();

create trigger t_invoice_data_update_barcodes
  after insert or update or delete on invoice_data
  for each row
  execute procedure invoice_data_update_barcodes();


-- barcode hops cached simplifier

create or replace function rebuild_barcode_hops(x_barcode_id d_id)
  returns void as
$$
  declare x_id d_id;
  declare x_hops d_positive_int;
begin
  if x_barcode_id is null then
    truncate barcode_hops_cached;

    for x_id in select id from barcodes where parent_id is not null loop
      perform rebuild_barcode_hops(x_id);
    end loop;
  else
    delete from barcode_hops_cached where barcode_id = x_barcode_id;
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