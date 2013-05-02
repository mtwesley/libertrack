

-- verification locks

create function verification_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'UPDATE') or (tg_op = 'DELETE') then
    delete from barcode_activity where id in (select id from barcode_activity where barcode_id = old.id and activity = 'N' and trigger = 'verification' limit 1);
    delete from barcode_locks where barcode_id = old.barcode_id and lock = 'VERIFY' and lock_id = old.id;
  end if;

  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    insert into barcode_activity (barcode_id,activity,trigger) values (new.id,'N','verification');
    insert into barcode_locks (barcode_id,lock,lock_id,user_id) values (new.barcode_id,'VERIFY',new.id,new.user_id);
  else  end if;

  return null;
end
$$ language 'plpgsql';


create trigger t_ssf_verification_update_barcodes
  after insert or update or delete on ssf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_tdf_verification_update_barcodes
  after insert or update or delete on ssf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_ldf_verification_update_barcodes
  after insert or update or delete on ssf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_mif_verification_update_barcodes
  after insert or update or delete on ssf_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_mof_verification_update_barcodes
  after insert or update or delete on mof_verification
  for each row
  execute procedure verification_update_barcodes();

create trigger t_specs_verification_update_barcodes
  after insert or update or delete on specs_verification
  for each row
  execute procedure verification_update_barcodes();




-- migrations

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
    case x_invoice.type
      when 'ST'  then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('T') and trigger = 'invoice_data' limit 1);
      when 'EXF' then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('X') and trigger = 'invoice_data' limit 1);
      else null;
    end case;
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
    case x_invoice.type
      when 'EXP' then delete from barcode_activity where id in (select id from barcode_activity where barcode_id = x_data.barcode_id and activity in ('E') and trigger = 'document_data' limit 1);
      else null;
    end case;
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

