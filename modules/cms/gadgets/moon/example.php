<?php

//
// File: moonphase.php
//     Sample code for using moonphase.inc.php
//	http://www.sentry.net/~obsid/moonphase
//

require( 'MoonPhase.php' );

// phasehunt() Example
print "Example: phasehunt()\n";
do_phasehunt();
print "\n\n";


// phaselist() Example
print "Example: phaselist()\n";
$start = strtotime( "2008-10-01 00:00:00 PST" );
$stop = strtotime( "2008-10-31 00:00:00 PST" );
do_phaselist( $start, $stop );
print "\n\n";


// phase() Example
$date = "2008-10-31";
$time = "00:00:00";
$tzone = "PST";
print "Example: phase() ($date $time $tzone)\n";
do_phase( $date, $time, $tzone );
print "\n\n";



// phasehunt() Example
function do_phasehunt()  {
	$phases = array();
	$phases = phasehunt();
	print date("D M j G:i:s T Y", $phases[0]) . "\n";
	print date("D M j G:i:s T Y", $phases[1]) . "\n";
	print date("D M j G:i:s T Y", $phases[2]) . "\n";
	print date("D M j G:i:s T Y", $phases[3]) . "\n";
	print date("D M j G:i:s T Y", $phases[4]) . "\n";
}



// phaselist() Example
function do_phaselist( $start, $stop )  {
	$name = array ( "New Moon", "First quarter", "Full moon", "Last quarter" );
	$times = phaselist( $start, $stop );

	foreach ( $times as $time )  {

		// First element is the starting phase (see $name).
		if ( $time == $times[0] )  {
			print $name["$times[0]"] . "\n";
		}
		else  {
			print date("D M j G:i:s T Y", $time) . "\n";
		}
	}
}



// phase() Example
function do_phase ( $date, $time, $tzone )  {
	$moondata = phase(strtotime($date . ' ' . $time . ' ' . $tzone));

	$MoonPhase	= $moondata[0];
	$MoonIllum	= $moondata[1];
	$MoonAge	= $moondata[2];
	$MoonDist	= $moondata[3];
	$MoonAng	= $moondata[4];
	$SunDist	= $moondata[5];
	$SunAng		= $moondata[6];

	$phase = 'Waxing';
	if ( $MoonAge > SYNMONTH/2 )  {
		$phase = 'Waning';
	}

	// Convert $MoonIllum to percent and round to whole percent.
	$MoonIllum = round( $MoonIllum, 2 );
	$MoonIllum *= 100;
	if ( $MoonIllum == 0 )  {
		$phase = "New Moon";
	}
	if ( $MoonIllum == 100 )  {
		$phase = "Full Moon";
	}

	print "Moon Phase: $phase\n";
	print "Percent Illuminated: $MoonIllum%\n";
}



?>
