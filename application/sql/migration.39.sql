
-- SHSH type

alter domain d_document_type drop constraint d_document_type_check;
alter domain d_document_type add check (value ~ E'(SPECS|SHSH|EXP|EPT|CERT)');

-- SHSH sequence

create sequence s_documents_shsh_number minvalue 1;


-- fixing document numbering.. moving to database

create or replace function create_document_number()
  returns trigger as
$$
begin
  if new.is_draft = FALSE then
    case new.type
      when 'SPECS' then new.number = nextval('s_documents_specs_number');
      when 'SHSH' then new.number = nextval('s_documents_shsh_number');
      when 'EXP' then new.number = nextval('s_documents_exp_number');
      when 'CERT' then new.number = nextval('s_documents_cert_number');
    end case;
  end if;

  return new;
end
$$ language 'plpgsql';


create trigger t_documents_create_number
  after insert or update on documents
  for each row
  execute procedure create_document_number();

