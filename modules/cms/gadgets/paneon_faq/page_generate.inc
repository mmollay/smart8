<?php 
/* mm 12.10.2022
/*This Parameter will be used by page_generate*/
    
$add_php_parameter = file_get_contents ( '../../gadgets/paneon_faq/include4pg.php' );
$content = preg_replace ( "/: faq_id/", ': \'<?=$faq_id?>\'', $content );
$content = preg_replace ( "/\#faq/", '<?=$set_container?>', $content );
$metatag = '<title><?=$dynamic_title;?></title>';
$metatag .= '<?=$dynamic_meta;?>';