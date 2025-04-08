//This will be included inner generated page 

$list_id = 'faq_list';
$faq_id = $_GET ['id'];
session_start();
if (! empty ( $_GET )) {
        unset ( $_SESSION ['search'] );
        unset ( $_SESSION ['filter'] );
}
if ($_GET ['search'])
        $_SESSION ['input_search'] [$list_id] = $_GET ['search'];
foreach ( $_GET as $key => $value ) {
		if ($key != 'id')
        $_SESSION ["filter"] [$list_id] [$key] = $value;
} 

if ($faq_id) {
	$set_container = '#left_0';
	
	//set tynamic - metatags
	require_once ('gadgets/config.php');
	$query2 = $GLOBALS ['mysqli']->query ( "SELECT * FROM ssi_paneon.faq LEFT JOIN ssi_paneon.faq2tag ON faq.faq_id = faq2tag.faq_id WHERE faq.faq_id = '$faq_id' " );
	$array = mysqli_fetch_array ( $query2 );
		
	$dynamic_meta = "
	<meta property='og:title' content='{$array ['title']}' />
	<meta property='og:description' content='{$array ['problem']}' />
	<meta property='og:image' content='{$array ['image']}'/>
	";
	
	$dynamic_title = $array ['title']; 

}
else 
	$set_container = '#faq';
