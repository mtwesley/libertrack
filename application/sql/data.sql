
insert into roles (name, description) values ('login', 'Login');
insert into roles (name, description) values ('data', 'Data Entry');
insert into roles (name, description) values ('analysis', 'Data Analysis');
insert into roles (name, description) values ('reports', 'Reporting');
insert into roles (name, description) values ('management', 'Project Management');
insert into roles (name, description) values ('admin', 'Site and Operator Configuration');
insert into roles (name, description) values ('users', 'User Management');
insert into roles (name, description) values ('barcodes', 'Barcode Management');
insert into roles (name, description) values ('invoices', 'Invoice Management');
insert into roles (name, description) values ('exports', 'Export Management');
insert into roles (name, description) values ('tolerances', 'Accuracy and Tolerance Management');

-- users

insert into users (id, name, username, password) values(1, 'SGS', 'sgs', md5('5gSu8z_'));

-- roles for users

insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('login'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('data'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('analysis'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('reports'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('management'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('admin'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('users'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('barcodes'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('exports'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('invoices'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('tolerances'));

-- species

insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('AFRO','C','Afrosersalisia afzelii','Akuedao',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('AFZ','A','Afzelia spp (bella africana)','Doussie (Afzelia Apa)',260.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ALB','C','Albizzia zygia','Zygia',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ALS','C','Alstonia boonei','Emien',170.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('AMP','C','Amphimas pterocarpoides','Lati (Bokango)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ANH','C','Anthonotha fragrans','Anthonotha (Kibokoko)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ANI','A','Anigeria robusta','Aningre (Annegre)',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ANO','B','Anopyxis klaineana','Kokoti',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ANT','B','Antiaris africana','Ako',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ANTH','C','Anthcliesta nobilis','Cabbage Tree',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ARA','C','Araliopsis tabouensis','Araliopsis (Grenian)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('AUB','C','Aubrevillea platycarpa','Biethi',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('BEI','C','Beilschmiedia mannii','Kanda (Tawa)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('BER','C','Berlinia confusa','Pocouli (Ebiara)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('BOM','B','Bombax buonopozense','Bombax',190.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('BRA','A','Brachystegia leonensis','Naga',170.00,90);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('BRI','C','Bridelia grandis','Doandoh',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('BUS','C','Bussea occidentalis','Samanta',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CAL','C','Calpocalyz aubrevillei','Badio (Calpocalz)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CAN','A','Canarium schweinfurthii','Aiele',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CEI','A','Ceiba pentandra','Ceiba (Fromager',170.00,90);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CEL','C','Celtis spp (aldolfi-friederiei)','Celtis (Lokenfi)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CHI','A','Chidlowia sanguinea','Bala',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CHL','A','Chlorophora','Iroko (Odum Kambala)',250.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CHR','B','Chrysophyllum spp','Akatio (Longui)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('COM','C','Combretodendron macrocarpum','Abale',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('COP','C','Copaifera salikounda','Etimoe',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('COU','C','Coula edulis','Coula',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CRY','C','Cryptosepalum tetraphyllum','African Pine',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('CYN','B','Cynometra ananta','Apome',150.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('DAC','C','Dacryodes klaineana','Monkey plum',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('DAN','B','Daniella thurifera','Faro',180.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('DIA','C','Dialium aubrevillei','kropio (Eyoum)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('DID','B','Didelotia idea','Bondu',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('DIO','C','Diospyros sanzaminika','Ebony',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('DIS','A','Distemonanthus benthamianus','Movingui',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ENTA','A','Entandrophragma angolense','Tiama (Edinam)',230.00,90);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ENTC','A','Entandrophragma candollei','Kosipo (Abourd Kro)',170.00,90);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ENTCY','A','Entandrophragma cylindricum','Sapele (Sapelle Aboudikro)',170.00,90);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ENTU','A','Entandrophragma utile','Sipo (Utile)',170.00,100);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ERY','B','Erythrophleum ivorensis','Tali (Sassawood)',270.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ERYM','C','Erythroxylum mannii','Landa',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('FAG','C','Fagara macrophylla Fagara','Olondu',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('FUN','C','Funtumia elastica','Funtumia (Mutundu)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('GIL','A','Gilbertiodendron preussii','Limbali',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('GLU','C','Gluema ivoransis','Adiepingoa',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('GUA','A','Guarea cedrata','Bosse',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('GUI','A','Guibourtia ehie','Amazakoue (Bubinga)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('UNK','C','Unknown','Unknown',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('HAL','A','Hallea ciliata','Abura (Bahia)',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('HAN','C','Hannoa klaineana','Hannoa (Effeu)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('HAP','B','Haplormosia macrophylla','Black gum (Idewa)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('IRV','C','Irvingia Gabonensis','Irvingia',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('KHA','A','Khaya anthotheca','Khaya (Acajou blanc)',170.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('KHI','A','Khaya ivorensis','Khaya (Acajou d Afrique)',170.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('KLA','C','Klainedoxa gabonensis','Klainodoxa (Eveuss)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('LOP','A','Lophira alata','Ekki  (Azobe)',250.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('LOV','A','Lovoa trichilioides','Lovoa (Dibetou)',180.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('MAM','B','Mammea africana','Mammea (Oboto)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('MAN','B','Mansonia altissima','Mansonia (Bete)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('MANI','C','Manilkara obovata','Duka (false Makore)',190.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('MON','C','Monopetalanthus compactus','Ekop',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('MUS','C','Musanga cecropioides','African corkwood',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('NAU','A','Nauclea diderrichii','Kusia (Bilinga Opepe)',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('NES','B','Nesogordonia papaverifera','Danta (Kotibe)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('NEW','C','Newtonia aubrevillei','Pellegrin',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('OLD','B','Oldfieldia africana','Oldfieldia (Dantoue)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('ONG','C','Ongokea gore','Angueuk (Kuwi)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PAC','C','Pachystela brevipes','Bokulolo',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PAK','C','Parkia bicolor','Parkia (Lo)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PAR','C','Parinari excelsa','Parinari (Songue)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PEN','C','Pentadesma butyracea','Timber-lacewood',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PENT','C','Pentaclethra macrophylla','Oil-bean tree',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PER','B','Pericopsis elata','Afromosia',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PIP','A','Piptadeniastrum africanum','Dahoma',190.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PTE','B','Pterygota macrocarpa','Koto (Ake)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('PYC','B','Pycnanthus africanus','Ilomba',170.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('RHO','B','Rhodoguaphalon brevicuape','Alone (Kondrotti)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('RIC','C','Ricinondendron heudelotii','African Oil tree',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('SAB','C','Sabicea species','Abobonkahyire',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('SAC','B','Sacoglottis gabonensis','Ozouga',170.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('SAM','C','Samanea dinklagai','Monkey Pod',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('SAN','C','Sanseviera liberica','Africana Hemp',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('STR','C','Strombosia glaucescens','Afina',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('SYM','C','Symphonia globulifera','Symphonia',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('SYN','C','Synsepalum dulcificum','Sweet Berry',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TAR','A','Heritiera utilis','Niangon (Whismore)',280.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TEI','A','Terminalia ivorensis','Framire (Baji Emire)',203.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TES','A','Terminalia superba','Frake (Limba Afara)',170.00,70);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TET','A','Tetraberlinia tubmaniana','Tetra (Sikon)',190.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TIE','A','Tieghemella heckelii','Makore (Baku Douka)',170.00,100);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TRI','A','Triplochiton scleroxylon','Obeche (SambaWawa)',170.00,90);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('TUR','B','Turraeanthus africanus','Avodire',170.00,80);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('UAP','C','Uapaca guinensis','Uapaca (Rikio)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('XYL','C','Xylia evansii','Dan (Mano)',170.00,60);
insert into species (code,class,botanic_name,trade_name,fob_price,min_diameter) values ('XYLO','C','Xylopia aethiopica','Guinea Papper Tree (Okala)',170.00,60);

-- tolerances

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_survey_line',2,20);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDF','is_matching_length',2,10);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDF','is_matching_volume',0.2,2);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SPECS','is_matching_volume',0.2,2);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SSFV','is_matching_survey_line',2,20);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SSFV','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('SSFV','is_matching_height',2,10);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDFV','is_matching_diameter',5,40);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('TDFV','is_matching_length',2,10);

insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDFV','is_matching_diameter',5,30);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDFV','is_matching_length',0.5,2);
insert into tolerances (form_type,"check",accuracy_range,tolerance_range) values ('LDFV','is_matching_volume',0.2,2);

-- fees

insert into fees (type,value,name,description,tax_code) values ('P',0.652,'Stumpage Fee (GoL share)','FDA Regulation 107-7, Section 22(b)','1415-14');
insert into fees (type,value,name,description,tax_code) values ('P',0.348,'Chain of Custody Stumpage Share','GoL-SGS Contract','1415-15');
insert into fees (type,value,name,description,tax_code) values ('P',1.000,'Log and Wood Product Export Fee','FDA Regulation 107-7, Section 44-45','1415-17');
insert into fees (type,value,name,description,tax_code) values ('P',0.014,'Chain of Custody Management Fee','GoL-SGS Contract (1.4% of FOB Value)','1415-18');
insert into fees (type,value,name,description,tax_code) values ('F',100,'Timber Export License Fee','FDA Regulation 107-7, Section 42(c)','1415-16');
