
-- specs

alter table specs_data add column specs_barcode_id d_id not null;
alter table specs_data add column epr_barcode_id d_id not null;
alter table specs_data add column contract_numbuer d_text_short;
alter table specs_data add column origin d_text_short;
alter table specs_data add column destination d_text_short;
alter table specs_data add column create_date d_date not null;

alter table specs_data drop column expected_loading_date;

