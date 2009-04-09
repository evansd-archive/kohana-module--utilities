<?php
class form extends form_Core
{
	/* *
	 * All form input functions now accept an array or object as $value and will
	 * use $value[$name] (or $value->$name) as the input value
	 * e.g., form::input('name', $user) behaves the same as:
	 *       form::input('name', $user['name'])
	 * If $value[$name] is not definied the value will be NULL
	 * */
	protected static function get_value($data, $value)
	{
		$name = is_array($data) ? $data['name'] : $data;

		if (is_array($value) OR $value instanceof ArrayObject)
		{
			return isset($value[$name]) ? $value[$name] : NULL;
		}
		elseif (is_object($value))
		{
			return isset($value->$name) ? $value->$name : NULL;
		}
		else
		{
			return $value;
		}
	}


	public static function input($data, $value = '', $extra = '')
	{
		$value = self::get_value($data, $value);
		return parent::input($data, $value, $extra);
	}


	public static function password($data, $value = '', $extra = '')
	{
		$value = self::get_value($data, $value);
		return parent::password($data, $value, $extra);
	}


	public static function upload($data, $value = '', $extra = '')
	{
		$value = self::get_value($data, $value);
		return parent::upload($data, $value, $extra);
	}


	public static function textarea($data, $value = '', $extra = '')
	{
		$value = self::get_value($data, $value);
		return parent::textarea($data, $value, $extra);
	}


	public static function dropdown($data, $options = NULL, $selected = NULL, $extra = '')
	{
		$selected = self::get_value($data, $selected);
		return parent::dropdown($data, $options, $selected, $extra);
	}


	public static function checkbox($data,  $value = '1', $checked = FALSE, $extra = '')
	{
		$checked = self::get_value($data, $checked);
		return parent::checkbox($data, $value, $checked, $extra);
	}


	public static function radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		$checked = self::get_value($data, $checked);

		if ( ! is_bool($checked))
		{
			$checked = (string) $value === (string) $checked;
		}

		return parent::radio($data, $value, $checked, $extra);
	}


	public static function attributes($attr, $type = NULL)
	{
		// Hack to prevent the automatic creation of id attributes:
		// parent::attributes won't add an id if it finds a '[' character
		// in the name, so we prepend one, and then strip it out afterwards
		if (isset($attr['name']))
		{
			$attr['name'] = '['.$attr['name'];
			$str = parent::attributes($attr, $type);
			$str = str_replace('name="[', 'name="', $str);
			return $str;
		}
		else
		{
			return parent::attributes($attr, $type);
		}
	}
}
