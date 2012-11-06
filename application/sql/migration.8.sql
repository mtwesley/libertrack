
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

