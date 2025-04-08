<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Form-Generator (develop)</title>
<link rel="stylesheet" href="../jquery/jquery-ui.css">
<link rel="stylesheet" href="../semantic/dist/semantic.min.css">
<link rel="stylesheet" href="style.css">

<script src="../jquery/jquery.min.js"></script>
<script src="../jquery/jquery-ui.min.js"></script>
<script src="../semantic/dist/semantic.min.js"></script>
<script src="js/jquery-quickedit.js"></script>
<script src="js/generator.js"></script>
</head>
<body>
<div style='position:absolute; top:0px; left:0px;'><a href="https://github.com/mmollay/smart-form"><img decoding="async" width="149" height="149" src="https://github.blog/wp-content/uploads/2008/12/forkme_left_orange_ff7600.png?resize=149%2C149" class="attachment-full size-full" alt="Fork me on GitHub" loading="lazy" data-recalc-dims="1"></a></div>
			
	<div class="ui fluid container" style="background-color: #EFE">
		<br>
		<div class="ui text container">
			<div class='ui center aligned icon header'>
				<i class='icon green circular wpforms'></i> Formular-Generator for Smart-Form
				<div class='label small ui red' align=center>Develop</div>
			</div>

			<div class='divider ui'></div>
			<div align=center>
				The finished generator will be available shortly!<br> Have fun
				trying.<br>
			</div>
			<div class='divider ui'></div>
			<div align=center><a href='../index.php'><i class='icon home'></i> Home</a></div>
		</div>
		<br>
	</div>
	<br>
	<div class="ui text container">
		<?php
		//define inner ajax/new_field.php
		$array_field ['input'] = 'Input';
		$array_field ['fielddate'] = 'Date';
		$array_field ['button'] = 'Button';
		$array_field ['dropdown'] = 'Dropdown';
		$array_field ['radio'] = 'Radio';
		$array_field ['checkbox'] = 'Checkbox';
		$array_field ['textarea'] = 'Textarea';
		$array_field ['splitter1'] = "Grid <i class='icon grid'></i>";

		$output_field = '';
		foreach ( $array_field as $id => $title ) {
			$output_field .= "<div  class='draggable item' title='Inputfeld hineinziehen' id='$id' >$title</div>";
		}
		?>
		<div align=center id='context_form'>
			<div class='ui sticky' id='sticky'>
				<div id='form_field' class='ui mini compact menu'><?=$output_field?></div>
			</div>

			<br>
			<div id='generator_form'></div>
		</div>
		<br>
		<button class='ui button red mini' id='set_default'>Load Default Values</button>
		</br>
	</div>
	<div align='center' style='position: relative; top: 14px;'>
		<i class="angle double big grey down icon"></i>
	</div>
	<div class="ui fluid container" style="background-color: #EEE">
		<br>
		<div class="ui text container">
			Code:
			<div class="ui fluid icon input">
				<textarea style='width: 100%; height: 200px; font-size: 14px;' id='generate_code'></textarea>
			</div>
		</div>
		<br> <br> <br>
		<div class='ui divider'></div>
		<div align=center>
			Powered by <a href='https://www.ssi.at' target='ssi'>SSI</a><br> <br>
		</div>
	</div>

	<div class='ui modal' id='edit_form'>
		<i class='close icon'></i>
		<div class='header'>Edit Field</div>
		<div class='content'></div>
	</div>

</body>
</html>