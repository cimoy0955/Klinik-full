<?
DEFINE("TREE_LENGTH",10);
DEFINE("TREE_LENGTH_TIC",10);
DEFINE("TREE_DELIMITER","... ");

DEFINE("STATUS_REGISTRASI","A");
DEFINE("STATUS_REFRAKSI","R");
DEFINE("STATUS_PEMERIKSAAN","M");
DEFINE("STATUS_PREOP","P");
DEFINE("STATUS_OPERASI","O");
DEFINE("STATUS_OPERASI_JADWAL","J");
DEFINE("STATUS_BEDAH","B");
DEFINE("STATUS_DIAGNOSTIK","D");
DEFINE("STATUS_DIAGNOSTIK_TIPE","DT");
DEFINE("STATUS_SELESAI","E");
DEFINE("STATUS_PREMEDIKASI","K");
DEFINE("STATUS_RAWATINAP","I");
DEFINE("STATUS_CEKOUT","C");
DEFINE("STATUS_LABORATORIUM","L");

$rawatStatus[STATUS_REGISTRASI] = "Registrasi";
$rawatStatus[STATUS_REFRAKSI] = "Refraksi";
$rawatStatus[STATUS_PEMERIKSAAN] = "Pemeriksaan";
$rawatStatus[STATUS_PREOP] = "PreOP";
$rawatStatus[STATUS_OPERASI] = "Operasi";
$rawatStatus[STATUS_OPERASI_JADWAL] = "Jadwal";
$rawatStatus[STATUS_BEDAH] = "Bedah Minor";
$rawatStatus[STATUS_DIAGNOSTIK] = "Diagnostik";
$rawatStatus[STATUS_DIAGNOSTIK_TIPE] = "Tipe Diagnostik";
$rawatStatus[STATUS_SELESAI] = "Selesai";
$rawatStatus[STATUS_PREMEDIKASI] = "Premedikasi";
$rawatStatus[STATUS_RAWATINAP] = "Rawat Inap";
$rawatStatus[STATUS_CEKOUT] = "Check Out";
$rawatStatus[STATUS_LABORATORIUM] = "Laboratorium";

$biayaStatus[STATUS_REGISTRASI] = "Registrasi";
$biayaStatus[STATUS_REFRAKSI] = "Refraksi";
$biayaStatus[STATUS_PEMERIKSAAN] = "Pemeriksaan";
$biayaStatus[STATUS_PREOP] = "PreOP";
$biayaStatus[STATUS_OPERASI] = "Operasi";
$biayaStatus[STATUS_OPERASI_JADWAL] = "Jadwal";
$biayaStatus[STATUS_BEDAH] = "Bedah";
$biayaStatus[STATUS_DIAGNOSTIK] = "Diagnostik"; 
$biayaStatus[STATUS_DIAGNOSTIK_TIPE] = "Tipe Diagnostik"; 
$biayaStatus[STATUS_PREMEDIKASI] = "Premedikasi";
$biayaStatus[STATUS_RAWATINAP] = "Rawat Inap";
$biayaStatus[STATUS_LABORATORIUM] = "Laboratorium";
 
$ruangProses[STATUS_REFRAKSI] = "Refraksi";
$ruangProses[STATUS_PEMERIKSAAN] = "Pemeriksaan";
$ruangProses[STATUS_PREOP] = "PreOP";
$ruangProses[STATUS_OPERASI] = "Operasi"; 
$ruangProses[STATUS_BEDAH] = "Bedah";
$ruangProses[STATUS_DIAGNOSTIK] = "Diagnostik"; 
$ruangProses[STATUS_DIAGNOSTIK_TIPE] = "Tipe Diagnostik"; 
$ruangProses[STATUS_PREMEDIKASI] = "Premedikasi";
$ruangProses[STATUS_RAWATINAP] = "Rawat Inap";
$ruangProses[STATUS_LABORATORIUM] = "Laboratorium";

DEFINE("STATUS_ANTRI","0");
DEFINE("STATUS_PROSES","1");
DEFINE("STATUS_MENGINAP","2");

$rawatStatus[STATUS_ANTRI] = "Antri";
$rawatStatus[STATUS_PROSES] = "Proses";
$rawatStatus[STATUS_MENGINAP] = "Menginap";

DEFINE("PGW_JENIS_DOKTER",1);
DEFINE("PGW_JENIS_SUSTER",2);
DEFINE("PGW_JENIS_ADMIN",3);
DEFINE("PGW_JENIS_REFRAKSIONIST",4);

$jenisPegawai[PGW_JENIS_DOKTER] = "Dokter";
$jenisPegawai[PGW_JENIS_SUSTER] = "Perawat";
$jenisPegawai[PGW_JENIS_ADMIN] = "Administrasi";
$jenisPegawai[PGW_JENIS_REFRAKSIONIST] = "Refraksionist";

DEFINE("RAWAT_KEADAAN_BAIK","B");
DEFINE("RAWAT_KEADAAN_LEMAH","L");
DEFINE("RAWAT_KEADAAN_JELEK","J");
DEFINE("RAWAT_KEADAAN_COMA","C");

$rawatKeadaan[RAWAT_KEADAAN_BAIK] = "Baik";
$rawatKeadaan[RAWAT_KEADAAN_LEMAH] = "Lemah";
$rawatKeadaan[RAWAT_KEADAAN_JELEK] = "Jelek";
$rawatKeadaan[RAWAT_KEADAAN_COMA] = "Coma";


DEFINE("JK_LAKILAKI","L");
DEFINE("JK_PEREMPUAN","P");

$jenisKelamin[JK_LAKILAKI] = "Laki-laki";
$jenisKelamin[JK_PEREMPUAN] = "Perempuan";


DEFINE("KAT_OBAT_ANESTESIS","01");
DEFINE("KAT_OBAT_INJEKSI","02");

DEFINE("ICD_DIAGNOSIS","1");
DEFINE("ICD_OPERASI","2");

DEFINE("INA_DIAGNOSIS","1");
DEFINE("INA_OPERASI","2");

DEFINE("BIAYA_LOKET","RJ1-02");
DEFINE("BIAYA_GULA","LB1-18");
DEFINE("BIAYA_ARK","RF1-01");
DEFINE("BIAYA_USG","OK1-11");
DEFINE("BIAYA_GULA_PREOP","4");
DEFINE("BIAYA_INJEKSI","7");
DEFINE("BIAYA_KERATOMETRI","RF1-07");
DEFINE("BIAYA_BIOMETRI","OK1-12");
DEFINE("BIAYA_KARTU","RJ1-01");
DEFINE("BIAYA_EKG","OK1-15");
DEFINE("BIAYA_FUNDUS","OK1-09");
DEFINE("BIAYA_OPTHALMOSCOPY","LB3-50");
DEFINE("BIAYA_OCT","OK1-05");
DEFINE("BIAYA_YAG","OPK1-34");
DEFINE("BIAYA_ARGON","OPK1-35");
DEFINE("BIAYA_GLAUKOMA","IO1-22");
DEFINE("BIAYA_HUMPREY","OK1-07");
DEFINE("BIAYA_SLT","OPK1-33");

// -- biaya pemeriksaan --
DEFINE("BIAYA_UJIMATA","19");

// --- biaya preop ---
DEFINE("BIAYA_GULA_PREOP","4");
DEFINE("BIAYA_GULAREGULASI_PREOP","20");

$bayarNama[BIAYA_KARTU] = "Cetak Kartu Identitas Pasien";

DEFINE("PASIEN_BARU","B");
DEFINE("PASIEN_LAMA","L");

$statusPasien[PASIEN_BARU] = "Baru";
$statusPasien[PASIEN_LAMA] = "Lama";


DEFINE("PASIEN_BAYAR_ASKES","1");
DEFINE("PASIEN_BAYAR_PNS","2");
DEFINE("PASIEN_BAYAR_SWADAYA","3");
DEFINE("PASIEN_BAYAR_JAMKESNAS_PUSAT","4");
DEFINE("PASIEN_BAYAR_JAMKESNAS_DAERAH","5");
DEFINE("PASIEN_KOMPLIMEN","6");
DEFINE("PASIEN_DINASLUAR","7");
DEFINE("PASIEN_BAYAR_JAMKESNAS_KOTA","8");
DEFINE("PASIEN_BAYAR_JAMKESNAS","9");
DEFINE("PASIEN_BAYAR_JAMKESDA_PROV","10");
DEFINE("PASIEN_BAYAR_JAMKESDA_KAB","11");
DEFINE("PASIEN_BAYAR_BPJS_PNS","12");
DEFINE("PASIEN_BAYAR_BPJS_ASTEK","13");
DEFINE("PASIEN_BAYAR_BPJS_JAMKESMAS","14");
DEFINE("PASIEN_BAYAR_LAIN","15");


//$bayarPasien[PASIEN_BAYAR_ASKES] = "Askes/PNS";
//$bayarPasien[PASIEN_BAYAR_PNS] = "PNS";
$bayarPasien[PASIEN_BAYAR_SWADAYA] = "Swadana";
$bayarPasien[PASIEN_BAYAR_BPJS_PNS] = "BPJS PNS/Pal/AB";
$bayarPasien[PASIEN_BAYAR_BPJS_ASTEK] = "BPJS ASTEK";
$bayarPasien[PASIEN_BAYAR_BPJS_JAMKESMAS] = "BPJS Jamkesmas";
//$bayarPasien[PASIEN_BAYAR_JAMKESNAS] = "Jamkesmas";
$bayarPasien[PASIEN_BAYAR_JAMKESDA_PROV] = "Jamkesda Provinsi";
$bayarPasien[PASIEN_BAYAR_JAMKESDA_KAB] = "Jamkesda Kabupaten/Kota";
$bayarPasien[PASIEN_KOMPLIMEN] = "Komplimen";
$bayarPasien[PASIEN_BAYAR_LAIN] = "Lain - Lain";
//$bayarPasien[PASIEN_DINASLUAR] = "Dinas Luar";

$bayarPasien2[PASIEN_BAYAR_ASKES] = "ASK";
$bayarPasien2[PASIEN_BAYAR_PNS] = "PNS";
$bayarPasien2[PASIEN_BAYAR_SWADAYA] = "SWD";
$bayarPasien2[PASIEN_BAYAR_JAMKESNAS_PUSAT] = "JPS";
$bayarPasien2[PASIEN_BAYAR_JAMKESNAS_DAERAH] = "JD";
$bayarPasien2[PASIEN_KOMPLIMEN] = "KMP";
$bayarPasien2[PASIEN_DINASLUAR] = "DL";
$bayarPasien2[PASIEN_BAYAR_JAMKESNAS_KOTA] = "DKK";
$bayarPasien2[PASIEN_BAYAR_BPJS_PNS] = "BPJS PNS";
$bayarPasien2[PASIEN_BAYAR_BPJS_ASTEK] = "BPJS ASTEK";
$bayarPasien2[PASIEN_BAYAR_BPJS_JAMKESMAS] = "BPJS J";
$bayarPasien2[PASIEN_BAYAR_JAMKESDA_PROV] = "JPV";
$bayarPasien2[PASIEN_BAYAR_JAMKESDA_KAB] = "JD";
$bayarPasien2[PASIEN_BAYAR_LAIN] = "LL";

DEFINE("RAWAT_JALAN","M");
DEFINE("RAWAT_INAP","I");
DEFINE("RAWAT_UGD","D");
DEFINE("RAWAT_UMUM","U");

$rawatPasien[RAWAT_JALAN] = "Rawat Jalan";
$rawatPasien[RAWAT_INAP] = "Rawat Inap";
$rawatPasien[RAWAT_UGD] = "UGD";
$rawatPasien[RAWAT_UMUM] = "Pasien Umum";
?>
