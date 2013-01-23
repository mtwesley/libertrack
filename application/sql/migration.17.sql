
-- shipping numbers

create domain d_specs_number as character(6) check (value ~ E'[0-9]{6}');
create domain d_epr_number as character(6) check (value ~ E'[0-9]{6}');

alter table specs_data add column specs_number d_specs_number;
alter table specs_data add column epr_number d_epr_number;

alter table specs_data add constraint specs_data_specs_number_or_barcode check (specs_number is not null or specs_barcode_id is not null);
alter table specs_data add constraint specs_data_epr_number_or_barcode check (epr_number is not null or epr_barcode_id is not null);

alter table specs_data alter column specs_barcode_id drop not null;
alter table specs_data alter column epr_barcode_id drop not null;


