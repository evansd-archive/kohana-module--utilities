<?php
class field_Core
{
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
		$value = field::get_value($data, $value);
		return form::input($data, $value, $extra);
	}
	
	
	public static function password($data, $value = '', $extra = '')
	{
		$value = field::get_value($data, $value);
		return form::password($data, $value, $extra);
	}
	
	
	public static function upload($data, $value = '', $extra = '')
	{
		$value = field::get_value($data, $value);
		return form::upload($data, $value, $extra);
	}
	
	
	public static function textarea($data, $value = '', $extra = '')
	{
		$value = field::get_value($data, $value);
		return form::textarea($data, $value, $extra);
	}
	
	
	public static function dropdown($data, $options = NULL, $selected = NULL, $extra = '')
	{
		$selected = field::get_value($data, $selected);
		return form::dropdown($data, $options, $selected, $extra);
	}
	
	
	public static function checkbox($data, $checked = FALSE, $value = '1', $extra = '')
	{
		
		$name = is_array($data) ? $data['name'] : $data;
		$checked = field::get_value($name, $checked);
		return '<input type="hidden" name="'.$name.'" value="" />'.form::checkbox($data, $value, $checked, $extra);
	}
	
	
	public static function radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		$checked = field::get_value($data, $checked);
		if ( ! is_bool($checked))
		{
			$checked = (string) $value === (string) $checked;
		}
		return form::radio($data, $value, $checked, $extra);
	}
}
