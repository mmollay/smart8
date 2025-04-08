<?php
session_start();
$user_id = $_SESSION['user_id'];
$page_id = $_SESSION['smart_page_id'];
$company = $_SESSION['company'];

if ($_SERVER['HTTP_HOST'] == 'localhost')
  $root = "/Applications/XAMPP/xamppfiles/htdocs";
elseif ($_SERVER['HTTP_HOST'] == 'develop.ssi.at')
  $root = "/var/www/develop";
else
  $root = "/var/www/ssi";

$file = "/smart_users/$company/user$user_id/explorer/$page_id";

// CONFIG / https://www.files.gallery/docs/config/
// Uncomment the parameters you want to edit.
return [
  'root' => $root . $file,
  'root_url_path' => NULL,
  //'start_path' => false,
  //'username' => '',
  //'password' => '',
  //'load_images' => true,
  //'load_files_proxy_php' => false,
  //'load_images_max_filesize' => 1000000,
  //'image_resize_enabled' => true,
  //'image_resize_cache' => true,
  //'image_resize_dimensions' => 320,
  //'image_resize_dimensions_retina' => 480,
  //'image_resize_dimensions_allowed' => '',
  //'image_resize_types' => 'jpeg, png, gif, webp, bmp, avif',
  //'image_resize_quality' => 85,
  //'image_resize_function' => 'imagecopyresampled',
  //'image_resize_sharpen' => true,
  //'image_resize_memory_limit' => 256,
  //'image_resize_max_pixels' => 60000000,
  //'image_resize_min_ratio' => 1.5,
  //'image_resize_cache_direct' => false,
  //'folder_preview_image' => true,
  //'folder_preview_default' => '_filespreview.jpg',
  //'menu_enabled' => true,
  //'menu_max_depth' => 5,
  //'menu_sort' => 'name_asc',
  //'menu_cache_validate' => true,
  'menu_load_all' => true,
  //'menu_recursive_symlinks' => true,
  //'layout' => 'rows',
  //'cache' => true,
  //'cache_key' =>743833204,
  //'storage_path' => '_files',
  //'files_include' => '',
  //'files_exclude' => '',
  //'dirs_include' => '',
  'dirs_exclude' => '/\\/autoresize|screenshots|thumb_gallery|thumbnail/i',
  //'allow_symlinks' => true,
  //'get_mime_type' => false,
  'license_key' => 'F1-7325-BDL0-JFXI-21Q9-27WH-3HSJ',
  //'download_dir' => 'browser',
  //'download_dir_cache' => 'dir',
  //'assets' => '',
  //'allow_all' => false,
  'allow_upload' => true,
  'allow_delete' => true,
  'allow_rename' => true,
  'allow_new_folder' => true,
  'allow_new_file' => true,
  'allow_duplicate' => true,
  'allow_text_edit' => true,
  //'allow_zip' => false,
  //'allow_unzip' => false,
  'allow_move' => true,
  //'allow_copy' => false,
  //'allow_download' => true,
  //'allow_mass_download' => false,
  //'allow_mass_copy_links' => false,
  //'allow_settings' => false,
  //'allow_check_updates' => false,
  //'allow_tests' => true,
  'allow_tasks' => true,
  //'demo_mode' => false,
  //'upload_allowed_file_types' => '',
  //'upload_max_filesize' => 0,
  //'upload_exists' => 'increment',
  //'video_thumbs' => true,
  //'video_ffmpeg_path' => 'ffmpeg',
  'pdf_thumbs' => true,
  //'imagemagick_path' => 'magick',
  //'use_google_docs_viewer' => false,
  //'lang_default' => 'en',
  //'lang_auto' => true,
  //'index_cache' => false,
];