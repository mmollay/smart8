<?php
// mm@ssi.at am 16.07.2017
include (__DIR__ . '/../f_config.php');
$document = $_POST['document'];

$_SESSION['faktura_company_id'] = $company_id = $_POST['company_id'];
echo mysql_singleoutput("SELECT MAX(bill_number) as bill_number FROM bills WHERE DATE_FORMAT(date_create,'%Y') = '$year' AND document = '$document' AND company_id='$company_id' order by date_create", "bill_number") + 1;