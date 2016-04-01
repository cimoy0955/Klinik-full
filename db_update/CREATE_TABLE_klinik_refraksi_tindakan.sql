CREATE TABLE "klinik"."klinik_refraksi_tindakan" ( 
	"ref_tindakan_id" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default" NOT NULL,
	"id_ref" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default",
	"id_tindakan" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default",
	"ref_tindakan_total" NUMERIC( 16, 2 ),
	"ref_tindakan_bayar" CHARACTER VARYING( 255 ) COLLATE "pg_catalog"."default",
	"ref_tindakan_urut" SMALLINT,
	PRIMARY KEY ( "ref_tindakan_id" ) );
 