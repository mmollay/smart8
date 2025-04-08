<?php


if (! is_array ( $array_icon )) {
    $array_icon = array('checkmark','heart','home','empty heart','idea','rocket','alarm','idea','star','announcement','smile','smile outline','grin outline','grin wink outline','grin tongue squint outline','thumbs up','handshake','plug','space shuttle','power','play','compass','compass outline','add user','map signs','sign in','mail','mail outline','mail square','copy','hand pointer','in cart','money','dove','car side','thumbtack','gift','comment','fruit-apple','snowman','award','theater masks','music','radiation','spa','carrot','fruit-apple','leaf','lemon','lemon outline','pepper hot','utensils',
        'accessible','address book','alarm','ambulance','archive','arrow alternate circle down','arrow alternate circle left','arrow alternate circle right','arrow alternate circle up','arrow circle down','arrow circle left','arrow circle right','arrow circle up','arrow down','arrow left','arrow right','arrow up','at','bell','bell slash','bicycle','binoculars','birthday cake','book','bookmark','box','briefcase','building','calendar','camera','car','caret down','caret left','caret right','caret up','chart bar','chart pie','chat','check','circle','clipboard','clock','cloud','code','cog','comment','comments','compass','computer','credit card','cube','cut','database','desktop','diamond','download','edit','ellipsis horizontal','ellipsis vertical','envelope','envelope open','exclamation','exclamation circle','eye','eye slash','female','file','film','filter','flag','folder','folder open','fork','gamepad','gem','gift','globe','graduation cap','grid layout','hammer','hand point down','hand point left','hand point right','hand point up','hdd','heart','home','hourglass','id badge','image','inbox','info','info circle','key','keyboard','leaf','life ring','lightbulb','linkify','list','lock','magic','magnet','male','map marker','medal','medkit','microphone','minus','mobile','money bill alternate','moon','music','newspaper','paper plane','pause','paw','pencil','phone','plane','plus','power off','print','puzzle piece','qrcode','question','quote left','quote right','random','recycle','refresh','reply','reply all','road','rocket','rss','save','search','server','share','shield alternate','shopping bag','shopping cart','sign in','sign out','signal','sitemap','sliders horizontal','smile','snowflake','sort','space shuttle','spinner','star','stop','street view','suitcase','sun','sync','table','tag','tags','tasks','taxi','terminal','thumbtack','thumbs down','thumbs up','ticket alternate','times','times circle','toggle off','toggle on','tools','train','trash alternate','tree','trophy','truck','tv','unlock','upload','user','video','volume down','volume up','wrench','yen sign','zoom in','zoom out');
    
}

foreach ( $array_icon as $icon ) {
	$js_icon = preg_replace ( '/ /', '+', $icon );
	$data_html .= "<a onclick=get_icon('$id','$js_icon')><i class='icon $icon'></i></a>"; // $('#$id').val('$js_icon').change();
}

$data_html .= "<hr><a href='http://fomantic-ui.com/elements/icon.html' target='icon'>mehr...</a>";

if (!$value) {
    $button_value = 'search';
}
else {
    $button_value = '';
}

$type_field = "
<div class='ui fluid action input'>
<input class='$form_id $class_input' placeholder='icon'  type='text' name ='$id' id='$id' value='$value'><div class='$form_size ui icon button tooltip-click' data-html=\"$data_html\" data-position='bottom right'><i id='button_icon_$id' class='$button_value $value icon'></i></div>
</div>";