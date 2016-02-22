<?
DEFINE('APP_ID', 'CAREMAX');
DEFINE('APP_TITLE', '.:: Welcome HEAL EXSYS ::.');

// --- connection data ---
DEFINE('DB_DRIVER', 'postgres');
DEFINE('DB_SERVER', '127.0.0.1');
DEFINE('DB_USER', 'SVRTaXRzV2luYXRh');
DEFINE('DB_PASSWORD', 'SVRTaXRzamFicmlrV2luYXRh');
DEFINE('DB_NAME', 'pracoba');

DEFINE('DB_SCHEMA_GLOBAL', 'global');
DEFINE('DB_SCHEMA_KLINIK', 'klinik');
DEFINE('DB_SCHEMA_HRIS', 'hris');
DEFINE('DB_SCHEMA_LOGISTIK', 'logistik');
DEFINE('DB_SCHEMA', 'hris');
DEFINE('DB_SCHEMA_POS', 'pos');
DEFINE('DB_SCHEMA_OPTIK', 'optik');
DEFINE('DB_SCHEMA_APOTIK', 'apotik');
DEFINE('DB_SCHEMA_LAB', 'laboratorium');
DEFINE('DB_SCHEMA_APOTIK_SWADAYA', 'apotik_swadaya');

DEFINE('DB_DEBUGGING',false);
DEFINE("DP_ORDER_ASC", 0);
DEFINE("DP_ORDER_DESC", 1);

DEFINE("DPE_CHAR", 1); /*qstr ''*/
DEFINE("DPE_CLOB", 2); /*qstr ''*/
DEFINE("DPE_DATE", 3); /*DBDate ''*/
DEFINE("DPE_DATETIME", 4);  /*DBDate ''*/
DEFINE("DPE_TIMESTAMP", 5); /*DBDate ''*/
DEFINE("DPE_BOOL", 6);
DEFINE("DPE_NUMERIC", 7);
DEFINE("DPE_BLOB", 8);
DEFINE("DPE_NUMERICKEY", 9);
DEFINE("DPE_CHARKEY", 10);

DEFINE("PRIV_CREATE",0);
DEFINE("PRIV_READ",1);
DEFINE("PRIV_UPDATE",2);
DEFINE("PRIV_DELETE",3);

// --- modul code ---

DEFINE("APP_CAREMAX","10");
DEFINE("APP_OPTIK","11");
DEFINE("APP_LOGISTIK","12");


DEFINE("TIPE_DISTRIBUTOR","00");

DEFINE("ROLE_TIPE_CUSTOMER",1);
DEFINE("ROLE_TIPE_DISTRIBUTOR",2);

DEFINE("USR_TIPE_CUSTOMER","C");
DEFINE("USR_TIPE_DISTRIBUTOR","D");
DEFINE("USR_TIPE_INOSOFT","I");

DEFINE('USER_IDLE','1');	
DEFINE('USER_AKTIF','2');	

DEFINE("SPLIT_PERAWATAN","SA");
DEFINE("SPLIT_OBAT","SB");
DEFINE("SPLIT_VISITE","SC");
DEFINE("SPLIT_INAP","SD");


DEFINE("TINDAKAN_1","TA");
DEFINE("TINDAKAN_2","TB");
DEFINE("TINDAKAN_3","TC");
DEFINE("TINDAKAN_VIP","TD");

$namaTindakan[TINDAKAN_1] = "Tindakan Kelas 1";
$namaTindakan[TINDAKAN_2] = "Tindakan Kelas 2";
$namaTindakan[TINDAKAN_3] = "Tindakan Kelas 3";
$namaTindakan[TINDAKAN_VIP] = "Tindakan Kelas VIP";

$namaFolio[TINDAKAN_1] = "Tindakan Kelas 1";
$namaFolio[TINDAKAN_2] = "Tindakan Kelas 2";
$namaFolio[TINDAKAN_3] = "Tindakan Kelas 3";
$namaFolio[TINDAKAN_VIP] = "Tindakan Kelas VIP";

DEFINE("OBAT_TERAPI_INAP","QA");

$namaObat[OBAT_TERAPI_INAP] = "Biaya Obat Terapi";

$namaFolio[OBAT_TERAPI_INAP] = "Biaya Obat Terapi";

DEFINE("VISITE_1","VA");
DEFINE("VISITE_2","VB");
DEFINE("VISITE_3","VC");
DEFINE("VISITE_VIP","VD");

$namaVisite[VISITE_1] = "Visite Kelas 1";
$namaVisite[VISITE_2] = "Visite Kelas 2";
$namaVisite[VISITE_3] = "Visite Kelas 3";
$namaVisite[VISITE_VIP] = "Visite Kelas VIP";

//SETUP KELAS
DEFINE("KELAS_1","KA");
DEFINE("KELAS_2","KB");
DEFINE("KELAS_3","KC");
DEFINE("KELAS_VIP","KD");
DEFINE("KELAS_IRD","KE");
DEFINE("KELAS_HCU","KF");
DEFINE("KELAS_ICU","KG");


$namaKelas[KELAS_1] = "Biaya Kelas 1 per Hari";
$namaKelas[KELAS_2] = "Biaya Kelas 2 per Hari";
$namaKelas[KELAS_3] = "Biaya Kelas 3 per Hari";
$namaKelas[KELAS_VIP] = "Biaya Kelas Paviliun per Hari";
$namaKelas[KELAS_IRD] = "Biaya Kelas IRD / Perinatlogi per Hari";
$namaKelas[KELAS_HCU] = "Biaya Kelas HCU per Hari";
$namaKelas[KELAS_ICU] = "Biaya Kelas ICU per Hari";

$judulKelas[KELAS_1] = "Kelas 1";
$judulKelas[KELAS_2] = "Kelas 2";
$judulKelas[KELAS_3] = "Kelas 3";
$judulKelas[KELAS_VIP] = "Kelas Paviliun";
$judulKelas[KELAS_IRD] = "Kelas IRD";
$judulKelas[KELAS_HCU] = "Kelas HCU";
$judulKelas[KELAS_ICU] = "Kelas ICU";


$namaFolio[KELAS_1] = "Kelas 1";
$namaFolio[KELAS_2] = "Kelas 2";
$namaFolio[KELAS_3] = "Kelas 3";
$namaFolio[KELAS_VIP] = "Kelas Paviliun";
$namaFolio[KELAS_IRD] = "Kelas IRD";
$namaFolio[KELAS_HCU] = "Kelas HCU";
$namaFolio[KELAS_ICU] = "Kelas ICU";

//Setup Visite
DEFINE("VISITE_1","VA");
DEFINE("VISITE_2","VB");
DEFINE("VISITE_3","VC");
DEFINE("VISITE_VIP","VD");
DEFINE("VISITE_IRD","VE");
DEFINE("VISITE_HCU","VF");
DEFINE("VISITE_ICU","VG");

$namaVisite[VISITE_1] = "Biaya Visite Kelas 1";
$namaVisite[VISITE_2] = "Biaya Visite Kelas 2";
$namaVisite[VISITE_3] = "Biaya Visite Kelas 3";
$namaVisite[VISITE_VIP] = "Biaya Visite Kelas Paviliun";
$namaVisite[VISITE_IRD] = "Biaya Visite Kelas IRD";
$namaVisite[VISITE_HCU] = "Biaya Visite Kelas HCU";
$namaVisite[VISITE_ICU] = "Biaya Visite Kelas ICU";

$namaFolio[VISITE_1] = "Biaya Visite Kelas 1";
$namaFolio[VISITE_2] = "Biaya Visite Kelas 2";
$namaFolio[VISITE_3] = "Biaya Visite Kelas 3";
$namaFolio[VISITE_VIP] = "Biaya Visite Kelas Paviliun";
$namaFolio[VISITE_IRD] = "Biaya Visite Kelas IRD";
$namaFolio[VISITE_HCU] = "Biaya Visite Kelas HCU";
$namaFolio[VISITE_ICU] = "Biaya Visite Kelas ICU";

?>
