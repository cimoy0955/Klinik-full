CREATE TABLE "klinik"."klinik_operasi_prosedur" ( 
	"op_prosedur_id" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default" NOT NULL,
	"id_op" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default",
	"id_prosedur" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default",
	PRIMARY KEY ( "op_prosedur_id" ) );
 