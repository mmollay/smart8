<?php
session_start ();
$user_id = $_SESSION ['user_id'];
$page_id = $_SESSION ['smart_page_id'];
$company = $_SESSION['company'];

if ($_SERVER ['HTTP_HOST'] == 'localhost')
	$root = dirname ( dirname ( dirname ( dirname ( dirname ( __DIR__ ) ) ) ) ) ;
	elseif ($_SERVER ['HTTP_HOST'] == 'develop.ssi.at')
	$root = "/var/www/develop";
	else
		$root = "/var/www/ssi";
		
		$file = "/smart_users/$company/user$user_id/explorer/$page_id";
		
		
		// CONFIG / https://www.files.gallery/docs/config/
		// Uncomment the parameters you want to edit.
		return array ('root' => $root . $file,
				// 'start_path' => false,
				// 'username' => '',
				// 'password' => '',
				// 'load_images' => true,
				// 'load_files_proxy_php' => false,
				// 'load_images_max_filesize' => 1000000,
				// 'image_resize_enabled' => true,
				// 'image_resize_cache' => true,
				// 'image_resize_dimensions' => 320,
				// 'image_resize_dimensions_retina' => 480,
				// 'image_resize_dimensions_allowed' => '',
				// 'image_resize_types' => 'jpeg, png, gif, webp, bmp, avif',
				// 'image_resize_quality' => 85,
				// 'image_resize_function' => 'imagecopyresampled',
				// 'image_resize_sharpen' => true,
				// 'image_resize_memory_limit' => 128,
				// 'image_resize_max_pixels' => 30000000,
				// 'image_resize_min_ratio' => 1.5,
				// 'image_resize_cache_direct' => false,
				// 'folder_preview_image' => true,
				// 'folder_preview_default' => '_filespreview.jpg',
				// 'menu_enabled' => true,
				'menu_show' => false,
				// 'menu_max_depth' => 5,
				// 'menu_sort' => 'name_asc',
				// 'menu_cache_validate' => true,
				'menu_load_all' => true,
				// 'menu_recursive_symlinks' => true,
				// 'layout' => 'rows',
				// 'sort' => 'name_asc',
				// 'sort_dirs_first' => true,
				// 'sort_function' => 'locale',
				// 'cache' => true,
				// 'cache_key' => 0,
				// 'storage_path' => '_files',
				'files_exclude' => '', // /\.(gif|jpe?g|png|pdf|mp3|zip|ico|mp4|ico)$/i
				'dirs_exclude' => '/\\/thumbnail|autoresize|screenshots/i',
				// 'allow_symlinks' => true,
				// 'title' => '%name% [%count%]',
				// 'history' => true,
				// 'transitions' => true,
				'click' => 'menu','click_window' => 'pdf, html','click_window_popup' => true,
				// 'code_max_load' => 100000,
				// 'topbar_sticky' => 'scroll',
				'check_updates' => true,'allow_tasks' => true,
				// 'get_mime_type' => false,
				// 'context_menu' => true,
				// 'prevent_right_click' => false,
				'license_key' => 'F1-7325-BDL0-JFXI-21Q9-27WH-3HSJ',
				// 'filter_live' => true,
				// 'filter_props' => 'name, filetype, mime, features, title',
				// 'download_dir' => 'browser',
				// 'download_dir_cache' => 'dir',
				// 'assets' => '',
				'allow_upload' => true,'allow_delete' => true,'allow_rename' => true,'allow_new_folder' => true,'allow_new_file' => true,'allow_duplicate' => true,'allow_text_edit' => true // 'demo_mode' => false,
				// 'upload_allowed_file_types' => '',
				// 'upload_max_filesize' => 0,
				// 'upload_exists' => 'increment',
				// 'popup_video' => true,
				// 'video_thumbs' => true,
				// 'video_ffmpeg_path' => 'ffmpeg',
				// 'lang_default' => 'en',
				// 'lang_auto' => true,
		);