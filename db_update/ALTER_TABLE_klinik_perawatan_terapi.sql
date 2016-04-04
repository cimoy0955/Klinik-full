ALTER TABLE "klinik"."klinik_perawatan_terapi" DROP COLUMN "id_item";
ALTER TABLE "klinik"."klinik_perawatan_terapi" ADD COLUMN "id_item" Character Varying( 255 ) COLLATE "pg_catalog"."default";