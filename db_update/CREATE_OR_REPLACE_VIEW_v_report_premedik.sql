CREATE OR REPLACE VIEW "klinik"."v_report_premedikasi" AS  SELECT a.preme_id,
    a.id_reg,
    a.preme_lab_tensi,
    a.preme_lab_nafas,
    a.preme_lab_nadi,
    a.preme_tonometri_scale_od,
    a.preme_tonometri_weight_od,
    a.preme_tonometri_pressure_od,
    a.preme_tonometri_od,
    a.preme_tonometri_scale_os,
    a.preme_tonometri_pressure_os,
    a.preme_tonometri_weight_os,
    a.preme_tonometri_os,
    a.id_cust_usr,
    a.preme_waktu,
    a.preme_iol_jenis,
    a.preme_iol_merk,
    a.preme_anestesis_jenis,
    a.preme_anestesis_obat,
    a.preme_anestesis_dosis,
    a.preme_anestesis_komp,
    a.preme_anestesis_pre,
    a.preme_status,
    c.pgw_nama AS dokter_nama,
    e.pgw_nama AS suster_nama,
    g.cust_usr_nama,
    g.cust_usr_jenis_kelamin,
    g.cust_usr_kode
   FROM ((((((klinik.klinik_premedikasi a
     LEFT JOIN klinik.klinik_premedikasi_dokter b ON (((a.preme_id)::text = (b.id_preme)::text)))
     LEFT JOIN hris.hris_pegawai c ON ((b.id_pgw = c.pgw_id)))
     LEFT JOIN klinik.klinik_premedikasi_suster d ON (((a.preme_id)::text = (d.id_preme)::text)))
     LEFT JOIN hris.hris_pegawai e ON ((d.id_pgw = e.pgw_id)))
     LEFT JOIN klinik.klinik_registrasi f ON (((a.id_reg)::text = (f.reg_id)::text)))
     LEFT JOIN global.global_customer_user g ON (((g.cust_usr_id)::text = (f.id_cust_usr)::text)));;