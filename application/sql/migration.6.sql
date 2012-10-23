
-- permissions

insert into roles (name, description) values ('invoices', 'Invoice Management');
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('invoices'));

-- species fob price update

update species set fob_price = 170 where code = 'AFRO';
update species set fob_price = 260 where code = 'AFZ';
update species set fob_price = 170 where code = 'ALB';
update species set fob_price = 170 where code = 'ALS';
update species set fob_price = 170 where code = 'AMP';
update species set fob_price = 170 where code = 'ANH';
update species set fob_price = 170 where code = 'ANI';
update species set fob_price = 170 where code = 'ANO';
update species set fob_price = 170 where code = 'ANT';
update species set fob_price = 170 where code = 'ANTH';
update species set fob_price = 170 where code = 'ARA';
update species set fob_price = 170 where code = 'AUB';
update species set fob_price = 170 where code = 'BEI';
update species set fob_price = 170 where code = 'BER';
update species set fob_price = 190 where code = 'BOM';
update species set fob_price = 170 where code = 'BRA';
update species set fob_price = 170 where code = 'BRI';
update species set fob_price = 170 where code = 'BUS';
update species set fob_price = 170 where code = 'CAL';
update species set fob_price = 170 where code = 'CAN';
update species set fob_price = 170 where code = 'CEI';
update species set fob_price = 170 where code = 'CEL';
update species set fob_price = 170 where code = 'CHI';
update species set fob_price = 250 where code = 'CHL';
update species set fob_price = 170 where code = 'CHR';
update species set fob_price = 170 where code = 'COM';
update species set fob_price = 170 where code = 'COP';
update species set fob_price = 170 where code = 'COU';
update species set fob_price = 170 where code = 'CRY';
update species set fob_price = 150 where code = 'CYN';
update species set fob_price = 170 where code = 'DAC';
update species set fob_price = 180 where code = 'DAN';
update species set fob_price = 170 where code = 'DIA';
update species set fob_price = 170 where code = 'DID';
update species set fob_price = 170 where code = 'DIO';
update species set fob_price = 170 where code = 'DIS';
update species set fob_price = 230 where code = 'ENTA';
update species set fob_price = 170 where code = 'ENTC';
update species set fob_price = 170 where code = 'ENTCY';
update species set fob_price = 170 where code = 'ENTU';
update species set fob_price = 270 where code = 'ERY';
update species set fob_price = 170 where code = 'ERYM';
update species set fob_price = 170 where code = 'FAG';
update species set fob_price = 170 where code = 'FUN';
update species set fob_price = 170 where code = 'GIL';
update species set fob_price = 170 where code = 'GLU';
update species set fob_price = 170 where code = 'GUA';
update species set fob_price = 170 where code = 'GUI';
update species set fob_price = 170 where code = 'HAL';
update species set fob_price = 170 where code = 'HAN';
update species set fob_price = 170 where code = 'HAP';
update species set fob_price = 170 where code = 'IRV';
update species set fob_price = 170 where code = 'KHA';
update species set fob_price = 170 where code = 'KHI';
update species set fob_price = 170 where code = 'KLA';
update species set fob_price = 250 where code = 'LOP';
update species set fob_price = 180 where code = 'LOV';
update species set fob_price = 170 where code = 'MAM';
update species set fob_price = 170 where code = 'MAN';
update species set fob_price = 190 where code = 'MANI';
update species set fob_price = 170 where code = 'MON';
update species set fob_price = 170 where code = 'MUS';
update species set fob_price = 170 where code = 'NAU';
update species set fob_price = 170 where code = 'NES';
update species set fob_price = 170 where code = 'NEW';
update species set fob_price = 170 where code = 'OLD';
update species set fob_price = 170 where code = 'ONG';
update species set fob_price = 170 where code = 'PAC';
update species set fob_price = 170 where code = 'PAK';
update species set fob_price = 170 where code = 'PAR';
update species set fob_price = 170 where code = 'PEN';
update species set fob_price = 170 where code = 'PENT';
update species set fob_price = 170 where code = 'PER';
update species set fob_price = 190 where code = 'PIP';
update species set fob_price = 170 where code = 'PTE';
update species set fob_price = 170 where code = 'PYC';
update species set fob_price = 170 where code = 'RHO';
update species set fob_price = 170 where code = 'RIC';
update species set fob_price = 170 where code = 'SAB';
update species set fob_price = 170 where code = 'SAC';
update species set fob_price = 170 where code = 'SAM';
update species set fob_price = 170 where code = 'SAN';
update species set fob_price = 170 where code = 'STR';
update species set fob_price = 170 where code = 'SYM';
update species set fob_price = 170 where code = 'SYN';
update species set fob_price = 280 where code = 'TAR';
update species set fob_price = 203 where code = 'TEI';
update species set fob_price = 170 where code = 'TES';
update species set fob_price = 190 where code = 'TET';
update species set fob_price = 170 where code = 'TIE';
update species set fob_price = 170 where code = 'TRI';
update species set fob_price = 170 where code = 'TUR';
update species set fob_price = 170 where code = 'UAP';
update species set fob_price = 170 where code = 'UNK';
update species set fob_price = 170 where code = 'XYL';
update species set fob_price = 170 where code = 'XYLO';


-- fix barcode hops

create or replace function barcodes_hops()
  returns trigger as
$$
begin
  if tg_op = 'INSERT' then
    perform rebuild_barcode_hops(new.id);
  elseif tg_op = 'UPDATE' then
    delete from barcode_hops_cached where barcode_id = old.id;
    perform rebuild_barcode_hops(new.id);
  elseif tg_op = 'DELETE' then
    delete from barcode_hops_cached where barcode_id = old.id;
  end if;

  return null;
end
$$ language 'plpgsql';


-- fix csv_duplicates

create temporary table t_tmp on commit drop as
select distinct on (csv_id,duplicate_csv_id,field) * from csv_duplicates;
truncate csv_duplicates;
insert into csv_duplicates
select * from t_tmp;

alter table csv_duplicates add constraint csv_duplicates_unique unique(csv_id,duplicate_csv_id,field);


