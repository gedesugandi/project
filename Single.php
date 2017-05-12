<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
	
class Single extends Frontend_Controller {

	function __construct(){
		parent::__construct();
		
		// submiting visit
		$now = date('Y-m-d');
		$where = array('date'=>$now);
		$getit = $this->Visitor->get_where($where);
		$visit = NULL;
		if ($getit->num_rows() > 0) {
			$thisDate = $getit->result_array();
			$lastVisit = $thisDate[0]['count'];
			$visit = $lastVisit+1;
			$savingData = array(
				'count' => $visit
				);
			$this->Visitor->save($savingData,'update',$where);
		} else {
			$visit = 1;
			$savingData = array(
				'count' => $visit,
				'date' => $now
				);
			$this->Visitor->save($savingData,'save');
		}
		// Delete OLD visit
		$this->Visitor->dellastvis();
	}

	
	public function index($params='')
	{
		// ====+++++ update new +++========
				
		// setting menu genre Drodow
		$total_genre = count($this->FrontModel->getKategori());
		$offset1 = ceil($total_genre/2);
		$offset2 = $offset1-1;

		$data['tesaja'] = $offset1;
		$data['genreListDropdow_left'] = $this->FrontModel->menu_genreDropdow(0,$offset1);
		$data['genreListDropdow_right'] = $this->FrontModel->menu_genreDropdow($offset1,$total_genre);
		// ====+++++ /update new +++========

		// update visitor
		$visitget = $this->FrontModel->GetVisit($params)->result_array();
		$visitup = @$visitget[0]['view']+1;
		$savingData = array(
			'view' => $visitup
			);
		$this->db->where('slug', $params);
		$this->db->update('tbl_post', $savingData);
		// NavItemList
		$data['navTag'] = $this->FrontModel->getKategori(12);

		
		// actions
		$cek = $this->FrontModel->cekdataSinglepage($params);
		if ($cek > 0) {
			//==== New Updae (Create Popular Post) *** =====
			$tgl = date('Y-m-d');
			$id_posts = @$visitget[0]['id_post'];
			$dataInsert = array(
					'id_post_popular' => $id_posts,
					'tgl' => $tgl
				);
			$this->db->insert('tbl_popular_post',$dataInsert);

			
			$data['singleData'] = $this->FrontModel->ambildatasingle($params)->result_array();
			$data['genredata'] = $this->FrontModel->genreData()->result_object();
			$genre_get = $data['singleData'][0]['genre'];
			$region_get = $data['singleData'][0]['negara'];
			$genre_replace = explode(',', $genre_get);
			$genre_data = $genre_replace[0];
			$data['artikelTerkait'] = $this->FrontModel->getArtikelTerkait($region_get,$genre_data,8);
			$data_Gen_cat = $this->db->get_where('tbl_category',array('id'=>$genre_data))->result_array();
			$data['breandc'] =  @$data_Gen_cat[0]['cat_name'];
			$data['breandcSlug'] =  @$data_Gen_cat[0]['slug_url'];
			$downloaGetID = $data['singleData'][0]['id_video'];
			$data['dataMdlDown'] = $this->FrontModel->getLinkDownload($downloaGetID)->result_object();

			if($data['singleData'][0]['version'] == 'new'){

				// =====****_-_-_-UPDATE-_-_-_****======
				$get = @$this->uri->segment(3);
				$url = @$data['singleData'][0]['streaming_url'];
				// url
				@$filter = explode('url=',$url);
	            @$filter2 = explode('&',$filter[1]);
	            @$urlKey = $filter2[0];

	            // thumbnail
	            // @$filter3 = explode('thumbnail=',$url);
	            // @$filter4 = explode('&',$filter3[1]);
	            // @$thumbnail = $filter4[0];
	            // $data['thumbnail'] = @$thumbnail; 
	             
	            // subtitle
	   //          @$filter5 = explode('sub=',$url);
	   //          @$filter6 = explode('&',$filter5[1]);
	   //          @$sub = $filter6[0];
				// $data['sub'] = @$sub;		
				

	            $linkDrive = base64_decode(urldecode($urlKey));
	            $htmlDrive = file_get_contents_curl($linkDrive);

	            $doc = new DOMDocument();
	            @$doc->loadHTML($htmlDrive);
	            $nodes = $doc->getElementsByTagName("title");
	            $title = str_replace(" - Google Drive", "", @$nodes->item(0)->nodeValue);

	            $idDrive = "";
	            if ($linkDrive) {
	                $pecahLink = explode("/", $linkDrive);
	                $idDrive = $pecahLink[5];
	            }
	            $options = array(
	                "http" => array(
	                    "header"     => "Content-Type: application/json\n",
	                    "method"     => "GET"
	                )
	            );
	            $urlIhik = "http://file.tangituru.com/gdrive/7574616e67/";
	            $dataDrive = file_get_contents_curl($urlIhik . $idDrive);
	            if( ( $response = json_decode( $dataDrive ) ) === NULL )
	            {
	                $data['error_message'] = "
	                		<strong>Error</strong>
	                		 <p>Maaf Video ini tidak bisa diputar untuk saat ini, mungkin terjadi kesalahan saat menampilkan halaman ini atau jaringan internet anda lambat/terputus.</p>
	                	<p><small>Silakan periksa jaringan internet anda dan refresh lagi halaman ini.</small></p>
	                		<p>Ada beberapa faktor lain yang menyebabkan video ini tidak bisa diputar antara lain : 
	                		<ol>
	                			<li>- link yang kami gunakan sudah rusak</li>
	                			<li>- File Video sudah rusak</li>
	                		</ol>
	                		<small>Tolong segera laporkan ke Kami jika masalah ini terjadi di halaman <b><u>kontak</u></b> kami, trimakasih.</small>
	                		</p>
	                	";
	                // exit( " File tidak tersedia " );
	            }
	            $jsonDrive = json_decode($dataDrive);
	            if ($jsonDrive->status != 200) {
	                $data['error_message'] = "
	                		<strong>Error</strong>
	                		 <p>Maaf Video ini tidak bisa diputar untuk saat ini, mungkin terjadi kesalahan saat menampilkan halaman ini atau jaringan internet anda lambat/terputus.</p>
	                	<p><small>Silakan periksa jaringan internet anda dan refresh lagi halaman ini.</small></p>
	                		<p>Ada beberapa faktor lain yang menyebabkan video ini tidak bisa diputar antara lain : 
	                		<ol>
	                			<li>- link yang kami gunakan sudah rusak</li>
	                			<li>- File Video sudah rusak</li>
	                		</ol>
	                		<small>Tolong segera laporkan ke Kami jika masalah ini terjadi di halaman <b><u>kontak</u></b> kami, trimakasih.</small>
	                		</p>
	                	";
	                // die();
	            }
	            $sourceMentah = $jsonDrive->data;
	            $data['sourceMentahS'] = $jsonDrive->data;
	            $sourceMatang = array();
	            foreach ($sourceMentah as $row) {
	                $file = str_replace("explorer", "file.tangituru.com", $row->filename);
	                $object = array(
	                    "label" => $row->label,
	                    "file" => $file."&title=" . $title . "-".$row->label."p",
	                    "type" => "video/mp4"
	                );
	                $sourceMatang[] = $object;
	            }
	            extract($_GET);
	            
	            // foreach ($sourceMentah as $key => $values) {
	            //      $fileUrl[] = $values->filename;
	            //      $fileLabel[] = $values->label;
	            // }


	   //          $resolusi = "";
	   //          $type = "";
	   //          $kualitas_keluar = "";
	   //          $status_kwlitas = "";
				// if($get == 144 || $get == ''){
		  //           $keyss = array_search(144, array_column($sourceMatang, 'label'));
		  //           $resolusi = $fileUrl[$keyss];
		  //           $type = $fileLabel[$keyss];
		  //           $status_kwlitas = 'SD';
		  //       }elseif($get == 240 || $get == ''){
		  //           $keyss = array_search(240, array_column($sourceMatang, 'label'));
		  //           $resolusi = $fileUrl[$keyss];
		  //           $type = $fileLabel[$keyss];
		  //           $status_kwlitas = 'SD';
		  //       }elseif($get == 360 || $get == ''){
		  //           $keyss = array_search(360, array_column($sourceMatang, 'label'));
		  //           $resolusi = $fileUrl[$keyss];
		  //           $type = $fileLabel[$keyss];
		  //           $status_kwlitas = 'SD';
		  //       }elseif($get == 480 || $get == ''){
		  //           $keyss = array_search(480, array_column($sourceMatang, 'label'));
		  //           $resolusi = $fileUrl[$keyss];
		  //           $type = $fileLabel[$keyss];
		  //           $status_kwlitas = 'SD';
		  //       }elseif($get == 720 || $get == ''){
		  //           $keyss = array_search(720, array_column($sourceMatang, 'label'));
		  //           $resolusi = $fileUrl[$keyss];
		  //           $type = $fileLabel[$keyss];
		  //           $status_kwlitas = 'HD';
		  //       }elseif($get == 1080 || $get == ''){
		  //           $keyss = array_search(1080, array_column($sourceMatang, 'label'));
		  //           $resolusi = $fileUrl[$keyss];
		  //           $type = $fileLabel[$keyss];
		  //           $status_kwlitas = 'HD';
		  //       }
		  //       $data['resolusi'] = $resolusi;
		  //       $data['type'] = $type;
		  //       $data['title'] = $title;
    //             $data['status_kwlitas'] = $status_kwlitas;
				// =====****_-_-_-STOP UPDATE-_-_-_****======
			}

// 			$getseason = @$data['singleData'][0]['seasons'];
// 			$data['episode'] = $this->db->query("SELECT * FROM `tbl_post` INNER JOIN (SELECT id_type,code_names,season FROM tbl_type GROUP BY season ) AS tabel_baru on tbl_post.code_name = tabel_baru.code_names WHERE tabel_baru.season = '".$getseason."' AND 
// tbl_post.type = 'TvSeries'  ")->result_array();
			// genre_sidebar
			$data['sidebarTag'] = $this->FrontModel->getKategori();
			// sidebar
			$data['terpopular'] = $this->FrontModel->popular_post(8);
			// title settings
			$data['halaman'] = 'single';
			$data['pageTitle'] = $data['singleData'][0]['title'];
			$data['date_release'] = $data['singleData'][0]['tahun_release'];
			
			//$data['url_gdrive'] = $this->getLink('https://drive.google.com/file/d/0B4Xx-ha3HnYeenpqdXpOUEtPMGs/view?usp=sharing');
			//$data['gdvideo'] = $this->getSources('jwplayer');

			$queryCk = $this->db->query("SELECT view,sum(view) AS countView  FROM tbl_post limit 24 ")->result_array();
			$queryRes = $queryCk[0]['countView'];
			$id_post = $data['singleData'][0]['id_post'];
			$query2 =$this->db->query("SELECT view FROM tbl_post WHERE id_post = '".$id_post."' ")->result_array();
			$jumView =  $query2[0]['view'];
			$targt = $jumView + 100;
			$rms = $jumView/$targt*100;
			$data['ratting'] = round($rms,2).'%';
			$data['jumlahPreview'] = $query2[0]['view'];


			$this->load->view('inc/header',$data);
			$this->load->view('single',$data);
			$this->load->view('inc/footer',$data);
		}else{
			$data['halaman'] = 'error_page';
			$data['pageTitle'] = 'Halamana Tidak Ditemukan';
			$this->load->view('inc/header',$data);
			$this->load->view('Error',$data);	
			$this->load->view('inc/footer',$data);
		}
	}












}

	/* End of file Single.php */
	/* Location: ./application/controllers/Single.php */
 ?>