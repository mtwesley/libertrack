
-- ssf volume

alter table ssf_data add column volume d_volume;
alter table ssf_verification add column volume d_volume;
create index ssf_data_volume on ssf_data (id,volume);
create index ssf_verification_volume on ssf_verification (id,volume);

alter table ssf_data disable trigger all;
alter table ssf_verification disable trigger all;

update ssf_data set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);
update ssf_verification set volume = (pi() * power(((diameter::numeric / 2) / 100), 2) * length);

alter table ssf_data alter column volume set not null;
alter table ssf_verification alter column volume set not null;

alter table ssf_data enable trigger all;
alter table ssf_verification enable trigger all;


-- order

alter table reports add column "order" d_text_long;
