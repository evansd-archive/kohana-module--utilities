<?php
class ImageMagick_Core {
	
	// Directory that ImageMagick is installed in
	protected $directory;

	// Command extension (exe for windows)
	protected $ext;
	
	// ImageMagick commands
	protected $identify;
	protected $convert;
	
	protected static $version;
	
	// Holds the command line arguments to be passed to 'convert'
	protected $argument;
	
	protected $details;
	
	/**
	* Creates a new ImageMagick instance and returns it.
	*
	* @param   string   filename of image
	* @return  object
	*/
	public static function factory($file)
	{
		return new ImageMagick($file);
	}
	
	/**
	* Validates a given file.
	*
	* @param   mixed   filename, or array of uploaded file details
	* @return  bool
	*/
	public static function valid($file)
	{
		if (is_array($file))
		{
			$file = $file['tmp_name'];
		}
		
		return ImageMagick::factory($file)->is_valid;
	}
	
	/**
	* Creates a new ImageMagick instance.
	*
	* @param   string   filename of image
	* @return  object
	*/
	public function __construct($file)
	{
		if ( ! isset($this->directory))
		{
			$this->directory = '';
		}
		
		$this->ext = KOHANA_IS_WIN ? '.exe' : '';
		
		if ( ! isset($this->identify))
		{
			$this->identify = $this->directory.'identify'.$this->ext;
		}
		
		if ( ! isset($this->convert))
		{
			$this->convert = $this->directory.'convert'.$this->ext;
		}
		
		$this->file = $file;
		
		$this->details = new StdClass;
		
		$this->argument = escapeshellarg($file);
	}
	
	
	public function version()
	{
		if( ! isset(ImageMagick::$version))
		{
			sscanf($this->exec($this->directory.' -version', TRUE), 'Version: ImageMagick %s', ImageMagick::$version);
		}
		return ImageMagick::$version;
	}
	
	

	
	
	public function __get($key)
	{
		switch($key)
		{
			case 'is_valid':
				return (bool) $this->details()->format;
			
			case 'is_cmyk':
				return stripos($this->details()->colorspace, 'CMYK') !== FALSE;
				
			case 'has_embedded_profile':
				return $this->has_embedded_profile();
			
			default:
				return $this->details()->$key;
		}
		
	}
	
	
	protected function details()
	{
		if ( ! isset($this->details->format))
		{
			$properties = array('%w'=>'width', '%h'=>'height', '%m'=>'format', '%r'=>'colorspace');
			$endmarker = '--';
			
			$args = join('\n', array_keys($properties)).'\n%[EXIF:*]\n'.$endmarker.'\n';
			
			$command = $this->identify.' -format '.escapeshellarg($args).' '.escapeshellarg($this->file);
			
			$output = $this->exec($command);
			
			if($output)
			{
				foreach($properties as $property)
				{
					$this->details->$property = array_shift($output);
				}
				
				$this->details->exif = new StdClass;
				foreach($output as $line)
				{
					if($line == $endmarker) break;
					
					// Lines are of the form key=value
					if( ! preg_match('/^([A-Za-z0-9]+)=(.*)/', $line, $matches)) continue;
					
					// Add the property to the EXIF object
					$this->details->exif->$matches[1] = $matches[2];
				}
			}
			else
			{
				$this->details->format = FALSE;
			}
		}
		
		return $this->details;
	}
	
	
	protected function modify($command)
	{
		$this->argument .= ' '.$command;
		return $this;
	}
	
	
	public function resize($style, $width = NULL, $height = NULL, $flags = '', $filter = NULL)
	{
		switch($style)
		{
			// Fit within the given dimesions - can be just width or just height
			case 'fit':
				$command = '-resize '.escapeshellarg($width.'x'.$height.$flags);
				break;
			
			// Scale and crop to completely fill given dimensions - both width and height required
			case 'fill':
				$size = ($height * $this->width > $width * $this->height) ? 'x'.$height : $width.'x';
				$command =
					'-resize '.escapeshellarg($size).
				    ' -gravity center -crop '.escapeshellarg($width.'x'.$height.'+0+0').' +repage';
				break;
			
			// Fit within given dimensions and pad to full size - both width and height required
			case 'pad':
				$max = ceil(max($width, $height) / 2);
				$command =
					'-resize '.escapeshellarg($width.'x'.$height.$flags).
					' -compose Over -bordercolor transparent -border '.escapeshellarg($max).
					' -gravity center -crop '.escapeshellarg($width.'x'.$height.'+0+0').' +repage';
				break;
			
			case 'scale':
				// different argument order: style, factor, filter
				$factor = func_get_arg(1) * 100;
				$filter = func_get_arg(2);
				$command = '-resize '.escapeshellarg($factor.'x'.$factor.'%');
				break;
		}
		
		if($filter) $command = '-filter '.escapeshellarg($filter).' '.$command;
		
		return $this->modify($command);
	}
	
	
	
	// Ensure image is upright based on EXIF orientation.
	public function reorient() {
		
		switch($this->exif->Orientation) {
			
			case 2: return $this->modify('-flip horizontal');
			case 3: return $this->modify('-rotate 180');
			case 4: return $this->modify('-flip vertical');
			case 5: return $this->modify('-transpose');
			case 6: return $this->modify('-rotate 90');
			case 7: return $this->modify('-transverse');
			case 8: return $this->modify('-rotate 270');
			
		}
		
		return $this;
	}
	
	
	public function round_corners($tl, $tr = 0, $br = 0, $bl = 0)
	{	
		// If only one argument is given then use it as radius for all four corners
		$radii = func_num_args() > 1 ? array($tl, $tr, $br, $bl) : array_fill(0, 4, $tl);
		
		$command = '\( +clone -gravity NorthWest -fill white -draw "color 0,0 reset" ';
		
		foreach($radii as $radius)
		{
			$radius = max(0, intval($radius));
			$center = $radius - 0.5;
		
			$command .=
			  " -fill black -draw \"rectangle 0,0 $radius,$radius\"".
			  " -fill white -draw \"circle $center,$center 0,$center\"".
			  ' -rotate -90';
		}
		
		$command .= " +matte \) -compose CopyOpacity -composite";
			
		return $this->modify($command);
	
	}
	
	
	public function background($color)
	{
		return $this->modify(
			'\( +clone -fill '.escapeshellarg($color).' -draw "color 0,0 reset" \) '.
			'-compose DstOver -composite');
	}
	
	
	public function quality($quality)
	{
		return $this->modify('-quality '.escapeshellarg($quality));
	}
	
	
	public function interlace($type = 'line')
	{
		return $this->modify('-interlace '.escapeshellarg($type));
	}
	
	
	public function overlay($overlay, $gravity = 'Center', $x = 0, $y = 0, $compose = 'Over')
	{
		// Overlay can be either another ImageMagick object, or a string which is a filename
		$overlay = ($overlay instanceof ImageMagick) ? ('\( '.$overlay->command.' \)') : escapeshellarg($overlay);
		
		$x < 0 or $x = '+'.$x; // non-negative numbers need an explicit '+'
		$y < 0 or $y = '+'.$y;
		
		return $this->modify(
			$overlay.' -gravity '.escapeshellarg($gravity).
			' -geometry '.escapeshellarg($x.$y).
			' -compose '.escapeshellarg($compose).
			' -composite');
	}
	
	
	public function convert_profile($profile_to, $profile_from = NULL, $override_embedded = FALSE)
	{
		if( ! $override_embedded AND $this->has_embedded_profile())
		{
			return $this->modify('-profile '.escapeshellarg($profile_to));
		}
		elseif ($profile_from)
		{
			return $this->modify(
				'+profile icm'.
				' -profile '.escapeshellarg($profile_from).
				' -profile '.escapeshellarg($profile_to));
		}
		
		return $this;
	}
	
	
	public function has_embedded_profile()
	{
		if ( ! isset($this->details->has_embedded_profile))
		{
			// Apparently, the only reliable way to test for embedded color
			// profiles is to try to convert the file to a profile and
			// check if it produces anything - lovely, eh?
			$this->details->has_embedded_profile =
				(bool) $this->exec($this->convert.' '.escapeshellarg($this->file).' icc:-', TRUE);
		}
		
		return $this->details->has_embedded_profile;
	}
	
	
	public function save($file, $format = NULL)
	{
		// Passing TRUE as filename means, 'overwrite original file'
		$file !== TRUE or $file = $this->file; 
		
		// If the filename has no extension and no format is passed in then we use the original format
		if (strpos($file, '.') === FALSE AND ! $format)
		{
			$format = $this->details()->format;
		}
		
		$command = $this->convert.' '.$this->argument.' ';
		
		// Explicitly specifiy the output format, if there is one
		if($format) $command .= escapeshellarg($format).':';
		
		if ($file === FALSE)
		{
			// Pipe image to stdout and return it
			return $this->exec($command.'-', TRUE);
		}
		else
		{
			// Save resulting image to file and return status
			return (bool) $this->exec($command.escapeshellarg($file));
		}
	}
	
	
	public function render($format = NULL)
	{
		return $this->save(FALSE, $format);
	}
	
	
	protected function exec($command, $passthru = FALSE)
	{
		// Format command for Windows compatibility
		if (KOHANA_IS_WIN)
		{
			// Bracket escaping doesn't work on windows
			$command = str_replace(array('\(', '\)'), array('(', ')'), $command);
			
			// Windows needs whole command encased in quotes
			$command = '"'.$command.'"';
		}
		
		if($passthru)
		{
			ob_start();
			passthru($command, $error_status);
			$output = ob_get_clean();
		}
		else
		{
			exec($command, $output, $error_status);
		}
		
		return $error_status == 0 ? $output : FALSE;
	}
	
}
