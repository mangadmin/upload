<?php

/**
* Simple upload class with validation
*
* @author       Erwin Heldy G (http://www.facebook.com/erwinheldy)
* @copyright    Copyright (c) 2013
* @link         http://github.com/mangadmin/upload
*/

class Upload
{
	var $file_post;
	var $file_name;
	var $file_type;
	var $file_temp;
	var $file_size;
	var $max_size;
	var $upload_path;
	var $allowed_types;
	var $name;
	var $errors = array();

	function __construct($file_post)
	{
		if (!isset($_FILES[$file_post]))
			exit('Undefined index: '.$file_post);

		if (empty($_FILES[$file_post]['size']))
			exit('Please select file');

		$this->file_post = $file_post;
		$this->file_name = $_FILES[$this->file_post]['name'];
		$this->file_type = $_FILES[$this->file_post]['type'];
		$this->file_temp = $_FILES[$this->file_post]['tmp_name'];
		$this->file_size = self::get_formatted_size($_FILES[$this->file_post]['size'],0);
		$this->name      = $this->file_name;
	}
	public function set_upload_path($value='')
	{
		if (!empty($value))
			$this->upload_path = @realpath($value);
	}
	public function set_max_size($value='')
	{
		if (!empty($value))
			$this->max_size = $value;
	}
	public function set_allowed_types($value='')
	{
		if (!empty($value))
			$this->allowed_types = $value;
	}
	public function set_name($value='')
	{
		if (!empty($value))
			$this->name = $value;
	}
	private function validate_max_size()
	{
		if (!empty($this->max_size))
		{
			$file_size_type = self::get_unit($this->file_size);
			$max_size_type  = self::get_unit($this->max_size);
			$file_size      = self::get_size($this->file_size);
			$max_size       = self::get_size($this->max_size);

			if ($file_size_type > $max_size_type)
			{
				$this->errors[] = 'Maximum allowed size is '.$this->max_size;
			}
			else
			{
				if ($file_size > $max_size)
				{
					$this->errors[] = 'Maximum allowed size is '.$this->max_size;
				}
			}
		}
	}
	private function validate_upload_path()
	{
		if ( ! @is_dir($this->upload_path))
		{
			$this->errors[] = 'Upload path does not exist';
		}
	}
	private function validate_allowed_types()
	{
		if (!empty($this->allowed_types))
		{
			$file_ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
			$allowed_types = explode('|', $this->allowed_types);

			if (!in_array($file_ext, $allowed_types))
			{
				$this->errors[] = 'Allowed file type is '.implode(', ',$allowed_types);
			}
		}
	}
	private function upload()
	{
		if (!empty($this->errors)) { return false; } else
		{
			@move_uploaded_file($this->file_temp, $this->upload_path.DIRECTORY_SEPARATOR.$this->name);

			if (file_exists($this->upload_path.DIRECTORY_SEPARATOR.$this->name)) { return true; } else
			{
				$this->errors[] = 'There is an error in our server. Please try again later';
				return false;
			}
		}
	}
	public function run()
	{
		$this->validate_upload_path();
		$this->validate_max_size();
		$this->validate_allowed_types();

		if ($this->upload() !== true)
			return false;
		else
			return true;
	}
	public function get_errors()
	{
		return $this->errors;
	}
	public function get_ext()
	{
		return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
	}
	static function get_formatted_size($bytes, $precision = 2)
	{
	    $units = array('B', 'KB', 'MB', 'GB', 'TB');
	    $bytes = max($bytes, 0);
	    $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
	    $pow   = min($pow, count($units) - 1);
	    $bytes /= pow(1024, $pow);

	    return round($bytes, $precision).$units[$pow];
	}
	static function get_unit($value)
	{
		$unit = preg_split('#(?<=\d)(?=[a-z])#i', $value)[1];
		switch ($unit)
		{
			case 'B'  :return '0'; break;
			case 'KB' :return '1'; break;
			case 'MB' :return '2'; break;
			case 'GB' :return '3'; break;
			case 'TB' :return '4'; break;
			default	  :return '0'; break;
		}
	}
	static function get_size($value)
	{
		return preg_split('#(?<=\d)(?=[a-z])#i', $value)[0];
	}
}
