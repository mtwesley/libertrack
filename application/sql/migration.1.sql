
-- triggers

drop trigger t_site_type_and_reference on sites cascade;
drop function site_type_and_reference();

create function sites_parse_type()
  returns trigger as
$$
  declare x_site text[];
begin
  if new.name is not null then
    select regexp_matches(new.name::text, E'^([A-Z]{3})([\\s_-]*[A-Z1-9]{1,10})?$') into x_site;
    new.type = x_site[1];
  end if;

  return new;
end
$$ language 'plpgsql';


create trigger t_sites_parse_type
  before insert or update on sites
  for each row
  execute procedure sites_parse_type();


-- domains

create domain d_site_name as character varying(15);

-- columns

alter table sites alter column name type d_site_name;
alter table sites drop column reference cascade;

-- remove domains

drop domain d_site_reference;

-- values

update sites set "name" = substring("name" from E'^[A-Z]+\/([A-Z1-9\\s_-]+)$');

-- add check

alter domain d_site_name add check (value ~ E'^[A-Z]{3}[\\s_-]*[A-Z1-9]{1,10}$');
