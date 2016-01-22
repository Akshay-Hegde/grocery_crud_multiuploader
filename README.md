# Grocery_Crud_Multiuploader

### Version
1.0.2 ( update callback fix (28-07-2015 13:17:00), Renamed function upload_file to multiupload_file )

### Installation

If you have installed Codeigniter & Grocery crud already you just need to unzip grocery_crud_multiuploader and set permission to upload directory

```sh
$ unzip grocery_crud_multiuploader -d /<apache_root>/<CI>
```

### Example 

```sh
$ unzip grocery_crud_multiuploader -d /var/www/html/virtual/Development/Admin/
$ cd /var/www/html/virtual/Development/Admin/
$ chown -R apache assets/grocery_crud_multiuploader/GC_uploads/
$ firefox http://example.com/index.php/multiuploader/index
```

If you are using Codeigniter & Grocery crud first time, you may extract and use any of these files,
  - CI_220_GC_141.tar.gz           
          Codeigniter 2.2.0, Grocery Crud 1.4.1 preinstalled
          
  - CI_220_GC_150.tar.gz         
          Codeigniter 2.2.0, Grocery Crud 1.5.0 ( Latest ) preinstalled

```sh
$ tar -xvf CI_220_GC_150.tar.gz -C /var/www/html/virtual/Development/Admin/
$ cd /var/www/html/virtual/Development/Admin/CI_220_GC_150
$ chown -R apache assets/grocery_crud_multiuploader/GC_uploads/
$ firefox http://example.com/CI_220_GC_150/index.php/multiuploader/index
```


### Usage
```php 
$crud->new_multi_upload(arg1,arg2);
         arg1 - string, field_name
         arg2 - array, upload field settings
```

```php 
/* Uses default settings */
$crud->new_multi_upload("my_pictures");
```

```php 
/* Upload field configuration, path, allowed type etc */
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

		   /* show download button only for this types...*/
		   "download_allowed"        => 'jpg' 		
         );
$crud->new_multi_upload("my_pictures",$config);
```



#### Package Strucure with sample files & sql dump
```sh
grocery_crud_multiuploader
├── application
│   ├── controllers
│   │   └── multiuploader.php
│   ├── libraries
│   │   └── Grocery_CRUD_Multiuploader.php
│   └── views
│       └── crud.php
├── assets
│   └── grocery_crud_multiuploader
│       ├── GC_uploads
│       │   ├── files
│       │   │   ├── grocerycrud_API_and_Functions_list_20141221204352.pdf
│       │   │   └── stock_20141221204418.pdf
│       │   ├── index.html
│       │   ├── mail
│       │   │   ├── input_20141221204215.txt
│       │   │   └── output_20141221204206.txt
│       │   └── pictures
│       │       ├── 10858515_376256832544074_5836979769187221254_n_20141221204057.jpg
│       │       ├── 1780131_679628865422197_963985172_o_20141221205146.jpg
│       │       ├── Goa-Beach-Tour-HD-Wallpaper_20141221204327.jpeg
│       ├── scripts
│       │   └── jquery.mousewheel.js
│       └── styles
│           └── multi_uploader.css
└── multi_uploader.sql

12 directories, 16 files
```


#### Add record / Edit record
![Add Field][1]

#### List state
![list][2]

#### List state when no files, field value
![no file text][3]

#### SQL Structure
![sql][4]

[1]:https://github.com/Akshay-Hegde/grocery_crud_multiuploader/blob/master/screenshots/multi_1.png
[2]:https://github.com/Akshay-Hegde/grocery_crud_multiuploader/blob/master/screenshots/multi_2.png
[3]:https://github.com/Akshay-Hegde/grocery_crud_multiuploader/blob/master/screenshots/multi_3.png
[4]:https://github.com/Akshay-Hegde/grocery_crud_multiuploader/blob/master/screenshots/multi_4.png

Author  
----
     Akshay Hegde
     akshay.k.hegde@gmail.com
     https://www.linkedin.com/profile/view?id=206267783


License
----
	https://github.com/scoumbourdis/grocery-crud/blob/master/license-grocery-crud.txt
	
Copyright
----
    Copyright (c) 2010 through 2014, John Skoumbourdis

Many Thanks
----
    John Skoumbourdis, Amit Shah & Victor


