<?php
include_once('hicri.php');
include_once('functions.php');
use hicritakvim\hicri;

//var_dump(hicri::hicriMonthDayCount('1422-04-30'));

//var_dump( hicri::hilalBul(parseDateStr( '1900-05-03' )) );
//var_dump( hicri::miladiHicri(parseDateStr( '2001-07-22' )) ); //2001-07-22	1 Cemaziyelevvel	1422	PAZAR
//var_dump( hicri::miladiHicri(parseDateStr( '1972-12-07' )) );
//var_dump( hicri::hicriMiladi(parseDateStr( '1422-04-30' )) ); //2001-07-22	1 Cemaziyelevvel	1422	PAZAR
var_dump( hicri::hicriMiladi(parseDateStr( '1403-05-05' )) );
//var_dump( hicri::hicriMiladi(parseDateStr( '1318-01-03' )) );
