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
			$mimes = self::get_mimes();
			$file_type = self::recursive_array_search($this->file_type, $mimes);
			$allowed_types = explode('|', $this->allowed_types);

			$array_intersect = array_intersect($allowed_types, $file_type);
			if (empty($array_intersect))
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
		$exp = explode('.', $this->file_name);
		return '.'.end($exp);
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
	static function recursive_array_search($search,$array)
	{
	    $result = array();
	    foreach($array as $key => $value)
	    {
	        $current_key = $key;
        	if (!is_array($value))
        	{
        		if ($search === $value)
            		$result[] = $current_key;
        	}
            else
            {
            	if (in_array($search,$value))
            		$result[] = $current_key;
            }
	    }
	    return $result;
	}
	static function get_mimes()
	{
		return array(
			'hqx'	=>	array('application/mac-binhex40', 'application/mac-binhex', 'application/x-binhex40', 'application/x-mac-binhex40'),
			'cpt'	=>	'application/mac-compactpro',
			'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain'),
			'bin'	=>	array('application/macbinary', 'application/mac-binary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary'),
			'dms'	=>	'application/octet-stream',
			'lha'	=>	'application/octet-stream',
			'lzh'	=>	'application/octet-stream',
			'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
			'class'	=>	'application/octet-stream',
			'psd'	=>	array('application/x-photoshop', 'image/vnd.adobe.photoshop'),
			'so'	=>	'application/octet-stream',
			'sea'	=>	'application/octet-stream',
			'dll'	=>	'application/octet-stream',
			'oda'	=>	'application/oda',
			'pdf'	=>	array('application/pdf', 'application/force-download', 'application/x-download', 'binary/octet-stream'),
			'ai'	=>	array('application/pdf', 'application/postscript'),
			'eps'	=>	'application/postscript',
			'ps'	=>	'application/postscript',
			'smi'	=>	'application/smil',
			'smil'	=>	'application/smil',
			'mif'	=>	'application/vnd.mif',
			'xls'	=>	array('application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/excel', 'application/download', 'application/vnd.ms-office', 'application/msword'),
			'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
			'pptx'	=> 	array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-zip', 'application/zip'),
			'wbxml'	=>	'application/wbxml',
			'wmlc'	=>	'application/wmlc',
			'dcr'	=>	'application/x-director',
			'dir'	=>	'application/x-director',
			'dxr'	=>	'application/x-director',
			'dvi'	=>	'application/x-dvi',
			'gtar'	=>	'application/x-gtar',
			'gz'	=>	'application/x-gzip',
			'gzip'  =>	'application/x-gzip',
			'php'	=>	array('application/x-httpd-php', 'application/php', 'application/x-php', 'text/php', 'text/x-php', 'application/x-httpd-php-source'),
			'php4'	=>	'application/x-httpd-php',
			'php3'	=>	'application/x-httpd-php',
			'phtml'	=>	'application/x-httpd-php',
			'phps'	=>	'application/x-httpd-php-source',
			'js'	=>	array('application/x-javascript', 'text/plain'),
			'swf'	=>	'application/x-shockwave-flash',
			'sit'	=>	'application/x-stuffit',
			'tar'	=>	'application/x-tar',
			'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
			'z'		=>	'application/x-compress',
			'xhtml'	=>	'application/xhtml+xml',
			'xht'	=>	'application/xhtml+xml',
			'zip'	=>	array('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'),
			'rar'	=>	array('application/x-rar', 'application/rar', 'application/x-rar-compressed'),
			'mid'	=>	'audio/midi',
			'midi'	=>	'audio/midi',
			'mpga'	=>	'audio/mpeg',
			'mp2'	=>	'audio/mpeg',
			'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
			'aif'	=>	array('audio/x-aiff', 'audio/aiff'),
			'aiff'	=>	array('audio/x-aiff', 'audio/aiff'),
			'aifc'	=>	'audio/x-aiff',
			'ram'	=>	'audio/x-pn-realaudio',
			'rm'	=>	'audio/x-pn-realaudio',
			'rpm'	=>	'audio/x-pn-realaudio-plugin',
			'ra'	=>	'audio/x-realaudio',
			'rv'	=>	'video/vnd.rn-realvideo',
			'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
			'bmp'	=>	array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap'),
			'gif'	=>	'image/gif',
			'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
			'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
			'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
			'png'	=>	array('image/png',  'image/x-png'),
			'tiff'	=>	'image/tiff',
			'tif'	=>	'image/tiff',
			'css'	=>	array('text/css', 'text/plain'),
			'html'	=>	array('text/html', 'text/plain'),
			'htm'	=>	array('text/html', 'text/plain'),
			'shtml'	=>	array('text/html', 'text/plain'),
			'txt'	=>	'text/plain',
			'text'	=>	'text/plain',
			'log'	=>	array('text/plain', 'text/x-log'),
			'rtx'	=>	'text/richtext',
			'rtf'	=>	'text/rtf',
			'xml'	=>	array('application/xml', 'text/xml', 'text/plain'),
			'xsl'	=>	array('application/xml', 'text/xsl', 'text/xml'),
			'mpeg'	=>	'video/mpeg',
			'mpg'	=>	'video/mpeg',
			'mpe'	=>	'video/mpeg',
			'qt'	=>	'video/quicktime',
			'mov'	=>	'video/quicktime',
			'avi'	=>	array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
			'movie'	=>	'video/x-sgi-movie',
			'doc'	=>	array('application/msword', 'application/vnd.ms-office'),
			'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword', 'application/x-zip'),
			'dot'	=>	array('application/msword', 'application/vnd.ms-office'),
			'dotx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword'),
			'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/vnd.ms-excel', 'application/msword', 'application/x-zip'),
			'word'	=>	array('application/msword', 'application/octet-stream'),
			'xl'	=>	'application/excel',
			'eml'	=>	'message/rfc822',
			'json'  =>	array('application/json', 'text/json'),
			'pem'   =>	array('application/x-x509-user-cert', 'application/x-pem-file', 'application/octet-stream'),
			'p10'   =>	array('application/x-pkcs10', 'application/pkcs10'),
			'p12'   =>	'application/x-pkcs12',
			'p7a'   =>	'application/x-pkcs7-signature',
			'p7c'   =>	array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
			'p7m'   =>	array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
			'p7r'   =>	'application/x-pkcs7-certreqresp',
			'p7s'   =>	'application/pkcs7-signature',
			'crt'   =>	array('application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/pkix-cert'),
			'crl'   =>	array('application/pkix-crl', 'application/pkcs-crl'),
			'der'   =>	'application/x-x509-ca-cert',
			'kdb'   =>	'application/octet-stream',
			'pgp'   =>	'application/pgp',
			'gpg'   =>	'application/gpg-keys',
			'sst'   =>	'application/octet-stream',
			'csr'   =>	'application/octet-stream',
			'rsa'   =>	'application/x-pkcs7',
			'cer'   =>	array('application/pkix-cert', 'application/x-x509-ca-cert'),
			'3g2'   =>	'video/3gpp2',
			'3gp'   =>	'video/3gp',
			'mp4'   =>	'video/mp4',
			'm4a'   =>	'audio/x-m4a',
			'f4v'   =>	'video/mp4',
			'webm'	=>	'video/webm',
			'aac'   =>	'audio/x-acc',
			'm4u'   =>	'application/vnd.mpegurl',
			'm3u'   =>	'text/plain',
			'xspf'  =>	'application/xspf+xml',
			'vlc'   =>	'application/videolan',
			'wmv'   =>	array('video/x-ms-wmv', 'video/x-ms-asf'),
			'au'    =>	'audio/x-au',
			'ac3'   =>	'audio/ac3',
			'flac'  =>	'audio/x-flac',
			'ogg'   =>	'audio/ogg',
			'kmz'	=>	array('application/vnd.google-earth.kmz', 'application/zip', 'application/x-zip'),
			'kml'	=>	array('application/vnd.google-earth.kml+xml', 'application/xml', 'text/xml'),
			'ics'	=>	'text/calendar',
			'zsh'	=>	'text/x-scriptzsh',
			'7zip'	=>	array('application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'),
			'cdr'	=>	array('application/cdr', 'application/coreldraw', 'application/x-cdr', 'application/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-application/zz-winassoc-cdr'),
			'wma'	=>	array('audio/x-ms-wma', 'video/x-ms-asf'),
			'jar'	=>	array('application/java-archive', 'application/x-java-application', 'application/x-jar', 'application/x-compressed')
		);
	}
};

?>
