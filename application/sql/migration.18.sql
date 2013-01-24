
-- better constraints

alter table specs_data add constraint specs_data_specs_barcode_id_fkey foreign key (specs_barcode_id) references barcodes (id) on update cascade;
alter table specs_data add constraint specs_data_epr_barcode_id_fkey foreign key (epr_barcode_id) references barcodes (id) on update cascade;


-- relaxed locked checks, now only on deletes

drop trigger t_check_barcode_locks on ssf_data;
drop trigger t_check_barcode_locks on tdf_data;
drop trigger t_check_barcode_locks on ldf_data;
drop trigger t_check_barcode_locks on mof_data;
drop trigger t_check_barcode_locks on mif_data;
drop trigger t_check_barcode_locks on specs_data;
drop trigger t_check_barcode_locks on epr_data;

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

