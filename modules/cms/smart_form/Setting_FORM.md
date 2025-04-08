-###Smart-Form v2.x

Configs

## Basic Usage - List

```php
<?php

// Data for Content
$arr ['value'] = array ('title' => 'Title','text' => 'This is a test' );
$arr ['value'] ['content'] = 'Max Muster';
$arr ['value'] ['age1'] = '10';

// Header
$arr ['header'] = array ('title' => "Formular",'text' => 'here you get it','class' => 'small red','segment_class' => 'attached message','icon' => 'newspaper red' );
$arr ['footer'] = array ('text' => "Have a nice time",'segment_class' => 'attached message' );

// Config - Fields
$arr ['form'] = array ('id' => 'form_newsletter','action' => 'ajax/handler.php','class' => 'segment attached','width' => '800','align' => 'center');
$arr ['ajax'] = array ('success' => "$('#show_data').html(data);",'dataType' => 'html' ); //,'onLoad' => "alert('Hallo');" 

$arr ['field'] ['content'] = array ('type' => 'content','text' => "<b>This is:</b> {data}" );
$arr ['field'] ['drop'] = array ('type' => 'dropdown','label' => 'Drop','search' => true,'clearable' => true,'array' => array ('wood' => 'Wood','water' => 'Water' ) );

// Tabs --------------------------------------------
$arr ['tab'] = array ('tabs' => [ "first" => "First","second" => "Second" ],'active' => 'first' );

$arr ['field'] ['tab_input1'] = array ('tab' => 'first','type' => 'input','label' => 'First','placeholder' => 'First' );
$arr ['field'] ['age1'] = array ('tab' => 'first','type' => 'slider','label' => 'Statistic','step' => '2','max'=>'200', 'smooth' => true,'class' => 'labeled ticked','unit' => '%' );

$arr ['field'] ['tab_input2'] = array ('tab' => 'second','type' => 'input','label' => 'Second','placeholder' => 'Second' );
$arr ['field'] ['age2'] = array ('tab' => 'second','type' => 'slider','label' => 'Age','step' => '2','max' => 100,'smooth' => true,'class' => 'green' , 'value'=>'20', 'unit' => 'Years');

//$arr ['field'] ['ckeditor'] = array ('type' => 'ckeditor5','value' => 'This is <b>html</b><br><br>...and more','items' => "['bold','italic','alignment','link']" ); //,'autosave'=>'alert(editor.getData())'
$arr ['field'] ['ckeditor'] = array ('type' => 'ckeditor5','value' => 'This is <b>html</b><br><br>...and more' ); //,'autosave'=>'alert(editor.getData())'


// Accordion 2/1---------------------------------------
$arr ['field'] [] = array ('type' => 'accordion','title' => 'Input 1' );

$arr ['field'] [] = array ('type' => 'div','class' => 'fields equal width' );
$arr ['field'] ['date'] = array ('type' => 'calendar','label' => 'Date' );
$arr ['field'] ['firstname'] = array ('type' => 'input','label' => 'Firstname','placeholder' => 'Firstname' );
$arr ['field'] ['secondname'] = array ('type' => 'input','label' => 'Secondname','placeholder' => 'Secondname' );
$arr ['field'] [] = array ('type' => 'div_close' );

// Accordion 2/2---------------------------------------
$arr ['field'] [] = array ('type' => 'accordion','title' => 'Input 2','split' => true );
$arr ['field'] ['email'] = array ('type' => 'input','placeholder' => "Email",'label_right_class' => "button submit",'label_right' => 'Sumbit' );

// Accordion Close---------------------------------------
$arr ['field'] [] = array ('type' => 'accordion','close' => 'true' );

$arr ['field'] ['color'] = array ('type' => 'color','label' => 'Color', 'value'=>'red' );
$arr ['field'] ['icon'] = array ('type' => 'icon','label' => 'Icon' );


$arr ['field'] ['submit'] = array ('type' => 'button','value' => 'Submit','class' => 'submit','color' => 'green' );