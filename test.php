<?php

require_once "UnitConverter.php";

// Create a new instance of the converter, override default decimal point and thousand
// separator characters
$unit = new UnitConverter('.', ',');

// Weights
$unit->addConversion('kg', array('pound' => 2.20));

// Distance
$unit->addConversion('km', array('meter'         => 1000, 'dmeter' => 10000,
								'centimeter/cm' => 100000, 'millimeter' => 1000000,
								'mile'          => 0.62137, 'naut.mile' => 0.53996, 'inch/inches/zoll' => 39370,
								'ft/foot/feet'  => 3280.8, 'yd/yard' => 1093.6
	)
);

// Temperature
$unit->addConversion('Centigrade', array(
		'Fahrenheit' => array('ratio' => 1.8, 'offset' => 32),
		'Kelvin'     => array('ratio' => 1, 'offset' => 273),
		'Reaumur'    => 0.8)
);

$unit->addConversion('l', array(
		'ml' => 1000
	)
);

$centigrade = 20;

print '<h4><font color="green">UnitConvertor valid direct tests</font></h4>';
print('20 km is ' . $unit->convert(20, 'km', 'mile', 2) . ' mile');
print('<br><br>1 km is ' . $unit->convert(1, 'km', 'meter', 1) . ' m');
print('<br><br>12.5 C is ' . $unit->convert(12.5, 'Centigrade', 'Fahrenheit', 1) . ' Fahrenheit');
print('<br><br>10 C is ' . $unit->convert(10, 'Centigrade', 'Kelvin', 1) . ' Kelvin');
print('<br><br>10 C is ' . $unit->convert(10, 'Centigrade', 'Reaumur', 1) . ' Reaumur');

print '</pre><h4><font color="green">UnitConvertor valid reverse tests</font></h4>';

print('20 miles is ' . $unit->convert(20, 'mile', 'km', 2) . ' km');
print('<br><br>800 meter is ' . $unit->convert(800, 'meter', 'km', 3) . ' km');
print('<br><br>30,2 F is ' . $unit->convert(30.2, 'Fahrenheit', 'Centigrade', 1) . ' Centigrade');
print('<br><br>10 K is ' . $unit->convert(10, 'Kelvin', 'Centigrade', 1) . ' Centigrade');
print('<br><br>10 R is ' . $unit->convert(10, 'Reaumur', 'Centigrade', 1) . ' Centigrade');

print '</pre><h4><font color="green">UnitConvertor valid indirect tests</font></h4>';

print $centigrade . 'C is ' . $unit->convert($centigrade, 'Centigrade', 'Centigrade', 2) . ' Centigrade';
print("<br><br> 50F is " . $unit->convert(50, 'Fahrenheit', 'Kelvin', 2) . ' Kelvin');
print("<br><br>" . '10 Kelvin is ' . $unit->convert(10, 'Kelvin', 'Fahrenheit', 2) . ' Fahrenheit');
print("<br><br>" . '10 Reaumur is ' . $unit->convert(10, 'Reaumur', 'Fahrenheit', 2) . ' Fahrenheit');
print("<br><br>" . '10 Reaumur is ' . $unit->convert(10, 'Reaumur', 'Centigrade', 2) . ' Centigrade');
print("<br><br>" . '8 C is ' . $unit->convert(8, 'Centigrade', 'Fahrenheit', 2) . ' Fahrenheit');
print('<br><br>32 F is ' . $unit->convert(32, 'Fahrenheit', 'Kelvin', 1) . ' Kelvin');
print('<br><br>273 K is ' . $unit->convert(273, 'Kelvin', 'Fahrenheit', 1) . ' Fahrenheit');
print "<hr />";

print '</pre><h4><font color="green">UnitConvertor invalid illogical indirect tests</font></h4>';
print('32 F is ' . $unit->convert(32, 'Fahrenheit', 'km', 1) . 'km');

print '</pre><h4><font color="green">UnitConvertor Volume</font></h4>';
print('1 l is ' . $unit->convert(1, 'l', 'ml', 1) . 'Milliliter');

print('<br><br>100000 ml is ' . $unit->convert(100000, 'ml', 'l', 1) . 'liter');
