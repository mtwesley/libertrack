
-- SHSH type

alter domain d_document_type drop constraint d_document_type_check;
alter domain d_document_type add check (value ~ E'(SPECS|SHSH|EXP|EPT|CERT)');

-- SHSH sequence

create sequence s_documents_shsh_number minvalue 1;


-- fixing document numbering.. moving to database

create or replace function create_document_number()
  returns trigger as
$$
  declare x_number d_document_number;
begin
  if new.is_draft = false and new.number is null then
    case new.type
      when 'SPECS' then select nextval('s_documents_specs_number') into x_number;
      when 'SHSH' then select nextval('s_documents_shsh_number') into x_number;
      when 'EXP' then select nextval('s_documents_exp_number') into x_number;
      when 'CERT' then select nextval('s_documents_cert_number') into x_number;
    end case;
    new.number = x_number;
  end if;

  return new;
end
$$ language 'plpgsql';


create trigger t_documents_create_number
  before insert or update on documents
  for each row
  execute procedure create_document_number();

