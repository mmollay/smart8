<?php

// Pfad zur TXT-Datei
$file_path = 'survivaltips.txt';

// Datei einlesen und in ein Array speichern
$file_contents = file($file_path, FILE_IGNORE_NEW_LINES);

// Eine zufällige Zeilennummer auswählen
$random_line_number = rand(0, count($file_contents) - 1);

// Die zufällige Zeile aus dem Array auswählen
$random_line = $file_contents[$random_line_number];

// Ausgabe der zufälligen Zeile
$output =  $random_line;
