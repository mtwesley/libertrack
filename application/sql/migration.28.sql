

-- barcode activity comments

alter table barcode_activity add column comment d_text_long;

alter domain d_barcode_activity drop constraint d_barcode_activity_check;
alter domain d_barcode_activity add check (value ~ E'^[PIHTXDNESYALZC]$');


-- override status and comments

create table status_activity (
  id bigsearial not null,
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

