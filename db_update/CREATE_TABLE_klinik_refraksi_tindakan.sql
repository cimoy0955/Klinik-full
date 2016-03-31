CREATE TABLE "klinik"."klinik_refraksi_tindakan" ( 
	"ref_tindakan_id" Character Varying( 255 ) COLLATE "pg_catalog"."default" NOT NULL,
	"id_ref" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	"id_tindakan" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	"ref_tindakan_total" Numeric( 16, 2 ),
	"ref_tindakan_bayar" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	"ref_tindakan_urut" SmallInt,
	PRIMARY KEY ( "rawat_tindakan_id" ) );
 