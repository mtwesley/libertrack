

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
    select type,number,is_draft from invoices where id = old.invoice_id into x_invoice;
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
    select type,number,is_draft from documents where id = old.document_id into x_document;
    case x_document.type
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


-- utm coordinates

create domain d_utm as character varying(21) check (value ~ E'[0-9]{1,2} [0-9]{6}mE [0-9]{1,8}mN');

alter table ssf_data add column utm_origin d_utm;
alter table ssf_data add column utm_east d_utm;
alter table ssf_data add column utm_north_south d_utm;
alter table ssf_data add column utm_west d_utm;

alter table ssf_verification add column utm_origin d_utm;
alter table ssf_verification add column utm_east d_utm;
alter table ssf_verification add column utm_north_south d_utm;
alter table ssf_verification add column utm_west d_utm;


-- invoice number

alter table invoices add column invnumber d_text_short;

alter table invoices add column is_paid d_bool default true not null;
alter table invoices alter column is_paid set default false;


-- min diameter

alter table species add column min_diameter d_diameter;

update species set min_diameter = 60 where code = 'AFRO';
update species set min_diameter = 70 where code = 'AFZ';
update species set min_diameter = 60 where code = 'ALB';
update species set min_diameter = 70 where code = 'ALS';
update species set min_diameter = 60 where code = 'AMP';
update species set min_diameter = 60 where code = 'ANH';
update species set min_diameter = 80 where code = 'ANI';
update species set min_diameter = 60 where code = 'ANO';
update species set min_diameter = 60 where code = 'ANT';
update species set min_diameter = 60 where code = 'ANTH';
update species set min_diameter = 60 where code = 'ARA';
update species set min_diameter = 60 where code = 'AUB';
update species set min_diameter = 60 where code = 'BEI';
update species set min_diameter = 60 where code = 'BER';
update species set min_diameter = 70 where code = 'BOM';
update species set min_diameter = 90 where code = 'BRA';
update species set min_diameter = 60 where code = 'BRI';
update species set min_diameter = 60 where code = 'BUS';
update species set min_diameter = 60 where code = 'CAL';
update species set min_diameter = 80 where code = 'CAN';
update species set min_diameter = 90 where code = 'CEI';
update species set min_diameter = 60 where code = 'CEL';
update species set min_diameter = 60 where code = 'CHI';
update species set min_diameter = 80 where code = 'CHL';
update species set min_diameter = 60 where code = 'CHR';
update species set min_diameter = 60 where code = 'COM';
update species set min_diameter = 60 where code = 'COP';
update species set min_diameter = 60 where code = 'COU';
update species set min_diameter = 60 where code = 'CRY';
update species set min_diameter = 60 where code = 'CYN';
update species set min_diameter = 60 where code = 'DAC';
update species set min_diameter = 70 where code = 'DAN';
update species set min_diameter = 60 where code = 'DIA';
update species set min_diameter = 60 where code = 'DID';
update species set min_diameter = 60 where code = 'DIO';
update species set min_diameter = 80 where code = 'DIS';
update species set min_diameter = 90 where code = 'ENTA';
update species set min_diameter = 90 where code = 'ENTC';
update species set min_diameter = 90 where code = 'ENTCY';
update species set min_diameter = 100 where code = 'ENTU';
update species set min_diameter = 80 where code = 'ERY';
update species set min_diameter = 60 where code = 'ERYM';
update species set min_diameter = 60 where code = 'FAG';
update species set min_diameter = 60 where code = 'FUN';
update species set min_diameter = 60 where code = 'GIL';
update species set min_diameter = 60 where code = 'GLU';
update species set min_diameter = 80 where code = 'GUA';
update species set min_diameter = 60 where code = 'GUI';
update species set min_diameter = 80 where code = 'HAL';
update species set min_diameter = 60 where code = 'HAN';
update species set min_diameter = 60 where code = 'HAP';
update species set min_diameter = 60 where code = 'IRV';
update species set min_diameter = 70 where code = 'KHA';
update species set min_diameter = 70 where code = 'KHI';
update species set min_diameter = 60 where code = 'KLA';
update species set min_diameter = 80 where code = 'LOP';
update species set min_diameter = 70 where code = 'LOV';
update species set min_diameter = 60 where code = 'MAM';
update species set min_diameter = 60 where code = 'MAN';
update species set min_diameter = 60 where code = 'MANI';
update species set min_diameter = 60 where code = 'MON';
update species set min_diameter = 60 where code = 'MUS';
update species set min_diameter = 80 where code = 'NAU';
update species set min_diameter = 60 where code = 'NES';
update species set min_diameter = 60 where code = 'NEW';
update species set min_diameter = 60 where code = 'OLD';
update species set min_diameter = 60 where code = 'ONG';
update species set min_diameter = 60 where code = 'PAC';
update species set min_diameter = 60 where code = 'PAK';
update species set min_diameter = 60 where code = 'PAR';
update species set min_diameter = 60 where code = 'PEN';
update species set min_diameter = 60 where code = 'PENT';
update species set min_diameter = 60 where code = 'PER';
update species set min_diameter = 80 where code = 'PIP';
update species set min_diameter = 60 where code = 'PTE';
update species set min_diameter = 70 where code = 'PYC';
update species set min_diameter = 60 where code = 'RHO';
update species set min_diameter = 60 where code = 'RIC';
update species set min_diameter = 60 where code = 'SAB';
update species set min_diameter = 70 where code = 'SAC';
update species set min_diameter = 60 where code = 'SAM';
update species set min_diameter = 60 where code = 'SAN';
update species set min_diameter = 60 where code = 'STR';
update species set min_diameter = 60 where code = 'SYM';
update species set min_diameter = 60 where code = 'SYN';
update species set min_diameter = 60 where code = 'TAR';
update species set min_diameter = 70 where code = 'TEI';
update species set min_diameter = 70 where code = 'TES';
update species set min_diameter = 60 where code = 'TET';
update species set min_diameter = 100 where code = 'TIE';
update species set min_diameter = 90 where code = 'TRI';
update species set min_diameter = 80 where code = 'TUR';
update species set min_diameter = 60 where code = 'UAP';
update species set min_diameter = 60 where code = 'UNK';
update species set min_diameter = 60 where code = 'XYL';
update species set min_diameter = 60 where code = 'XYLO';


-- utm again

alter domain d_utm drop constraint d_utm_check;
alter domain d_utm add check (value ~ E'^[0-9]{1,2} [0-9]{6}E [0-9]{1,8}N$');

alter table blocks add column utm_origin d_utm;
alter table blocks add column utm_east d_utm;
alter table blocks add column utm_north_south d_utm;
alter table blocks add column utm_west d_utm;

