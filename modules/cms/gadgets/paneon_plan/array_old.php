<?php

$arr['field']['animal_race'] = array('type' => 'radio', 'label'=>'Rasse', 'array'=>array('cat'=>'Katze','dog'=>'Hund'), 'validate'=> true, 'value'=>'dog' );
$arr['field']['animal_name'] = array('type' => 'input','label'=>'Name', 'validate'=> true,'placeholder'=>'Max', 'value'=>'Max' );
$arr['field']['animal_age'] = array('type' => 'dropdown','label'=>'Alter', 'validate'=> true, 'min'=>'1', 'max'=>'30', 'step'=>'1', 'value'=>2,'unit'=>'Jahre','search'=>true,'info'=>'Alter des ist relativ egal');
$arr['field']['animal_weight'] = array('type' => 'dropdown','label'=>'Alter', 'validate'=> true, 'min'=>'1', 'max'=>'60', 'step'=>'1', 'value'=>10,'unit'=>'kg','search'=>true,'info'=>'Alter des ist relativ egal' );
$arr['field']['animal_weight_ideal'] = array('type' => 'dropdown','label'=>'Alter (ideal)', 'validate'=> true, 'min'=>'1', 'max'=>'60', 'step'=>'1', 'value'=>10,'unit'=>'Jahre','search'=>true,'info'=>'Alter des ist relativ egal' );

$array_title ['skin'] = 'Haut, Fell, Krallen';
$array_title ['face'] = 'Zahnfleisch, Karies, Geruch';
$array_title ['body'] = 'Gelenke, Sehnen, Bandscheiben';
$array_title ['organ'] = 'Nierenwerte, Leberwerte';
$array_title ['verdauung'] = 'Nierenwerte, Leberwerte';
$array_title ['immun'] = 'Organe degeneriert';
$array_title ['autoimmun'] = 'Nierenwerte, Leberwerte';
$array_title ['tumor'] = 'Tumore';

$array_level ['skin'] = 1;
$array_level ['face'] = 2;
$array_level ['body'] = 2;
$array_level ['organ'] = 5;
$array_level ['verdauung'] = 3;
$array_level ['immun'] = 4;
$array_level ['autoimmun'] = 5;
$array_level ['tumor'] = 6;

$array_result ['text'] ['1'] = 'Alles in Ordnung';
$array_result ['text'] ['2'] = 'Erste Anzeichen';
$array_result ['text'] ['3'] = 'Handlungsbedarf';
$array_result ['text'] ['4'] = 'Dringender Handlungsbedarf';
$array_result ['text'] ['5'] = 'Gefahr in Verzug!';
$array_result ['text'] ['6'] = 'Es droht der Tod!';

$array_result ['info'] ['1'] = 'Alles scheint perfekt zu sein!';
$array_result ['info'] ['2'] = 'Ihre Angaben deuten auf leichte Themen hin, übersehen Sie nichts! Wir empfehlen...';
$array_result ['info'] ['3'] = 'Die Auswertung hat ergeben, wir machen mit Ihnen ganz indidivduelle Beratung!';
$array_result ['info'] ['4'] = 'Er wird Zeit zu handeln, wählen Sie 00000 000000, wir finden mit Ihnen gemeinsam sofort eine Lösung!';
$array_result ['info'] ['5'] = 'Es ist höchste Zeit, den Angaben nach sollte sofort gehandelt werden!!!';
$array_result ['info'] ['6'] = 'Leider schaut es gar nicht gut aus!';

$array_result ['color'] ['1'] = 'green';
$array_result ['color'] ['2'] = 'olive';
$array_result ['color'] ['3'] = 'yellow';
$array_result ['color'] ['4'] = 'orange';
$array_result ['color'] ['5'] = 'red';
$array_result ['color'] ['6'] = 'red';

$array_result ['video'] ['1'] = '2WCuWFc9SNs';
$array_result ['video'] ['2'] = '2WCuWFc9SNs';
$array_result ['video'] ['3'] = 'xilwZsDzcB4';
$array_result ['video'] ['4'] = 'xilwZsDzcB4';
$array_result ['video'] ['5'] = 'xilwZsDzcB4';
$array_result ['video'] ['6'] = 'xilwZsDzcB4';

//https://fomantic-ui.com/elements/icon.html
$array_result ['icon'] ['1'] = 'heart';
$array_result ['icon'] ['2'] = 'spa';
$array_result ['icon'] ['3'] = 'exclamation';
$array_result ['icon'] ['4'] = 'bell';
$array_result ['icon'] ['5'] = 'exclamation triangle';
$array_result ['icon'] ['6'] = 'skull crossbones';

$array_value ['skin'] = array (
		'skin_1' => 'Haarausfall',
		'skin_2' => 'Haarballen',
		'skin_3' => 'Verfilzungen',
		'skin_4' => 'stumpfes Fell',
		'skin_5' => 'Geruch',
		'skin_6' => 'Allergie',
		'skin_7' => 'Juckreiz',
		'skin_8' => 'Hotspot',
		'skin_9' => 'Ekzem',
		'skin_10' => 'Parasiten'
);
$array_value ['face'] = array (
		'face_1' => 'Entzündungen (Zahnfleisch)',
		'face_2' => 'Entzündungen (Augen)',
		'face_3' => 'Entzündungen (Ohren)',
		'face_4' => 'Zahnstein',
		'face_5' => 'lockere Zähne',
		'face_6' => 'Geruch'
);
$array_value ['body'] = array (
		'body_1' => 'Gelenke',
		'body_2' => 'Bänder & Sehnen',
		'body_3' => 'Bandscheiben',
		'body_4' => 'Spondylose'
);
$array_value ['organ'] = array (
		'organ_1' => 'Organe',
		'organ_1' => 'Nieren',
		'organ_2' => 'Leber',
		'organ_3' => 'Bauchspeicheldrüse / Pankreas',
		'organ_4' => 'Schilddrüse',
		'organ_5' => 'Herz'
);
$array_value ['verdauung'] = array (
		'verdauung1' => 'Entzündungen (Darm)',
		'verdauung2' => 'Entzündungen (Bauchspeicheldrüse / Pankreas)',
		'verdauung4' => 'Allergien',
		'verdauung5' => 'Durchfall',
		'verdauung6' => 'Parasitenbefall (Giardien,Leishmanien,,Würmer'
);
$array_value ['immun'] = array (
		'immun_1' => 'Infektanfälligkeit',
		'immu2' => 'Parasitenbefall (Giardien, Leishmanien, Würmer)'
);
$array_value ['autoimmun'] = array (
		'autoimmun_1' => 'Schilddrüse (Unterfunktion, Übefunktion',
		'autoimmun_2' => 'Morbus',
		'autoimmun_3' => 'Pankreas',
		'autoimmun_4' => 'Diapetes',
		'autoimmun_5' => 'Epilepsie'
);
$array_value ['tumor'] = array (
		'tumor_1' => 'Lipome',
		'tumor_2' => 'Zysten',
		'tumor_3' => 'Fibrome',
		'tumor_4' => 'Krebs'
);