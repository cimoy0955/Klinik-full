CREATE TABLE "klinik"."klinik_jadwal_admin" ( 
	"jadwal_admin_id" Character Varying( 255 ) COLLATE "pg_catalog"."default" NOT NULL,
	"id_preop" Character Varying( 255 ) COLLATE "pg_catalog"."default" NOT NULL,
	"id_pgw" Bigint NOT NULL,
	PRIMARY KEY ( "jadwal_admin_id" ) );