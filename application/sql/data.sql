
insert into roles (name, description) values ('login', 'Login');
insert into roles (name, description) values ('data', 'Data Entry');
insert into roles (name, description) values ('analysis', 'Data Analysis');
insert into roles (name, description) values ('reports', 'Reporting');
insert into roles (name, description) values ('management', 'Project Management');
insert into roles (name, description) values ('admin', 'Administration');

-- users

insert into users (1, name, username, password) values(id, 'sgs', md5('5gSu8z_'));

-- roles for users

insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('login'));
insert into roles_users (user_id, role_id) values (lookup_user_id('sgs'), lookup_role_id('admin'));

-- species

insert into species (code,class,botanic_name,trade_name,fob_price) values('AFRO','C','Afrosersalisia afzelii','Akuedao',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('AFZ','A','Afzelia spp (bella africana)','Doussie (Afzelia Apa)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ALB','C','Albizzia zygia','Zygia',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ALS','C','Alstonia boonei','Emien',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('AMP','C','Amphimas pterocarpoides','Lati (Bokango)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ANH','C','Anthonotha fragrans','Anthonotha (Kibokoko)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ANI','A','Anigeria robusta','Aningre (Annegre)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ANO','B','Anopyxis klaineana','Kokoti',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ANT','B','Antiaris africana','Ako',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ANTH','C','Anthcliesta nobilis','Cabbage Tree',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ARA','C','Araliopsis tabouensis','Araliopsis (Grenian)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('AUB','C','Aubrevillea platycarpa','Biethi',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('BEI','C','Beilschmiedia mannii','Kanda (Tawa)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('BER','C','Berlinia confusa','Pocouli (Ebiara)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('BOM','B','Bombax buonopozense','Bombax',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('BRA','A','Brachystegia leonensis','Naga',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('BRI','C','Bridelia grandis','Doandoh',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('BUS','C','Bussea occidentalis','Samanta',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CAL','C','Calpocalyz aubrevillei','Badio (Calpocalz)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CAN','A','Canarium schweinfurthii','Aiele',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CEI','A','Ceiba pentandra','Ceiba (Fromager',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CEL','C','Celtis spp (aldolfi-friederiei)','Celtis (Lokenfi)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CHI','A','Chidlowia sanguinea','Bala',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CHL','A','Chlorophora','Iroko (Odum Kambala)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CHR','B','Chrysophyllum spp','Akatio (Longui)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('COM','C','Combretodendron macrocarpum','Abale',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('COP','C','Copaifera salikounda','Etimoe',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('COU','C','Coula edulis','Coula',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CRY','C','Cryptosepalum tetraphyllum','African Pine',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('CYN','B','Cynometra ananta','Apome',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('DAC','C','Dacryodes klaineana','Monkey plum',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('DAN','B','Daniella thurifera','Faro',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('DIA','C','Dialium aubrevillei','kropio (Eyoum)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('DID','B','Didelotia idea','Bondu',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('DIO','C','Diospyros sanzaminika','Ebony',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('DIS','A','Distemonanthus benthamianus','Movingui',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ENTA','A','Entandrophragma angolense','Tiama (Edinam)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ENTC','A','Entandrophragma candollei','Kosipo (Abourd Kro)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ENTCY','A','Entandrophragma cylindricum','Sapele (Sapelle Aboudikro)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ENTU','A','Entandrophragma utile','Sipo (Utile)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ERY','B','Erythrophleum ivorensis','Tali (Sassawood)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ERYM','C','Erythroxylum mannii','Landa',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('FAG','C','Fagara macrophylla Fagara','Olondu',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('FUN','C','Funtumia elastica','Funtumia (Mutundu)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('GIL','A','Gilbertiodendron preussii','Limbali',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('GLU','C','Gluema ivoransis','Adiepingoa',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('GUA','A','Guarea cedrata','Bosse',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('GUI','A','Guibourtia ehie','Amazakoue (Bubinga)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('HAL','A','Hallea ciliata','Abura (Bahia)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('HAN','C','Hannoa klaineana','Hannoa (Effeu)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('HAP','B','Haplormosia macrophylla','Black gum (Idewa)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('IRV','C','Irvingia Gabonensis','Irvingia',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('KHA','A','Khaya anthotheca','Khaya (Acajou blanc)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('KHI','A','Khaya ivorensis','Khaya (Acajou d Afrique)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('KLA','C','Klainedoxa gabonensis','Klainodoxa (Eveuss)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('LOP','A','Lophira alata','Ekki  (Azobe)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('LOV','A','Lovoa trichilioides','Lovoa (Dibetou)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('MAM','B','Mammea africana','Mammea (Oboto)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('MAN','B','Mansonia altissima','Mansonia (Bete)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('MANI','C','Manilkara obovata','Duka (false Makore)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('MON','C','Monopetalanthus compactus','Ekop',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('MUS','C','Musanga cecropioides','African corkwood',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('NAU','A','Nauclea diderrichii','Kusia (Bilinga Opepe)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('NES','B','Nesogordonia papaverifera','Danta (Kotibe)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('NEW','C','Newtonia aubrevillei','Pellegrin',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('OLD','B','Oldfieldia africana','Oldfieldia (Dantoue)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('ONG','C','Ongokea gore','Angueuk (Kuwi)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PAC','C','Pachystela brevipes','Bokulolo',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PAK','C','Parkia bicolor','Parkia (Lo)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PAR','C','Parinari excelsa','Parinari (Songue)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PEN','C','Pentadesma butyracea','Timber-lacewood',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PENT','C','Pentaclethra macrophylla','Oil-bean tree',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PER','B','Pericopsis elata','Afromosia',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PIP','A','Piptadeniastrum africanum','Dahoma',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PTE','B','Pterygota macrocarpa','Koto (Ake)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('PYC','B','Pycnanthus africanus','Ilomba',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('RHO','B','Rhodoguaphalon brevicuape','Alone (Kondrotti)',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('RIC','C','Ricinondendron heudelotii','African Oil tree',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('SAB','C','Sabicea species','Abobonkahyire',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('SAC','B','Sacoglottis gabonensis','Ozouga',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('SAM','C','Samanea dinklagai','Monkey Pod',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('SAN','C','Sanseviera liberica','Africana Hemp',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('STR','C','Strombosia glaucescens','Afina',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('SYM','C','Symphonia globulifera','Symphonia',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('SYN','C','Synsepalum dulcificum','Sweet Berry',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TAR','A','Heritiera utilis','Niangon (Whismore)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TEI','A','Terminalia ivorensis','Framire (Baji Emire)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TES','A','Terminalia superba','Frake (Limba Afara)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TET','A','Tetraberlinia tubmaniana','Tetra (Sikon)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TIE','A','Tieghemella heckelii','Makore (Baku Douka)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TRI','A','Triplochiton scleroxylon','Obeche (SambaWawa)',3);
insert into species (code,class,botanic_name,trade_name,fob_price) values('TUR','B','Turraeanthus africanus','Avodire',2);
insert into species (code,class,botanic_name,trade_name,fob_price) values('UAP','C','Uapaca guinensis','Uapaca (Rikio)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('UNK','C','Unknown','Unknown',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('XYL','C','Xylia evansii','Dan (Mano)',1);
insert into species (code,class,botanic_name,trade_name,fob_price) values('XYLO','C','Xylopia aethiopica','Guinea Papper Tree (Okala)',1);
