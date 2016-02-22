<?php
require_once('periode.cls.php');
require_once('perkiraan.cls.php');
require_once('departemen.cls.php');
require_once('job.cls.php');
require_once('job.cls.php');
require_once($APLICATION_ROOT."library/datamodel.cls.php");
/**
 *
 *
 * @version $Id$
 * @copyright 2005
 *    class jurnal_entry
 *       start($id=NULL)
 *       $id == NULL -->nambah transaksi baru
 *          -memasukan ke tabel gl_transaksidetil_tmp dengan values
 *           (nextval('id_gl_transaksidetil_tmp'),'%s', 'test', 'test', 0)
 *          -mendapatkan id_tra (id_transaksi)
 *          -$master_id = id transaksi
 *
 *       add($nomor_perkiraan,$debet,$kredit)
 *           -memasukkan ke tabel gl_transaksidetil_tmp
 *              tra_id = $this->master_id  //id transaksi
 *              prk_id = id dari kode $nomor_perkiraan
 *              jum_trad = $debet + -$kredit
 *
 *       post($id_transaksi,$nama_user,$keterangan_transaksi,$tanggal_transaksi)
 *          -memasukkan ke tabel gl_buffer_transaksi
 *          -mengcopy isi tabel gl_transaksidetil_tmp ke tabel gl_buffer_transaksidetil
 *
 *       batal()
 *          menghapus record pada tabel gl_transaksidetil_tmp dengan tra_id = $this->master_id
 *
 *       get_master_id()
 *          mendapatkan tra_id terbesar + 1 pada gl_transaksidetil_tmp
 *
 *       delete($id_transaksi_detil)
 *          menghapus $id_transaksi_detil
 *
 *       sudah_bertransaksi($id_akun)
 *          apakah $id_akun sudah bertransaksi pada transaksi dengan $master_id
 *
 **/

class jurnal_entry {
    var $conn;
    var $error_msg;
    var $no_ref_baru;
    var $last_id;
    var $master_id;
    var $tanggal_transaksi;
    var $keterangan;
    var $kode_dept;
    var $awal_periode;         //untuk view
    var $akhir_periode;        //untuk view
    var $is_kasir=NULL;

   /***************************************************************************************************
   *  Constructor;
   *  $connection adalah koneksi yang akan dipakai class ini
   *
   */
	function jurnal_entry($connection)
	{
		$this->conn = $connection;
	}

   /***************************************************************************************************
   *  set koneksi yang akan dipakai class ini
   *
   */
	function setConnection($connection)
	{
		$this->conn = $connection;
	}

   /***************************************************************************************************
   *  Get Error Message;
   *
   */
   function ErrorMsg(){
      return $this->error_msg;
   }

   /***************************************************************************************************
   *  start jurnal entry
   *  id == NULL
   *     menambahkan id transaksi pada tabel gl_transaksidetil_tmp
   *  id
   *     update
   */
	function start($tanggal_transaksi,$kode_dept,$kode_job=NULL,$id=NULL) {
      $periode = new periode($this->conn);
      $departemen = new departemen($this->conn);
      $_Job = new job($this->conn);
      $this->kode_dept = $kode_dept;
      $query = sprintf("select max(ref_tra) as maks from gl_buffer_transaksi where ref_tra <> -1 and ref_tra <> -2");
      $result = $this->conn->Execute($query)or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $this->no_ref_baru = $row["maks"]+1;
      
      if($id==NULL)		//nambah baru
		{
         //cek apakah periode ada da
         $result_periode = $periode->cari($tanggal_transaksi);
         if(!$rsperiode = $result_periode->FetchRow()){
            $this->error_msg = "<h3>Tanggal yang anda masukkan tidak termasuk dalam periode yang ada !!!</h3>
                              <h4>Coba masukkan tanggal yang lain...</h4>";
            return NULL;
         }
         $result = $departemen->cari($kode_dept);
         if(!$row= $result->FetchRow()){
            $this->error_msg = " <h3>Kode departemen tidak ada!</h3>
                                 <h4>Coba masukkan kode yang lainnya</h4>";
            return NULL;
         }else  $id_departemen = $row["id_dept"];
         if($kode_job!=NULL){
           $result = $_Job->cari($kode_job);
           if(!$row= $result->FetchRow()){
              $this->error_msg = " <h3>Kode Job tidak ada!</h3>
                                   <h4>Coba masukkan kode yang lainnya</h4>";
              return NULL;
         }else  $id_job = $row["id_job"];
        }
                  
         $this->master_id = $this->get_master_id();

         $dbTable = "gl_transaksidetil_tmp";
            
        $dbField[0] = "id_trad";   // PK
        $dbField[1] = "tra_id";
        $dbField[2] = "prk_id";
        $dbField[3] = "ket_trad";
        $dbField[4] = "jumlah_trad";
        $dbField[5] = "dept_id";
        $dbField[6] = "job_id";
       
        // DPE_NUMERIC,DPE_CHAR,DPE_DATE 
        $dbValue[0] = QuoteValue(DPE_NUMERIC,"nextval('id_gl_transaksidetil_tmp')");
        $dbValue[1] = QuoteValue(DPE_CHAR,$this->master_id);
        $dbValue[2] = QuoteValue(DPE_CHAR,"test");
        $dbValue[3] = QuoteValue(DPE_CHAR,"test");
        $dbValue[4] = QuoteValue(DPE_NUMERIC,0);
        $dbValue[5] = QuoteValue(DPE_CHAR,$id_departemen);
        if($id_job)$dbValue[6] = QuoteValue(DPE_CHAR,$id_job);
        else $dbValue[6] = QuoteValue(DPE_NUMERIC,"null");

        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);
        $dtmodel->Insert() or die("insert  error");	
        
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
		}
		else						//edit
   	{
         $query = sprintf("select * from gl_buffer_transaksi where id_tra = %s", $id);
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
         $row = $result->FetchRow();
         $this->no_ref_baru = $row["ref_tra"];
         $this->tanggal_transaksi = $row["tanggal_tra"];
         $this->keterangan = $row['ket_tra'];
         $id_dept = $row['dept_id'];
         $query = sprintf("delete from gl_transaksidetil_tmp where tra_id = %s",$id);
         $this->conn->Execute( $query )or die($this->conn->ErrorMsg());
         if($this->is_kasir=="KELUAR"){
            $query = sprintf("select * from gl_buffer_transaksidetil where tra_id = %s and jumlah_trad >= 0", $id);
         }else if($this->is_kasir=="MASUK"){
            $query = sprintf("select * from gl_buffer_transaksidetil where tra_id = %s and jumlah_trad <= 0", $id);
         }else{
            $query = sprintf("select * from gl_buffer_transaksidetil where tra_id = %s", $id);
         }
         $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
         while($row = $result->FetchRow()) {
            $query = sprintf("insert into gl.gl_transaksidetil_tmp(id_trad, tra_id, ket_trad, jumlah_trad, prk_id,dept_id,job_id) values (%s, %s, '-', %s, '%s','%s','%s')",
                    "nextval('id_gl_transaksidetil_tmp')",$row['tra_id'],  $row['jumlah_trad'], $row['prk_id'],$row["dept_id"],$row["job_id"]);
            $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
         }
         $result = $departemen->get_kode($id_dept);
         $row = $result->FetchRow();
         $this->kode_dept = $row["kode_dept"];
		}
      return true;
	}

   /***************************************************************************************************
   *  menambah entry pada transaksi
   *
   */
	function add_detil($nomor_perkiraan, $kode_dept, $kode_job=NULL, $keterangan_transaksidetil,$jumlah_transaksidetil) {
      $perkiraan = new perkiraan($this->conn);
      $departemen = new departemen($this->conn);
      $_Job = new job($this->conn);
      $this->kode_dept = $kode_dept;

      $result = $perkiraan->cari($nomor_perkiraan);
      if (!($row = $result->FetchRow())){
         $this->error_msg = "<h3>Kode Perkiraan yang anda Masukkan tidak ada!!!</h3>
                           <h4>Coba masukkan kode yang lain...</h4>";
         echo $this->error_msg ;
         return NULL;
      } else {
         $id_akun = $row["id_prk"];

           if($kode_job!=NULL){
             $result = $_Job->cari($kode_job);
             if(!$row= $result->FetchRow()){
                $this->error_msg = " <h3>Kode Job tidak ada!</h3>
                                     <h4>Coba masukkan kode yang lainnya</h4>";
                return NULL;
             }else  $id_job = $row["id_job"];
            }

//         if($this->sudah_bertransaksi($id_akun) && $keterangan_transaksidetil == "-"){
         if($this->sudah_bertransaksi($id_akun,$id_job) ){
            $this->error_msg = "<h3>Kode Perkiraan yang anda Masukkan sudah ada pada transaksi ini!!!</h3>
                              <h4>Coba masukkan kode yang lain...</h4>";
            return NULL;
         } else {
            $result = $departemen->cari($kode_dept);
            if(!$row= $result->FetchRow()){
               $this->error_msg = "<h3>Kode departemen tidak ada!</h3>
                                    <h4>Coba masukkan kode yang lainnya</h4>";

               return NULL;
            }else  $id_departemen = $row["id_dept"];
           if($kode_job!=NULL){
             $result = $_Job->cari($kode_job);
             if(!$row= $result->FetchRow()){
                $this->error_msg = " <h3>Kode Job tidak ada!</h3>
                                     <h4>Coba masukkan kode yang lainnya</h4>";
                return NULL;
             }else  $id_job = $row["id_job"];
            }

            $dbTable = "gl_transaksidetil_tmp";
            
            $dbField[0] = "id_trad";   // PK
            $dbField[1] = "tra_id";
            $dbField[2] = "prk_id";
            $dbField[3] = "ket_trad";
            $dbField[4] = "jumlah_trad";
            $dbField[5] = "dept_id";
            $dbField[6] = "job_id";
           
            // DPE_NUMERIC,DPE_CHAR,DPE_DATE 
            $dbValue[0] = QuoteValue(DPE_NUMERIC,"nextval('id_gl_transaksidetil_tmp')");
            $dbValue[1] = QuoteValue(DPE_CHAR,$this->master_id);
            $dbValue[2] = QuoteValue(DPE_CHAR,$id_akun);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan_transaksidetil);
            $dbValue[4] = QuoteValue(DPE_NUMERIC,$jumlah_transaksidetil);
            $dbValue[5] = QuoteValue(DPE_CHAR,$id_departemen);
            if($id_job)$dbValue[6] = QuoteValue(DPE_CHAR,$id_job);
            else $dbValue[6] = QuoteValue(DPE_NUMERIC,"null");
    
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);
            $dtmodel->Insert() or die("insert  error");	
            
            unset($dtmodel);
            unset($dbField);
            unset($dbValue);
            unset($dbKey);

         }
      }
	}

   /***************************************************************************************************
   *  Post ke jurnal
   *
   */
	function post($nama_user, $kode_dept, $no_bukti,$keterangan_transaksi,$tanggal_transaksi,$id=NULL)
	{
      $departemen = new departemen($this->conn);
      //buat edit..
      if($id) {
         $query = sprintf("delete from gl_buffer_transaksi where id_tra ='%s'",$id);
         $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $query = sprintf("delete from gl_buffer_transaksidetil where tra_id ='%s'",$id);
         $this->conn->Execute($query) or die ($this->conn->ErrorMsg());
         $mylastid = $id;
      } else {
      //memasukkan ke tabel gl_buffer_transaksi
          $mylastid = $this->conn->GetNewID("gl_buffer_transaksi","id_tra",DB_SCHEMA);
      }

      $this->last_id = $mylastid;
      $result = $departemen->cari($kode_dept);
      if(!$row= $result->FetchRow()){
         $this->error_msg = "<h3>Kode departemen tidak ada!</h3>
                           <h4>Coba masukkan kode yang lainnya</h4>";
         return NULL;
      }else  $id_departemen = $row["id_dept"];

      $query = sprintf("insert into gl_buffer_transaksi(id_tra, ref_tra, tanggal_tra, ket_tra, namauser,real_time,dept_id)
                        values ('%s', %s, '%s', '%s', '%s',%s,'%s')",
                        $mylastid, $no_bukti, $tanggal_transaksi,
                        $keterangan_transaksi, $nama_user,QuoteValue(DPE_DATETIME,time()),
                        $id_departemen);

      $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      //-------------------------------------------------------------------

      //menghapus dari detil....(compatibility buat edit)
      $query = sprintf("select * from gl_transaksidetil_tmp where tra_id = %s and not(prk_id = 'test')",
                        $this->master_id);
      $result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;

      while($row = $result->FetchRow())    {
            $dbTable = "gl_buffer_transaksidetil";
            
            $dbField[0] = "id_trad";   // PK
            $dbField[1] = "tra_id";
            $dbField[2] = "ket_trad";
            $dbField[3] = "jumlah_trad";
            $dbField[4] = "prk_id";
            $dbField[5] = "dept_id";
            $dbField[6] = "job_id";
           
            /* DPE_NUMERIC,DPE_CHAR,DPE_DATE */

            
            if(!$tradId) $tradId = $this->conn->GetNewID("gl_buffer_transaksidetil","id_trad",DB_SCHEMA);
            $dbValue[0] = QuoteValue(DPE_NUMERIC,$tradId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$mylastid);
            $dbValue[2] = QuoteValue(DPE_CHAR,$row['ket_trad']);
            $dbValue[3] = QuoteValue(DPE_CHAR,$row["jumlah_trad"]);
            $dbValue[4] = QuoteValue(DPE_CHAR,$row["prk_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$row["dept_id"]);
            if($row['job_id'])$dbValue[6] = QuoteValue(DPE_CHAR,$row['job_id']);
            else $dbValue[6] = QuoteValue(DPE_NUMERIC,"null");

            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA);
            $dtmodel->Insert() or die("insert  error");	
            
            unset($dtmodel);
            unset($dbField);
            unset($dbValue);
            unset($dbKey);
            unset($tradId);
      }

      $query = sprintf("delete from gl_transaksidetil_tmp where tra_id = %s", $this->master_id);
      $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      //-------------------------------------------------------------------
	}


   /***************************************************************************************************
   *  delete
   *
   */
	function del_detil($id_transaksi_detil) {
      $query = sprintf("delete from gl_transaksidetil_tmp where id_trad = %s", $id_transaksi_detil);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      return $this->result;
	}

   /***************************************************************************************************
   *  Get id untuk tiap transaksi
   *
   */
   function get_master_id(){
      $result = $this->conn->Execute("select max(tra_id)+1 as lastnum from gl_transaksidetil_tmp") or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      if($row['lastnum'] != NULL) $mymasterid = $row["lastnum"];
      else $mymasterid = 1;
      return $mymasterid;
   }

   /***************************************************************************************************
   *  jika membatalkan transaksi
   *
   */
   function batal(){
      $query = sprintf("delete from gl_transaksidetil_tmp where tra_id = %s", $this->master_id);
      $this->result = $this->conn->Execute($query) or die($this->conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  apakah $id_akun sudah ada pada transaksi $this->master_id
   *
   */
   function sudah_bertransaksi($id_akun,$id_job=null){
      
      if($id_job) $query = sprintf("select count(*) as jum from gl_transaksidetil_tmp where prk_id = '%s' and tra_id = '%s' and job_id = '%s'", $id_akun,$this->master_id, $id_job);
      else $query = sprintf("select count(*) as jum from gl_transaksidetil_tmp where prk_id = '%s' and tra_id = '%s'", $id_akun,$this->master_id);
      
      
      $result = $this->conn->Execute($query)or die($this->conn->ErrorMsg());
      $row = $result->FetchRow();
      $jumlah = $row["jum"];

      if($jumlah==0){
         return false;
      }else {
         return true;
      }
   }

   /***************************************************************************************************
   *  hapus jurnal
   *
   */
   function hapus($id)
   {
      $query = sprintf("delete from gl_buffer_transaksidetil where tra_id = %s", $id);
      $this->result =  $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      $query = sprintf("delete from gl_buffer_transaksi where id_tra = %s", $id);
      $this->result =  $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
//      $conn->Execute("optimize table gl_kaskeluar, gl_kasmasuk, gl_buffer_transaksidetil, gl_buffer_transaksi")or die($conn->ErrorMsg());
      return $this->result;
   }

   /***************************************************************************************************
   *  view
   *
   */
   function view($nama_periode,$kode_dept,$sementara=NULL)
   {
      if($sementara) $tabel = "gl_buffer_transaksi";
      else $tabel = "gl_transaksi";

      $departemen = new departemen($this->conn);
      $result = $departemen->cari($kode_dept);
      $row = $result->FetchRow();
      $id_departemen = $row["id_dept"];

      $query = sprintf("select akhir_prd, awal_prd from gl_periode where nama_prd = '%s'",$nama_periode);
      $result =  $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      $jum = $result->RecordCount();
      if($jum==0){
         $this->error_msg = 'Periode Tidak Ditemukan.';
         return NULL;
      }

      $row = $result->FetchRow();
      $this->awal_periode = $row["awal_prd"];
      $this->akhir_periode = $row["akhir_prd"];
      $query = sprintf("select * from $tabel where tanggal_tra >= '%s'
                        and tanggal_tra <= '%s' and dept_id ='%s' order by tanggal_tra, ref_tra",
                        $this->awal_periode,$this->akhir_periode,$id_departemen);
      $this->result =  $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      return $this->result;
   }

   function get_detil($id_transaksi,$sementara=NULL)
   {
      if($sementara) $tabel = "gl_buffer_transaksi";
      else $tabel = "gl_transaksi";
      $query = sprintf("select no_prk,nama_prk,jumlah_trad from ".$tabel."detil,gl_perkiraan
                        where prk_id=id_prk and tra_id = '%s' order by jumlah_trad DESC",
                        $id_transaksi);
      $result =  $this->conn->Execute($query) or die($this->conn->ErrorMsg()) ;
      return $result;
   }

   function posting_to_GL($sementara=NULL)
   {

   }
}

?>
