<?php
/*
	Tested On
		CI 2.2.0
		GC 1.4.1,1.5.0

	unzip folder & then
	make sure that you have write permission on upload folder

	[root@localhost grocery_crud_multi]# pwd
	/var/www/html/virtual/Development/Admin/grocery_crud_multi

	[root@localhost grocery_crud_multi]# chown -R apache assets/grocery_crud_multiuploader/GC_uploads/


	Access : http:://<YourSite>/grocery_crud_multi/index.php/multiuploader

	1. assets/grocery_crud_multiuploader/
		1.GC_uploads/
			1.pictures
			2.files
			3.mail
		2.scripts/jquery.mousewheel.js
		3.styles/multi_uploader.css

	2. application/library/
		1.Grocery_CRUD_Multiuploader.php

	3. application/controllers/
		1.multiuploader.php

	4. application/views
		1.crud.php
*/
class Multiuploader extends CI_Controller {

function __construct()
{
	parent::__construct();
	$this->load->database();
	$this->load->helper('url');
	$this->load->library('grocery_CRUD');
        $this->load->library('Grocery_CRUD_Multiuploader');
}

function Output_HTML($output = null, $view="crud")
{	
	$this->load->view($view,$output);
}

public function index()
{
	// No required if you have set timezone already... :)
	if( ! ini_get('date.timezone') )
	{ 
		date_default_timezone_set('GMT'); 
	}
	
	try{

	$crud = new Grocery_CRUD_Multiuploader(); 
	$this->db = $this->load->database("default",true);
	$crud->set_table('multi_uploader_gallery');
	$crud->set_subject('Document');

	$col = array("title","my_pictures","my_files","my_mail_attachments");
		
	$crud->fields($col);
	$crud->columns($col);


	$config = array(

		/* Destination directory */
		"path_to_directory"       =>'assets/grocery_crud_multiuploader/GC_uploads/pictures/',

		/* Allowed upload type */
		"allowed_types"           =>'gif|jpeg|jpg|png',

		/* Show allowed file types while editing ? */
		"show_allowed_types"      => true,
	
		/* No file text */
		"no_file_text"            =>'No Pictures',

		/* enable full path or not for anchor during list state */
		"enable_full_path"        => false,

		/* Download button will appear during read state */
		"enable_download_button"  => true,

		/* One can restrict this button for specific types...*/
		"download_allowed"        => 'jpg' 		
	 );
	$crud->new_multi_upload("my_pictures",$config);
	
	$config = array(
		"path_to_directory"       =>'assets/grocery_crud_multiuploader/GC_uploads/files/',
		"allowed_types"           =>'pdf|doc|html',
		"show_allowed_types"      => true,
		"no_file_text"            =>'No files',
		"enable_full_path"        => false,
		"enable_download_button"  => true,
		"download_allowed"        => 'pdf'		
	 );
	$crud->new_multi_upload("my_files",$config);

	$config = array(
		"path_to_directory"       =>'assets/grocery_crud_multiuploader/GC_uploads/mail/',
		"allowed_types"           =>'txt|dat',
		"show_allowed_types"      => true,
		"no_file_text"            =>'No attachments',
		"enable_full_path"        => false,
		"enable_download_button"  => true,
		"download_allowed"        => 'dat'		
	 );
	$crud->new_multi_upload("my_mail_attachments",$config);

	$output = $crud->render();
	$this->Output_HTML($output);	
	
	}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		
	}		

}

}




















