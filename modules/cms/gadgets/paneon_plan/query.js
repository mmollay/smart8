//$('.ui-checkbox').change(function(){ alert('test'); });

//Show or hide the race of the dogs
$('input[type=radio][name=animal_race]').change(function() {
    if (this.value == 'cat') {
        $('#row_animal_dog_race').hide();
    }
    else if (this.value == 'dog') {
     	$('#row_animal_dog_race').show();
    }
});