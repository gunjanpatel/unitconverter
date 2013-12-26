<?php
/**
 * @package    Unit.Converter
 *
 * @copyright  Copyright (C) 2005 - 2013 gunjanpatel. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * redSHOP Unit converter
 *
 * @package  Unit.Converter
 * @since    2.5
 */
class UnitConverter
{
	/**
	 * Stores conversion ratios.
	 *
	 * @var      array
	 * @access   private
	 */
	public $conversionTable = array();

	/**
	 * Decimal point character (default is "." - American - set in constructor).
	 *
	 * @var      string
	 * @access   private
	 */
	public $decimalPoint;

	/**
	 * Thousands separator (default is "," - American - set in constructor).
	 *
	 * @var      string
	 * @access   private
	 */
	public $thousandSeparator;

	/**
	 * For future use
	 *
	 * @var      array
	 * @access   private
	 */
	public $bases = array();

	/**
	 * Constructor. Initializes the UnitConvertor object with the most important
	 * properties.
	 *
	 * @param   string  $decimalPoint       Decimal point character
	 * @param   string  $thousandSeparator  Thousand separator character
	 *
	 * @access   public
	 */
	public function __construct($decimalPoint = '.', $thousandSeparator = ',')
	{
		$this->decimalPoint      = $decimalPoint;
		$this->thousandSeparator = $thousandSeparator;
	}

	/**
	 * Adds a conversion ratio to the conversion table.
	 *
	 * @param   string  $fromUnit  the name of unit from which to convert
	 * @param   array   $toArray   array(
	 *                              	"pound"=>array("ratio"=>'', "offset"=>'')
	 *                              )
	 *                              "pound" - name of unit to set conversion ration to
	 *                              "ratio" - 'double' conversion ratio which, when
	 *                              multiplied by the number of $fromUnit units produces
	 *                              the result
	 *                              "offset" - an offset from 0 which will be added to
	 *                              the result when converting (needed for temperature
	 *                              conversions and defaults to 0).
	 *
	 * @return   boolean   true if successful, false otherwise
	 *
	 * @access   public
	 */
	public function addConversion($fromUnit, $toArray)
	{
		if (!isset($this->conversionTable[$fromUnit]))
		{
			while (list($key, $val) = each($toArray))
			{
				if (strstr($key, '/'))
				{
					$toUnits = explode('/', $key);

					foreach ($toUnits as $toUnit)
					{
						$this->bases[$fromUnit][] = $toUnit;

						if (!is_array($val))
						{
							$this->conversionTable[$fromUnit . "_" . $toUnit] = array("ratio" => $val, "offset" => 0);
						}
						else
						{
							$this->conversionTable[$fromUnit . "_" . $toUnit] = array(
									"ratio"  => $val['ratio'],
									"offset" => (isset($val['offset']) ? $val['offset'] : 0)
								);
						}
					}
				}
				else
				{
					$this->bases[$fromUnit][] = $key;

					if (!is_array($val))
					{
						$this->conversionTable[$fromUnit . "_" . $key] = array("ratio" => $val, "offset" => 0);
					}
					else
					{
						$this->conversionTable[$fromUnit . "_" . $key] = array(
								"ratio"  => $val['ratio'],
								"offset" => (isset($val['offset']) ? $val['offset'] : 0)
							);
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Converts from one unit to another using specified precision.
	 *
	 * @param   double   $value      value to convert
	 * @param   string   $fromUnit   name of the source unit from which to convert
	 * @param   string   $toUnit     name of the target unit to which we are converting
	 * @param   integer  $precision  double precision of the end result
	 *
	 * @return   mixed   If success: converted value else false
	 *
	 * @access   public
	 */
	public function convert($value, $fromUnit, $toUnit, $precision)
	{
		$converted = 0;

		if ($this->getConvertSpecs($fromUnit, $toUnit, $value, $converted))
		{
			return number_format($converted, (int) $precision, $this->decimalPoint, $this->thousandSeparator);
		}
		else
		{
			return false;
		}
	}

	/**
	 * This Function is for to look up intermediary Conversions from the
	 * "base" unit being that one that has the highest hierarchical order in one
	 * "logical" Conversion_Array when taking
	 * $conv->addConversion('km',
	 * 			array('meter'=>1000, 'dmeter'=>10000, 'centimeter'=>100000,
	 *   		'millimeter'=>1000000, 'mile'=>0.62137, 'naut.mile'=>0.53996,
	 *    		'inch(es)/zoll'=>39370, 'ft/foot/feet'=>3280.8, 'yd/yard'=>1093.6
	 *     )
	 * );
	 *
	 * Checks for a key in the Conversion-table and returns a value
	 *
	 * @param   string  $key  Conversion Key
	 *
	 * @return  mixed
	 */
	public function checkKey($key)
	{
		if (array_key_exists($key, $this->conversionTable))
		{
			if (!empty($this->conversionTable[$key]))
			{
				return $this->conversionTable[$key];
			}
		}

		return false;
	}

	/**
	 * Key function. Finds the conversion ratio and offset from one unit to another.
	 *
	 * @param   string  $fromUnit    name of the source unit from which to convert
	 * @param   string  $toUnit      name of the target unit to which we are converting
	 * @param   double  $value       conversion ratio found. Returned by reference.
	 * @param   double  &$converted  offset which needs to be added (or subtracted, if negative)
	 *                               	to the result to convert correctly.
	 *                                	For temperature or some scientific conversions,
	 *                                 	i.e. Fahrenheit -> Celsius
	 *
	 * @return   boolean   true if ratio and offset are found for the supplied
	 *                      units, false otherwise
	 *
	 * @access   private
	 */
	public function getConvertSpecs($fromUnit, $toUnit, $value, &$converted)
	{
		$key        = $fromUnit . "_" . $toUnit;
		$reverseKey = $toUnit . "_" . $fromUnit;
		$found      = false;

		if ($ctArray = $this->checkKey($key))
		{
			// Conversion Specs found directly
			$ratio     = (double) $ctArray['ratio'];
			$offset    = $ctArray['offset'];
			$converted = (double) (($value * $ratio) + $offset);

			return true;
		}
		// Not found in direct order, try reverse order
		elseif ($ctArray = $this->checkKey($reverseKey))
		{
			$ratio     = (double) (1 / $ctArray['ratio']);
			$offset    = $ctArray['offset'] * (-1);
			$converted = (double) (($value + $offset) * $ratio);

			return true;
		}
		// Not found test for intermediary conversion
		else
		{
			// Return ratio = 1 if key-parts match
			if ($key == $reverseKey)
			{
				$ratio     = 1;
				$offset    = 0;
				$converted = $value;

				return true;
			}

			// Otherwise search intermediary
			reset($this->conversionTable);

			while (list($convertK, $i1Value) = each($this->conversionTable))
			{
				// Split the key into parts
				$keyParts = preg_split("/_/", $convertK);

				// Return ratio = 1 if key-parts match
				// Now test if either part matches the from or to unit
				if ($keyParts[1] == $toUnit && ($i2Value = $this->checkKey($keyParts[0] . "_" . $fromUnit)))
				{
					// An intermediary $keyParts[0] was found
					// now let us put things together intermediary 1 and 2
					$converted = (double) (((($value - $i2Value['offset']) / $i2Value['ratio']) * $i1Value['ratio']) + $i1Value['offset']);

					$found = true;
				}
				elseif ($keyParts[1] == $fromUnit && ($i2Value = $this->checkKey($keyParts[0] . "_" . $toUnit)))
				{
					// An intermediary $keyParts[0] was found
					// now let us put things together intermediary 2 and 1
					$converted = (double) (((($value - $i1Value['offset']) / $i1Value['ratio']) + $i2Value['offset']) * $i2Value['ratio']);

					$found = true;
				}
			}

			return $found;
		}
	}
}
