

-- barcode activity comments

alter table barcode_activity add column comment d_text_long;

alter domain d_barcode_activity drop constraint d_barcode_activity_check;
alter domain d_barcode_activity add check (value ~ E'^[PIHTXDNESYALZC]$');


-- override status and comments

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


-- barcode hops

alter table barcode_hops_cached rename to barcode_hops;

create or replace function barcodes_hops()
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


create or replace function rebuild_barcode_hops(x_barcode_id d_id)
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


create or replace function barcode_locks_update_locks()
  returns trigger as
$$
  declare x_id d_id;
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    delete from barcode_locks where lock = 'BRCODE' and lock_id = new.barcode_id;
    for x_id in select barcode_id from barcode_hops where parent_id = new.barcode_id loop
      insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (x_id,'BRCODE',new.barcode_id,new.user_id);
    end loop;

  elseif (tg_op = 'DELETE') then
    delete from barcode_locks where lock = 'BRCODE' and lock_id = old.barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';


-- invoice payments

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


-- verification operation type

alter domain d_operation_type drop constraint d_operation_type_check;
alter domain d_operation_type add check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|SSFV|TDFV|LDFV|MIFV|MOFV|SPECSV|CHECKS|VERIFY|EXP|INV|DOC|PJ|UNKWN)$');


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
  owner_operator_id d_id not null,
  transport_operator_id d_id not null,
  wb_barcode_id d_id not null,
  barcode_id d_id not null,
  species_id d_id not null,
  diameter d_diameter not null,
  length d_length not null,
  original_volume d_volume not null,
  volume d_volume not null,
  origin d_text_short not null,
  origin_date d_date not null,
  destination d_text_short not null,
  destination_date d_date not null,
  supervised_by d_text_short,
  truck_number d_text_short,
  entered_by d_text_short,
  create_date d_date not null,
  status d_data_status default 'P' not null,
  user_id d_id default 1 not null,
  timestamp d_timestamp default current_timestamp not null,

  constraint wb_data_pkey primary key (id),
  constraint wb_data_owner_operator_id_fkey foreign key (owner_operator_id) references operators (id) on update cascade,
  constraint wb_data_transport_operator_id_fkey foreign key (transport_operator_id) references operators (id) on update cascade,
  constraint wb_data_barcode_id_fkey foreign key (barcode_id) references barcodes (id) on update cascade,
  constraint wb_data_wb_barcode_id_fkey foreign key (wb_barcode_id) references barcodes (id) on update cascade,
  constraint wb_data_species_id_fkey foreign key (species_id) references species (id) on update cascade,
  constraint wb_data_user_id_fkey foreign key (user_id) references users (id) on update cascade,

  constraint wb_data_unique_barcode unique(barcode_id,wb_barcode_id)
);


