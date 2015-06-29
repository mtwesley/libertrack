
-- original TDF informatoin

alter table tdf_data add column original_species_id d_id;
alter table tdf_data add column original_diameter d_diameter;
alter table tdf_data add column original_length d_length;
alter table tdf_data add column original_volume d_volume;

alter table tdf_data disable trigger all;

update tdf_data set 
  original_species_id = species_id,
  original_diameter = diameter,
  original_length = length,
  original_volume = volume;

alter table tdf_data enable trigger all;

alter table tdf_data alter column original_species_id set not null;
alter table tdf_data alter column original_diameter set not null;
alter table tdf_data alter column original_length set not null;
alter table tdf_data alter column original_volume set not null;

