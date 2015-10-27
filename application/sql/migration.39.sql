
-- SHSH type

alter domain d_document_type drop constraint d_document_type_check;
alter domain d_document_type add check (value ~ E'(SPECS|SHSH|EXP|EPT|CERT)');

-- SHSH sequence

create sequence s_documents_shsh_number minvalue 1;
