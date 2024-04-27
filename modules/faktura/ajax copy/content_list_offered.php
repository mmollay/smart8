<?php
include ("../../ssi_smart/smart_form/include_list.php");

$array = call_list ( '../list/earnings.php', '../config.inc.php',array('document'=>'ang')  );
echo $array['html'];
echo $array['js'];
echo "
<script>
var company_id  = '{$_SESSION['faktura_company_id']}';
var add_bill    = '{$_GET['add_bill']}';
</script>
<script type=\"text/javascript\" src=\"js/list_bill.js\"></script>
<script type=\"text/javascript\" src=\"js/form_bill.js\"></script>
";