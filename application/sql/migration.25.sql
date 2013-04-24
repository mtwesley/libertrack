
-- upgrades

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|EPR|CHECKS|EXP|PJ|INV|DOC|UNKWN)$');

alter sequence s_documents_specs_number restart with 000500;
alter sequence s_documents_exp_number restart with 000500;


-- barcode locks upgrade

create domain d_barcode_lock as character varying(6) check (value ~ E'(ADMIN|INV|DOC|BRCODE)');

create table barcode_locks (
  id bigserial not null,
  barcode_id d_id not null,
  lock d_barcode_lock not null,
  lock_id d_id not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  -- constraint barcode_locks_pkey primary key (id),
  constraint barcode_locks_barcode_id foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade,

  constraint barcode_locks_unique unique(barcode_id,lock,lock_id)
);

create or replace function specs_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.specs_barcode_id) or (new.barcode_id = new.exp_barcode_id) or (new.exp_barcode_id = new.specs_barcode_id) then
      return null;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where barcodes.id = new.barcode_id;
    end if;

    if new.specs_barcode_id is not null then
      update barcodes set type = 'H' where barcodes.id = new.specs_barcode_id;
    end if;

    if new.exp_barcode_id is not null then
      update barcodes set type = 'E' where barcodes.id = new.exp_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function invoice_data_update_barcodes()
  returns trigger as
$$
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
    when 'SSF'   then select barcode_id,user_id from ssf_data where id = x_form_data_id into x_record;
    when 'TDF'   then select barcode_id,user_id from tdf_data where id = x_form_data_id into x_record;
    when 'LDF'   then select barcode_id,user_id from ldf_data where id = x_form_data_id into x_record;
    when 'MIF'   then select barcode_id,user_id from mif_data where id = x_form_data_id into x_record;
    when 'MOF'   then select barcode_id,user_id from mof_data where id = x_form_data_id into x_record;
    when 'SPECS' then select barcode_id,user_id from specs_data where id = x_form_data_id into x_record;
    else return null;
  end case;

  if (tg_op = 'DELETE') then
    delete from barcode_locks where barcode_id = x_record.barcode_id and lock = 'INV' and lock_id = old.invoice_id;
  else
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_record.barcode_id,'INV',new.invoice_id,x_record.user_id);
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function document_data_update_barcodes()
  returns trigger as
$$
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
    when 'SSF'   then select barcode_id,user_id from ssf_data where id = x_form_data_id into x_record;
    when 'TDF'   then select barcode_id,user_id from tdf_data where id = x_form_data_id into x_record;
    when 'LDF'   then select barcode_id,user_id from ldf_data where id = x_form_data_id into x_record;
    when 'MIF'   then select barcode_id,user_id from mif_data where id = x_form_data_id into x_record;
    when 'MOF'   then select barcode_id,user_id from mof_data where id = x_form_data_id into x_record;
    when 'SPECS' then select barcode_id,user_id from specs_data where id = x_form_data_id into x_record;
    else return null;
  end case;

  if (tg_op = 'DELETE') then
    delete from barcode_locks where barcode_id = x_record.barcode_id and lock = 'INV' and lock_id = old.document_id;
  else
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_record.barcode_id,'INV',new.document_id,x_record.user_id);
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function barcode_locks_update_locks()
  returns trigger as
$$
  declare x_id d_id;
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    delete from barcode_locks where lock = 'BRCODE' and lock_id = new.barcode_id;
    for x_id in select barcode_id from barcode_hops_cached where parent_id = new.barcode_id loop
      insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_id,'BRCODE',new.barcode_id,new.user_id);
    end loop;

  elseif (tg_op = 'DELETE') then
    delete from barcode_locks where lock = 'BRCODE' and lock_id = old.barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';


create trigger t_document_data_update_barcodes
  after insert or update or delete on document_data
  for each row
  execute procedure document_data_update_barcodes();

create trigger t_barcode_locks_update_locks
  after insert or update or delete on barcode_locks
  for each row
  execute procedure barcode_locks_update_locks();


insert into barcode_locks (barcode_id,lock,lock_id)
select id, 'BRCODE', id from barcodes where is_locked = true;

-- alter table barcodes drop column is_locked;
-- drop function barcodes_locks();
-- drop function rebuild_barcode_locks(d_id, d_bool);


-- activity

alter table barcode_coc_activity rename to barcode_activity;

alter table barcode_activity alter column trigger set default 'system';
alter table barcode_activity rename column status to activity;

alter table barcode_activity drop constraint barcode_coc_activity_barcode_id_fkey;
alter table barcode_activity drop constraint barcode_coc_activity_unique;

alter table barcode_activity add constraint barcode_activity_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade on delete cascade;
-- alter table barcode_activity add constraint barcode_activity_unique unique(barcode_id,activity);

alter index barcode_coc_activity_barcode_id_status rename to barcode_activity_barcode_id_activity;
alter index barcode_coc_activity_barcode_id_trigger rename to barcode_activity_barcode_id_trigger;
alter index barcode_coc_activity_status_trigger rename to barcode_activity_activity_trigger;

create or replace function mif_data_update_barcodes()
  returns trigger as
$$
begin
  return null;
end
$$ language 'plpgsql';

create or replace function barcodes_update_barcodes()
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

create trigger t_mif_data_update_barcodes
  after insert or update or delete on mif_data
  for each row
  execute procedure mif_data_update_barcodes();

create trigger t_barcodes_update_barcodes
  after insert or update or delete on barcodes
  for each row
  execute procedure barcodes_update_barcodes();

create or replace function specs_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op <> 'DELETE') then
    if (new.barcode_id = new.specs_barcode_id) or (new.barcode_id = new.exp_barcode_id) or (new.exp_barcode_id = new.specs_barcode_id) then
      return null;
    end if;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.barcode_id is not null then
      update barcodes set type = 'L' where barcodes.id = new.barcode_id;
      insert into barcode_activity (barcode_id,activity,trigger) values (new.barcode_id,'D','specs_data');
    end if;

    if new.specs_barcode_id is not null then
      update barcodes set type = 'H' where barcodes.id = new.specs_barcode_id;
    end if;

    if new.exp_barcode_id is not null then
      update barcodes set type = 'E' where barcodes.id = new.exp_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';

create or replace function invoice_data_update_barcodes()
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
    else return null;
  end case;

  if (tg_op = 'DELETE') then
    delete from barcode_locks where barcode_id = x_data.barcode_id and lock = 'INV' and lock_id = old.invoice_id;
  else
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_data.barcode_id,'INV',new.invoice_id,x_data.user_id);

    select type,number,is_draft from invoices where id = new.invoice_id into x_invoice;
    if (x_invoice.is_draft = false) then
      case x_invoice.type
        when 'ST'  then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'T','invoice_data');
        when 'EXF' then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'X','invoice_data');
      end case;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';

create or replace function document_data_update_barcodes()
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
    else return null;
  end case;

  if (tg_op = 'DELETE') then
    delete from barcode_locks where barcode_id = x_data.barcode_id and lock = 'DOC' and lock_id = old.document_id;
  else
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_data.barcode_id,'DOC',new.document_id,x_data.user_id);

    select type,number,is_draft from documents where id = new.document_id into x_document;
    if (x_document.is_draft = false) then
      case x_document.type
        when 'EXP' then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'E','document_data');
      end case;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


-- short shipment

alter table specs_data drop constraint specs_data_barcode_id_key;
alter table specs_data add constraint specs_data_unique_barcode unique(barcode_id,specs_barcode_id);


-- more fixes

create or replace function invoices_update_data()
  returns trigger as
$$
begin
  update invoice_data set form_data_id = form_data_id where invoice_id = new.id;
  return null;
end
$$ language 'plpgsql';


create or replace function invoice_data_update_barcodes()
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

  if (tg_op = 'DELETE') then
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


create or replace function documents_update_data()
  returns trigger as
$$
begin
  update document_data set form_data_id = form_data_id where document_id = new.id;
  return null;
end
$$ language 'plpgsql';


create or replace function document_data_update_barcodes()
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

  if (tg_op = 'DELETE') then
    delete from barcode_locks where barcode_id = x_data.barcode_id and lock = 'DOC' and lock_id = old.document_id;
  else
    if (tg_op = 'INSERT') then
      insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_data.barcode_id,'DOC',new.document_id,x_data.user_id);
    end if;

    select type,number,is_draft from documents where id = new.document_id into x_document;
    if (x_document.is_draft = false) then
      case x_document.type
        when 'EXP' then insert into barcode_activity (barcode_id,activity,trigger) values (x_data.barcode_id,'E','document_data');
        else null;
      end case;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';


create trigger t_invoices_update_data
  after update on invoices
  for each row
  execute procedure invoices_update_data();

create trigger t_documents_update_data
  after update on documents
  for each row
  execute procedure documents_update_data();


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


-- remove csv id
