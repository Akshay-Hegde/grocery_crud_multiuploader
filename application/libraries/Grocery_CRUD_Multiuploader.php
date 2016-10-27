<?php
/**
 * PHP Grocery_CRUD_Multiuploader
 *
 * 
 *
 * @package    	Grocery_CRUD_Multiuploader
 * @author     	Akshay Hegde <akshay.k.hegde@gmail.com>
 *              www.linkedin.com/profile/view?id=206267783
 *		https://github.com/Akshay-Hegde
 *
 * @version    	1.0.2 ( update callback fix (28-07-2015 13:17:00), function_renamed (11-08-2015 10:24:00 ) )
 * @copyright  	Copyright (c) 2010 through 2014, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
 *
 * Thanks
 * John Skoumbourdis, Amit Shah & Victor
 */
class Grocery_CRUD_Multiuploader extends grocery_CRUD
{
	protected $callback_read_field	= array();
	
	protected $multi_upload_function ;  

	/* multiupload css directory */ 
	protected $multiupload_css_path        = 'assets/grocery_crud_multiuploader/styles/';

	/* multiupload js directory */ 
	protected $multiupload_javascript_path = 'assets/grocery_crud_multiuploader/scripts/';

	/* Default upload directory */ 
	protected $path_to_directory           = 'assets/grocery_crud_multiuploader/GC_uploads';

	/* No file text on list/read state */ 
	protected $no_file_text                = '<p>Empty</p>';
	
	/* Anchor text */ 
	protected $enable_full_path            = true;
	
	/* download button on read state */ 
	protected $enable_download_button      = true;

	/* download button filetypes read state */ 
	protected $download_allowed            = null;


	/* Table where files saved */
	protected $file_table;

	/* Primary Key of table */
	protected $primary_key;

	/* upload Field */
	protected $upload_field;

	/* Allowed file types */
	protected $allowed_types      = 'gif|jpeg|jpg|png|pdf|doc';

	/* Show allowed types - edit state */
	protected $show_allowed_types = true;	

	/* Upload options */
	protected $hash_fields = array(
					"upload_field",
					"allowed_types",
					"show_allowed_types",
					"path_to_directory",
					"no_file_text",
					"enable_full_path",
					"enable_download_button",
					"download_allowed"
				      );

	/* Temp storage */
	protected $hash = array();

	public $basic_model,$ci;
	
	
	/*
	 * Constructor - Initializes and references CI
	*/
	public function __construct()
	{
		parent::__construct();
		$this->ci = &get_instance();
		$this->ci->load->model('grocery_CRUD_Model');
		$this->basic_model = new grocery_CRUD_Model();
		log_message('debug', "Grocery_CRUD_Multiuploader Class Initialized.");
	}
	
	/*
	/* Callback read field
	/* Thanks Amit shah for hint...
	*/
	public function callback_read_field($field, $callback = null)
	{
		$this->callback_read_field[$field] = $callback;
		return $this;
	}

	
	/*
	*  Nothing much changed added field info 
	*/
	protected function change_list($list,$types)
	{
		$primary_key    = $this->get_primary_key();
		$has_callbacks  = !empty($this->callback_column);
		$output_columns = $this->get_columns();

		foreach($list as $num_row => $row)
		{
			foreach($output_columns as $column)
			{
				$field_name 	= $column->field_name;
				$field_value 	= isset( $row->{"$column->field_name"} ) ? $row->{"$column->field_name"} : null;
				if( $has_callbacks && isset($this->callback_column[$field_name]) )
					$list[$num_row]->$field_name = call_user_func($this->callback_column[$field_name], $field_value, $row,$this->get_field_types()[$column->field_name]);
				elseif(isset($types[$field_name]))
					$list[$num_row]->$field_name = $this->change_list_value($types[$field_name] , $field_value);
				else
					$list[$num_row]->$field_name = $field_value;
			}
		}

		return $list;
	}

	
	/*
	 * _is_image
	 *
	 * @access	public
	 * @param	string
	 * @return      boolean
	 */
	function _is_image($name)
	{
		$imgs = array('.jpg','.png','.jpeg','.gif','.tiff');
		$inp  = array(substr($name, -4),substr($name, -5));		
		return count(array_intersect($imgs, $inp)) > 0;
	}

	
	/*
	 * _reset - used for multiple upload field...
	 * 
	 * @access	public
	 * @param	string
	 * @return      void
	 */
	function _reset($field)
	{	
		foreach($this->hash_fields as $f){
			$this->{"$f"} = $this->hash[$field][$f];
		}
	}


	/*
	 * remote_file_exists 
	 * 
	 * @access	public
	 * @param	string
	 * @return      boolean
	 */
	function remote_file_exists($url)
	{
	  	return(bool)preg_match( '~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)) );
	}


	/*
	 * segment_check 
	 * 
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return      void
	 */
	function segment_check($fun, $field)
	{
	   if($this->ci->input->post("field") && $this->ci->input->post("field") === $field)
	   {
		$this->_reset($field);

		$seg = $this->ci->uri->segments;

		if(!empty($seg))	
		{
			if(
				$seg[count($seg)-1] === $fun && 
				in_array($seg[count($seg)],array('uploade','delete_file'))
		  	  )
			  {
				switch ($seg[count($seg)])
				{
					case 'uploade':
						$this->multiupload_file();
						break;
					case 'delete_file':
						$this->delete_file();
						break;
					default:
						break;
				}
	
				die();
			  }
		}
	   }
	}
	
	/*
	 * Callback override fix for callback_before_(insert|update|delete)
	 * 
	 * @access	protected
	 * @return      void
	 */

	protected function pre_render()
	{
		$this->_initialize_variables();
		$this->_initialize_helpers();
		$this->_load_language();
		$this->state_code = $this->getStateCode();

		if($this->basic_model === null)
			$this->set_default_Model();

		$this->set_basic_db_table($this->get_table());

		$this->_load_date_format();

		$this->_set_primary_keys_to_model();


		switch($this->state_code)
		{
			case 4 : // before delete
				 $this->_delete_this_key($this->get_state_info_from_url()->first_parameter);
				 break;

			case 5 : // before insert
				 $_POST = $this->_set_files($_POST);
				 break;
			case 6 : 
				 // before update
				 $_POST = $this->_set_files($_POST,$this->get_state_info_from_url()->first_parameter);
				 break;
		}

	}
	
	/*
	 * set_callbacks 
	 * 
	 * @access	protected
	 * @param	string
	 * @return      void
	 */
	protected function set_callbacks($file_field)
	{

		/* Callback on list state */
		$this->callback_column($file_field,array($this,'_file_url'));
	
		/* Callback on add state */
		$this->callback_add_field($file_field,array($this, 'add_upload_field'));
		
		/* Callback on edit state */
		$this->callback_edit_field($file_field,array($this, 'edit_upload_fied'));

		
		/* Callback on read state */
		// GC < 1.5 we have some issue...	
		$version = explode(".",grocery_CRUD::VERSION);	
		switch($version[1])
		{
		   case   5:
			    $this->callback_read_field($file_field, array($this,'view_upload_field'));
			    break;
		   default : 
			   if($this->getState() == "read")
			   {
				$this->callback_field($file_field, array($this,'view_upload_field'));
			   }else
			   {
				$this->callback_read_field($file_field, array($this,'view_upload_field'));
			   }
		}
	
	}



	/*
	 * new_multi_upload
	 *
	 * @access	public
	 * @param	upload-field
	 * @param	obj/array
	 * @return	void
	 */
	public function new_multi_upload($field=null, $obj=null)
	{
		/* Whether field is null */
		if(is_null($field))
		{
			throw new Exception("field is mandotory");
			die();         
		}

		$this->file_table   = $this->basic_db_table;
		$this->primary_key  = $this->primary_key = $this->basic_model->get_primary_key($this->file_table);
		$this->multi_upload_function = __FUNCTION__;
		$this->upload_field = $field;

		/* Whether field exists in table ? */
		if(!$this->basic_model->field_exists($this->upload_field,$this->file_table))
		{
			throw new Exception("field : ".$this->upload_field." Not Found in table ".$this->file_table);
			die(); 
		}

		/* Override default configuration */
		if (!is_null($obj) && is_array($obj) && !empty($obj) )
		{
             	  foreach ($obj as $k => $v)
		  {
                	if (property_exists($this,$k)) 
			{
				$this->{"$k"} = $v;
                	}else
			{
				log_message('debug', "$k doesn't exists in Grocery_CRUD_Multiuploader.");
			}
             	  }
         	}
		
		/* Upload directory exists ? */		
		if(!file_exists($this->path_to_directory))
		{
			throw new Exception("Directory does not exist : ".$this->path_to_directory);
			die(); 
		}		

		/* Upload directory has write permission ? */	
		if(!is_writable($this->path_to_directory))
		{
			throw new Exception("Not writable : ".$this->path_to_directory."\n Current Permission : ".substr(sprintf('%o', fileperms($this->path_to_directory)), -4));
			die(); 	
		}
		
		/* temp storage */
		foreach($this->hash_fields as $f){
			$this->hash[$field][$f] = $this->{"$f"};
		}

		/* Check whether req is to upload file / delete file */
		$this->segment_check(__FUNCTION__,$field);

		/* Initialize Callbacks */
		$this->set_callbacks($field);

		/* Set Scripts */
		$this->set_scripts();

	}
	
	
	/*
	 * set_scripts
	 * 
	 * @access	protected
	 * @return      void
	 */
	protected function set_scripts()
	{ 

	  $css = array(
			'assets/grocery_crud/css/ui/simple/' . grocery_CRUD::JQUERY_UI_CSS,
			'assets/grocery_crud/css/jquery_plugins/file_upload/file-uploader.css',
			'assets/grocery_crud/css/jquery_plugins/file_upload/jquery.fileupload-ui.css',
			'assets/grocery_crud/css/jquery_plugins/fancybox/jquery.fancybox.css',
			$this->multiupload_css_path.'multi_uploader.css',
		      );

	  $js  = array(
			'assets/grocery_crud/js/' . grocery_CRUD::JQUERY,
			'assets/grocery_crud/js/jquery_plugins/ui/' . grocery_CRUD::JQUERY_UI_JS,
			'assets/grocery_crud/js/jquery_plugins/tmpl.min.js',
			'assets/grocery_crud/js/jquery_plugins/load-image.min.js',
			'assets/grocery_crud/js/jquery_plugins/jquery.iframe-transport.js',
			'assets/grocery_crud/js/jquery_plugins/jquery.fileupload.js',
			'assets/grocery_crud/js/jquery_plugins/config/jquery.fileupload.config.js',
			'assets/grocery_crud/js/jquery_plugins/jquery.fancybox.pack.js',
			'assets/grocery_crud/js/jquery_plugins/jquery.easing-1.3.pack.js',
			'assets/grocery_crud/js/jquery_plugins/config/jquery.fancybox.config.js',
			$this->multiupload_javascript_path.'jquery.mousewheel.js',
		      );

	   foreach($css as $c){
		$this->set_css($c);
	   }

	   foreach($js as $c){
		$this->set_js($c);
	   }
	}

	
	/*
	 * add_upload_field callback
	 * 
	 * @access	public
	 * @return      string
	 */
	function add_upload_field()
	{
		$args = func_get_args();
		$name = $args[2]->name;

		$this->_reset( $name );


	        $html = '<div>
			<span class="fileinput-button qq-upload-button" id="' . $this->upload_field . '_upload-button-svc">
				<span>Upload a file</span>
				<input type="file" name="' . $this->upload_field . '_new_multi_upload" id="' . $this->upload_field . '_new_multi_upload_field" >
			</span>
                       '.( $this->show_allowed_types ? '<span class="allowed_types '.$this->upload_field.'_allowed_types">'.str_replace('|',',',$this->allowed_types).'</span>' : null ) .'
                        <span class="qq-upload-spinner" id="ajax-loader-file" style="display:none;"></span>
			<span id="' . $this->upload_field . '_progress-multiple" style="display:none;"></span>
		</div>
		<select name="' . $this->upload_field . '_files[]" multiple="multiple" size="8" class="multiselect" id="' . $this->upload_field . '_multiple_select" style="display:none;">
		</select>
		<div id="' . $this->upload_field . '_list_svc" class="mutiupload_list" style="margin-top: 40px;">
		</div>';

		$html.=$this->JS( $name  );

		return $html;
	}


	function edit_upload_fied($value, $primary_key)
	{
		$args = func_get_args();
		$name = $args[2]->name;


		$this->_reset( $name );

		$result = $this->ci->db->get_where($this->file_table,array($this->primary_key => $primary_key));
		$result = $result->result_array();
				
		if(!empty($result))
		{
			$files = unserialize($result[0][$this->upload_field]);
		}else
		{
			$files = array();
		}
		
		$html = '<div>
			 <span class="fileinput-button qq-upload-button" 
			         id="' . $this->upload_field . '_upload-button-svc">
			 <span>Upload a file</span>
			 <input type="file" 
				name="' . $this->upload_field . '_new_multi_upload" 
				id="' . $this->upload_field . '_new_multi_upload_field" >
			 </span>
                         '.( $this->show_allowed_types ? '<span class="allowed_types '.$this->upload_field.'_allowed_types">'.str_replace('|',',',$this->allowed_types).'</span>' : null ) .'
			 <span class="qq-upload-spinner" 
				  id="ajax-loader-file" 
			       style="display:none;"></span>
			 <span    id="' . $this->upload_field . '_progress-multiple" 	
			       style="display:none;"></span>
			</div>';

		$html.= '<select 
				name="' . $this->upload_field . '_files[]" 
			    multiple="multiple" 
			        size="8" 
			       class="multiselect" 
				  id="' . $this->upload_field . '_multiple_select" 
                               style="display:none;">';

		if (!empty($files))
		{
			foreach ($files as $items)
			{
				$html.="<option value=" . $items . " selected='selected'>" . $items . "</option>";
			}
		}
		$html.='</select>';
		$html.='<div id="' . $this->upload_field . '_list_svc" 
			     class="mutiupload_list" 
			     style="margin-top: 40px;">';

		if (!empty($files))
		{
			foreach ($files as $items)
			{
			   $thisfile = base_url() . $this->path_to_directory . $items ;
			   if($this->remote_file_exists($thisfile))
			   {			

				if( strpos ($items,"." ) !== false )
				{

				if ($this->_is_image($items) === true)
				{
					$html.= '<div id="' . $items . '">';
					$html.= '<a href="' .$thisfile. '" class="image-thumbnail" id="fancy_' . $items . '">';
					$html.='<img src="' . $thisfile . '" height="50"/>';
					$html.='</a><br>';
					$html.='<a href="javascript:" onclick="delete_' . $this->upload_field . '_svc($(this),\'' . $items . '\')" style="color:red;" >Delete</a>';
					$html.='</div>';
				}
				else
				{
					$html.='<div id="' . $items . '" >
					<span>' . $items . '</span>
					<a href="javascript:" onclick="delete_' . $this->upload_field . '_svc($(this),\'' . $items . '\')" style="color:red;" >Delete</a>
					</div>';
				}

				}

			    }
			}
		}
		$html.='</div>';
		$html.=$this->JS($name);
		return $html;
	}



	 /*
	 * JS
	 * @param	upload-field
	 * @access	protected
	 * @return      string
	 */
	 protected function JS($field=null)
	 {
		
		if(is_null($field))return $field;
		
	 	$js = "
 		if (typeof string_progress === 'undefined') {
 			var string_upload_file 	= 'Upload a file';
			var string_delete_file 	= 'Deleting file';
			var string_progress 			= 'Progress: ';
			var error_on_uploading 			= 'An error has occurred on uploading.';
			var message_prompt_delete_file 	= 'Are you sure that you want to delete this file?';

			var error_max_number_of_files 	= 'You can only upload one file each time.';
			var error_accept_file_types 	= 'You are not allow to upload this kind of extension.';
			var error_max_file_size 		= 'The uploaded file exceeds the 5000MB directive that was specified.';
			var error_min_file_size 		= 'You cannot upload an empty file.';

 		}	
	 	function delete_" . $this->upload_field . "_svc(link,filename)
	 	{
	 		$('#" . $this->upload_field . "_multiple_select option[value=\"'+filename+'\"]').remove();
	 		link.parent().remove();
	 		$.post('" . $this->multi_upload_function . "/delete_file', {'file_name':filename,'".$this->ci->security->get_csrf_token_name()."':'".$this->ci->security->get_csrf_hash()."','field':'".$field."','state':window.location.href.substring(window.location.href.lastIndexOf('/') + 1)}, function(json){
	 			if(json.succes == 'true')
	 			{
	 				console.log('json data', json);
	 			}
	 		}, 'json');
}

$(document).ready(function() {
	$('#" . $this->upload_field . "_new_multi_upload_field').fileupload({
		url: '" . $this->multi_upload_function . "/uploade',
		sequentialUploads: true,
		formData:{'".$this->ci->security->get_csrf_token_name()."':'".$this->ci->security->get_csrf_hash()."','field':'".$field."'},
		cache: false,
		autoUpload: true,
		dataType: 'json',
		acceptFileTypes: /(\.|\/)(" . $this->ci->config->item('grocery_crud_file_upload_allow_file_types') . ")$/i,
		limitMultiFileUploads: 1,
		beforeSend: function()
		{
			$('#" . $this->upload_field . "_upload-button-svc').slideUp('fast');
			$('#ajax-loader-file').css('display','block');
			$('#" . $this->upload_field . "_progress-multiple').css('display','block');
		},
		progress: function (e, data) {
			$('#" . $this->upload_field . "_progress-multiple').html(string_progress + parseInt(data.loaded / data.total * 100, 10) + '%');
		},
		done: function (e, data)
		{
			/*console.log(data.result);*/
			if(data.result.success == 'false') {
				alert(data.result.error);
				$('#" . $this->upload_field . "_upload-button-svc').show('fast');
				$('#ajax-loader-file').css('display','none');
				$('#" . $this->upload_field . "_progress-multiple').css('display','none');
				$('#" . $this->upload_field . "_progress-multiple').html('');
				return;
			}
			$('#" . $this->upload_field . "_multiple_select').append('<option value=\"'+data.result.file_name+'\" selected=\"selected\">'+data.result.file_name+'</select>');
			var is_image = (data.result.file_name.substr(-4) == '.jpg'
				|| data.result.file_name.substr(-4) == '.png'
				|| data.result.file_name.substr(-5) == '.jpeg'
				|| data.result.file_name.substr(-4) == '.gif'
				|| data.result.file_name.substr(-5) == '.tiff')
				? true : false;
				var html;
				if(is_image==true)
				{
					html='<div id=\"'+data.result.file_name+'\" ><a href=\"" . base_url() . $this->path_to_directory . "'+data.result.file_name+'\" class=\"image-thumbnail\" id=\"fancy_'+data.result.file_name+'\">';
					html+='<img src=\"" . base_url() . $this->path_to_directory . "'+data.result.file_name+'\" height=\"50\"/>';
					html+='</a><br><a href=\"javascript:;\" onclick=\"delete_" . $this->upload_field . "_svc($(this),\''+data.result.file_name+'\')\" style=\"color:red;\" >Delete</a></div>';
					$('#" . $this->upload_field . "_list_svc').append(html);
					$('.image-thumbnail').fancybox({
						'transitionIn' : 'elastic',
						'transitionOut' : 'elastic',
						'speedIn' : 600,
						'speedOut' : 200,
						'overlayShow' : true
					});
				}
				else
				{
					html = '<div id=\"'+data.result.file_name+'\" ><span>'+data.result.file_name+'</span> <br><a href=\"javascript:\" onclick=\"delete_" . $this->upload_field . "_svc($(this),\''+data.result.file_name+'\')\" style=\"color:red;\" >Delete</a></div>';
					$('#" . $this->upload_field . "_list_svc').append(html);
				}
					$('#" . $this->upload_field . "_upload-button-svc').show('fast');
					$('#ajax-loader-file').css('display','none');
					$('#" . $this->upload_field . "_progress-multiple').css('display','none');
					$('#" . $this->upload_field . "_progress-multiple').html('');
				}
			});

		});
";

				
		$js = "<script>\n".$js."\n</script>";
	
		return $js;
	}

	 /*
	 * _create_unique_filename
	 * @param	string
	 * @access	protected
	 * @return      string
	 */
	protected function _create_unique_filename($filename)
	{
		return sprintf(
    				"%s_%s.%s",
    				pathinfo($filename, PATHINFO_FILENAME),
    				date('YmdHis'),
    				pathinfo($filename, PATHINFO_EXTENSION)
			);
	}
	
	 /*
	 * upload_file
	 * @access	public
	 * @return      json string
	 */
	function multiupload_file($state=null)
	{

		$json                    = array();
		$config['upload_path']   = $this->path_to_directory;
		$config['allowed_types'] = $this->allowed_types;
		$config['remove_spaces'] = TRUE;
		$config['max_filename']  = 0;
		$json['error']   = 'Failed';
		$json['success'] = 'false';

		if(array_key_exists($this->upload_field . '_new_multi_upload',$_FILES))
		{
			$config['file_name'] = $this->_create_unique_filename(
					$_FILES[$this->upload_field . '_new_multi_upload']['name']
			);
		}

		/* Mad issue with CI */
		/* http://stackoverflow.com/questions/8664758/the-upload-path-does-not-appear-to-be-valid-codeigniter-file-upload-not-worki */
		$this->ci->load->library('upload');
		$this->ci->upload->initialize($config);

		if (!$this->ci->upload->do_upload($this->upload_field . '_new_multi_upload'))
		{

		  $error = preg_replace("/<p[^>]*?>|<\/p>/", "", $this->ci->upload->display_errors());
		  $json['error'] =  $error. " Allowed file types are " . $this->allowed_types ;
		  $json['success'] = 'false';
		}
		else
		{
		  $uploade_data = $this->ci->upload->data();
		  $json['success'] = 'true';
		  $json['file_name'] = $uploade_data['file_name'];
		  unset($json['error']);
		}

		echo json_encode($json);
	}


	 /*
	 * view_upload_field callback
	 * @access	public
	 * @return      string
	 */
	function view_upload_field($value, $primary_key)
	{

		$args = func_get_args();
		$name = $args[2]->name;


		$this->_reset( $name );

		$any_file = 0;


		$result = $this->ci->db->get_where($this->file_table,array($this->primary_key => $primary_key));
		$result = $result->result_array();
				
		if(!empty($result))
		{
			$files = unserialize($result[0][$this->upload_field]);
		}else
		{
			return $this->no_file_text;
		}
		

		$html = '<select name="' . $this->upload_field . '_files[]" multiple="multiple" size="8" class="multiselect" id="' . $this->upload_field . '_multiple_select" style="display:none;">';
		if (!empty($files))
		{
			foreach ($files as $items)
			{
				$html.="<option value=" . $items . " selected='selected'>" . $items . "</option>";
			}
		}
		$html.='</select>';
		$html.='<div id="' . $this->upload_field . '_list_svc" class="mutiupload_list" style="margin-top: 40px;">';
		if (!empty($files))
		{

			$html .= "<table>";

			foreach ($files as $items)
			{
			
			$thisfile = base_url() . $this->path_to_directory . $items;

			if($this->remote_file_exists($thisfile))
			{
				$html .="<tr>";	$any_file = 1;			

				if ($this->_is_image($items) === true)
				{
					$html.= '<td><div id="' . $items . '">';
					$html.= '<a href="' . $thisfile . '" class="image-thumbnail" id="fancy_' . $items . '">';

					$html.='<img src="' .$thisfile. '" height="50"/>';
					$html.='</a>';
					$html.='</div></td>';
				}
				else
				{
					$html.='<td><div id="' . $items . '" >

					<span>' . $items . '</span>
					</div></td>';
				}

				if( strpos ($items,"." ) !== false )
				{
				      if(
						(
						  $this->enable_download_button && 
						  is_null($this->download_allowed)
						) ||
						(
						   $this->enable_download_button && 
						   in_array(
						        pathinfo(
								$thisfile, PATHINFO_EXTENSION
								),
							 explode(
								"|",$this->download_allowed
								)
							   )
						)
					)
					{
						$html.='<td><a href="'.$thisfile.'">Download</a></td>';
					}
				}

				$html .= "</tr>";

			   }

			}
				$html .= "</table>";
		}
		$html.='</div>';
		
		if(!$any_file){ $html = $this->no_file_text; }

		return $html;
	}


	
	 /*
	 * _file_url callback
	 * @access	public
	 * @return      string
	 */
	function _file_url($value,$row)
	{ 

		$args = func_get_args();
		$name = $args[2]->name;


		$this->_reset( $name );

		$files = unserialize($value);

		$any_file = 0;

		if(!empty($files))
		{
			$html = "";
			foreach($files as $items)
			{

				// No extension don't consider
				if (  strpos ($items,"." ) === false )
				{
					continue;
				}

				$thisfile = base_url() . $this->path_to_directory . $items;
				
				if($this->remote_file_exists($thisfile))
				{
				   $any_file = 1;
				   $html .= strlen($html)? " " : "";
				   $html .= "<span><a href='";
					
				      if  (  	(
						  $this->enable_download_button && 
						  is_null($this->download_allowed)
						) ||
						(
						   $this->enable_download_button && 
						   in_array(
						        pathinfo(
								$thisfile, PATHINFO_EXTENSION
								),
							 explode(
								"|",$this->download_allowed
								)
							   )
						)
					       
					   ){ $html .= $thisfile; }else{ $html .= "#"; } 
					
				   $html .="'>".( $this->enable_full_path ? $thisfile : $items ) ."</a></span><br>";
				}		
			}
			
		}
			if(!$any_file){ $html = $this->no_file_text; }

			return $html;
	}


	 /*
	 * _remove_files 
	 * @access	protected
         * @param	string
         * @param	array
	 * @return      void
	 */
	protected function _remove_files($path=null, $files=array())
	{	
	   if(!is_null($path) && !empty($files))
	   {
	      foreach($files as $file_name)
	      {
		if (file_exists($path . $file_name))
		{
			unlink($path . $file_name);
		}
              }
	   }	
	}
	
	 /*
	 * _set_files callback
	 * @access	public
         * @param	array
         * @param	int
	 * @return      array
	 */
	function _set_files($post_array, $primary_key=null)
	{
		
	   foreach(array_keys($this->hash) as $key)
	   {		
	     $this->upload_field = $key;

	     $this->_reset($key);

	     $files_to_delete = array();

	
	     $result = $this->ci->db->get_where($this->file_table,array($this->primary_key => $primary_key));
	     $result = $result->result_array();
				
	     if(!empty($result))
             {
			$files_exists = unserialize($result[0][$this->upload_field]);
	     }else
	     {
			$files_exists = array();
	     }
	
	     if(array_key_exists($this->upload_field.'_files',$post_array))
	     {	

		$files = $post_array[$this->upload_field . '_files'];	
		unset($post_array[$this->upload_field . '_files']);
		
		foreach($files_exists as $file_name)
		{
			if(!in_array($file_name, $files))
			{
				array_push($files_to_delete,$file_name);
			}
		}
		
		if (!empty($files))
		{	
			$post_array[$this->upload_field]   = serialize($files);	
		}
             }else
	     {
		$files_to_delete = $files_exists;
		$post_array[$this->upload_field]   = serialize(array());	
	     }

		$this->_remove_files($this->path_to_directory, $files_to_delete);
	   }
		
		return $post_array;
	}

	 /*
	 * _delete_this_key callback
	 * @access	public
         * @param	int
	 * @return      void
	 */
	function _delete_this_key($primary_key)
	{
	   foreach(array_keys($this->hash) as $key)
	   {		

		$this->_reset($key);
		
		$result = $this->ci->db->get_where($this->file_table,array($this->primary_key => $primary_key));
	        $result = $result->result_array();
				
	        if(!empty($result))
                {
			$files = unserialize($result[0][$key]);
	        }else
	        {
			$files = array();
	        }
		
		$this->_remove_files($this->path_to_directory, $files);
	    }	
	}

	 /*
	 * delete_file
	 * @access	public
         * @param	post array
	 * @return      json string
	 */
	function delete_file($state=null)
	{

		$state     = $this->ci->input->post("state");
		$field     = $this->ci->input->post("field");
		$file_name = $this->ci->input->post("file_name");

		// Just send msg success will delete files inside _set_files, if state is
		// other than add
		// only After update 
		if($state == "add")
		{
			$this->_reset($field);
			$this->_remove_files($this->path_to_directory,array($file_name));	
		}
		echo json_encode(array('success' => true));
		die();
		
	}


}
/* END OF Grocery_CRUD_Multiuploader */














