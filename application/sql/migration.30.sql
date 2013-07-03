
-- block approval

create domain d_block_status as character(1) check (value ~ E'^[PAR]$');

alter table blocks add column status d_block_status default 'P' not null;


