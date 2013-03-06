
-- fixes for error checks

alter domain d_error_type drop constraint d_error_type_check;
alter domain d_error_type add check (value ~ E'^[EWS]$');

-- changes to tolerances

alter table tolerances drop column form_fields;
alter table tolerances rename column type to "check";

alter table tolerances drop constraint tolerances_unique;
alter table tolerances add constraint tolerances_unique unique(form_type,"check");

-- clear out tolerances

delete from tolerances;

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_survey_line',2,20);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_length',2,10);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_volume',0.2,2);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_volume',0.2,2);

-- extra

alter table files alter column operation_type type character varying(6);
drop domain d_operation_type;
create domain d_operation_type as character varying(6) check (value ~ E'^(SSF|TDF|LDF|MIF|MOF|SPECS|CHECKS|EPR|PJ|INV|UNKWN)$');

-- export permit

create sequence s_epr_number minvalue 1;

create domain d_exp_number as character(6) check (value ~ E'[0-9]{6,10}');

alter table epr rename to exp;
alter table epr alter column number type exp_number;

drop domain d_epr_number;

alter table specs_data alter column epr_barcode_id rename to exp_barcode_id;
alter table specs_data alter column epr_id rename to exp_id;

create or replace function specs_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.specs_barcode_id is not null then
      update barcodes set type = 'H' where barcodes.id = new.specs_barcode_id;
    end if;
    if new.exp_barcode_id is not null then
      update barcodes set type = 'E' where barcodes.id = new.exp_barcode_id;
    end if;

    update barcodes set is_locked = false where id = new.barcode_id;
  end if;

  return null;
end
$$ language 'plpgsql';


create or replace function check_barcode_locks()
  returns trigger as
$$
  declare x_is_locked d_bool;
begin
  select is_locked from barcodes where id = old.barcode_id into x_is_locked;

  if x_is_locked = true then
    -- raise exception 'Sorry, cannot delete data due to barcode locks.';
    return null;

  elseif (tg_op = 'UPDATE') then
    return new;

  elseif (tg_op = 'DELETE') then
    return old;

  end if;

end
$$ language 'plpgsql';


--

alter table specs_data drop constraint specs_data_epr_check;
alter table specs_data drop constraint epr_data_epr_id_fkey;
alter table specs_data drop constraint specs_data_epr_barcode_id_fkey;

drop index epr_data_barcode_id;
drop index epr_data_barcode_type;
drop index epr_data_operator_id;
drop index epr_data_request_number;
drop index epr_data_status;
drop index specs_data_epr_barcode_id;
drop index specs_data_epr_id;

drop sequence epr_id_seq;

alter table "epr" drop constraint epr_pkey;
alter table "epr" drop constraint epr_final_check;
alter table "epr" drop constraint epr_file_id_key;

alter table "exp" add constraint exp_pkey primary key (id);
alter table "exp" add constraint exp_final_check check ((not ((((is_draft)::boolean = false) and (number is not null)) and (((is_draft)::boolean <> false) and (number is null)))));
alter table "exp" add constraint exp_file_id_key unique (file_id);

alter table specs_data add constraint specs_data_exp_check check (((exp_id is not null) or (exp_barcode_id is not null)));
alter table specs_data add constraint specs_data_exp_barcode_id_fkey foreign key (exp_barcode_id) references barcodes(id) on update cascade;
alter table specs_data add constraint specs_data_exp_id_fkey foreign key (exp_id) references exp(id) on update cascade;

create index specs_data_exp_barcode_id on specs_data using btree (id, exp_barcode_id);
create index specs_data_exp_id on specs_data using btree (id, exp_id);


-- other stuff

drop sequence tolerances_id_seq;
alter table invoices add constraint invoices_number_unique unique (type, number);

alter table tdf_data add constraint tdf_data_barcode_id_key unique (barcode_id);
alter table tdf_data add constraint tdf_data_stump_barcode_id_key unique (stump_barcode_id);
alter table tdf_data add constraint tdf_data_barcode_id_fkey foreign key (barcode_id) references barcodes(id) on update cascade;
alter table tdf_data add constraint tdf_data_stump_barcode_id_fkey foreign key (stump_barcode_id) references barcodes(id) on update cascade;

create index barcode_coc_activity_barcode_id_trigger on barcode_coc_activity using btree (barcode_id, trigger);

create trigger t_barcodes_locks
	after insert or update on barcodes
	for each row
	execute procedure barcodes_locks();
