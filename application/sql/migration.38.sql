
-- original TDF informatoin

alter table tdf_verification add column original_species_id d_id;
alter table tdf_verification add column original_diameter d_diameter;
alter table tdf_verification add column original_length d_length;
alter table tdf_verification add column original_volume d_volume;

alter table tdf_verification disable trigger all;

update tdf_verification set 
  original_species_id = species_id,
  original_diameter = diameter,
  original_length = length,
  original_volume = volume;

alter table tdf_verification enable trigger all;

alter table tdf_verification alter column original_species_id set not null;
alter table tdf_verification alter column original_diameter set not null;
alter table tdf_verification alter column original_length set not null;
alter table tdf_verification alter column original_volume set not null;

-- original LDF information

alter table ldf_verification add column original_species_id d_id;
alter table ldf_verification add column original_diameter d_diameter;
alter table ldf_verification add column original_length d_length;

alter table ldf_verification disable trigger all;

update ldf_verification set 
  original_species_id = species_id,
  original_diameter = diameter,
  original_length = length;

alter table ldf_verification enable trigger all;

alter table ldf_verification alter column original_species_id set not null;
alter table ldf_verification alter column original_diameter set not null;
alter table ldf_verification alter column original_length set not null;


-- original SPECS information

alter table specs_data add column original_diameter d_diameter;

alter table specs_data disable trigger all;

update specs_data set original_diameter = diameter;

alter table specs_data enable trigger all;

alter table specs_data alter column original_diameter set not null;
