
-- get printjobs less than 1000

select printjob, barcodes
from (
	select distinct count(printjob_id) barcodes, number printjob
	from barcodes
	join printjobs on printjob_id = printjobs.id
	group by printjob_id,number
) as result
where barcodes <> 1000;

-- drop site requirement from print jobs

alter table printjobs alter column site_id drop not null;

-- specs barcode triggers

create function specs_data_update_barcodes()
  returns trigger as
$$
begin
  if (tg_op = 'INSERT') or (tg_op = 'UPDATE') then
    if new.specs_barcode_id is not null then
      update barcodes set type = 'H' where barcodes.id = new.specs_barcode_id;
    end if;
    if new.epr_barcode_id is not null then
      update barcodes set type = 'E' where barcodes.id = new.epr_barcode_id;
    end if;
  end if;

  return null;
end
$$ language 'plpgsql';

create trigger t_specs_data_update_barcodes
  after insert or update or delete on specs_data
  for each row
  execute procedure specs_data_update_barcodes();


