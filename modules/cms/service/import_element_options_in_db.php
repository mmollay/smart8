<?php
include ("../../login/config_main.inc.php");

echo "Element Option import<hr>";

$sql = $GLOBALS['mysqli']->query("SELECT * from smart_layer") or die(mysqli_error($GLOBALS['mysqli']));
while ($array = mysqli_fetch_array($sql)) {
    $format = $array['format'];
    $layer_id = $array['layer_id'];
    // $from_id = $array['from_id'];
    $gadget = $array['gadget'];
    $layer_fixed = $array['layer_fixed'];

    $gadget_array = $gadget_array2 = $array['gadget_array'];

    $gadget_array_n = explode("|", $gadget_array);
    if ($array['gadget_array']) {
        foreach ($gadget_array_n as $array) {
            $array2 = preg_split("[=]", $array, 2);
            $id = $array2[0];
            $value = $array2[1];

            if ($value and $id) {
                echo "$layer_id:   {$array2[0]} = {$array2[1]} <br>";
                save_smart_element_option($layer_id, array(
                    $id => $value
                ));
            }
        }

        // echo $layer_id."->".$gadget_array . "<br>";
    }
}

