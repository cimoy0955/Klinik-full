CREATE TABLE "global"."global_rujukan_dokter" ( 
	"rujukan_dokter_id" Integer NOT NULL,
	"rujukan_dokter_nama" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	"rujukan_dokter_alamat" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	"id_kota" Bigint,
	"rujukan_dokter_telp" Character Varying( 15 ) COLLATE "pg_catalog"."default",
	"rujukan_dokter_kode_rekening_poin" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	"rujukan_dokter_bank" Character Varying( 255 ) COLLATE "pg_catalog"."default",
	PRIMARY KEY ( "rujukan_dokter_id" ) );
 COMMENT ON TABLE  "global"."global_rujukan_dokter" IS 'Sebagai Master Rujukan Rumah Sakit';