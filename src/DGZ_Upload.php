<?php
namespace DGZ_Uploader;
use Illuminate\Support\Facades\Redirect;


class DGZ_Upload {

  protected $_uploaded = array();



  protected $_destination;



  protected $_max = 51200;



  protected $_messages = array();



  protected $_permitted = array('image/gif',
								'image/jpeg',
								'image/pjpeg',
								'image/jpg',
								'image/png',
								'image/webp');
  protected $_renamed = false;



  protected $_filenames = array();




  public function __construct($path) {
	  try {
		  if (!is_dir($path) || !is_writable($path)) {
			  throw new \InvalidArgumentException("$path must be a valid, writable directory.");
		  }
	  }
	  catch (\InvalidArgumentException $e)
	  {
		  return Redirect::back()->withErrors(['Error', $e->getMessage()]);
	  }
	$this->_destination = $path;
	$this->_uploaded = $_FILES;
  }




  public function getMaxSize() {
	return number_format($this->_max/1024, 1) . 'kB';
  }




  /**
   * Set the maximum permitted upload file size.
   *
   * Accepts a human-readable string or a raw integer (bytes).
   *
   * String examples (case-insensitive, space optional):
   *     '50KB'   '50 KB'   '5MB'   '5 MB'   '5.5MB'   '1GB'   '1.5 GB'
   *
   * Raw integer (bytes) also accepted for backwards compatibility:
   *     5 * 1024 * 1024   (5 MB)
   *
   * @param int|string $size
   */
  public function setMaxSize($size) {
	if (is_string($size) && preg_match('/^\s*(\d+\.?\d*)\s*(KB|MB|GB|B)?\s*$/i', $size, $m)) {
		$value = (float) $m[1];
		$unit  = strtoupper($m[2] ?? 'B');
		$multipliers = ['B' => 1, 'KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824];
		$this->_max = (int) round($value * $multipliers[$unit]);
	} elseif (is_numeric($size)) {
		$this->_max = (int) $size;
	} else {
		throw new \Exception("Invalid size value '$size'. Use a number (bytes) or a string like '5MB', '500KB', '1.5GB'.");
	}
  }




	/*
	 * Upload the file
	 * @param String $modify either 'original' to upload the file as is, or 'resize' to resize the file upon uploading
	 * @param Boolean $overwrite to determine whether to replace any previous copy of the file at the destination, or to rename and keep both
	 *
	 */
  public function move($modify = 'original',$overwrite = false) {
	$path = $this->_destination;

	if ($this->_uploaded) {
		$field = current($this->_uploaded);
		if (is_array($field['name'])) {
			foreach ($field['name'] as $number => $filename) {
				// process multiple upload
				$this->_renamed = false;
				$this->processFile($filename, $field['error'][$number], $field['size'][$number], $field['type'][$number], $field['tmp_name'][$number], $path, $modify, $overwrite);
			}
		}
		else {
			$this->processFile($field['name'], $field['error'], $field['size'], $field['type'], $field['tmp_name'], $path, $modify, $overwrite);
		}
	}
  }




  public function getMessages() {
	return $this->_messages;
  }




  protected function checkError($filename, $error) {
	switch ($error) {
	  case 0:
		return true;
	  case 1:
	  case 2:
	    $this->_messages[] = "$filename exceeds maximum size: " . $this->getMaxSize();
		return true;
	  case 3:
		$this->_messages[] = "Error uploading $filename. Please try again.";
		return false;
	  case 4:
		$this->_messages[] = 'No file selected.';
		return false;
	  default:
		$this->_messages[] = "System error uploading $filename. Contact webmaster.";
		return false;
	}
  }




  protected function checkSize($filename, $size) {
	if ($size == 0) {
	  return false;
	} elseif ($size > $this->_max) {
	  $this->_messages[] = "$filename exceeds maximum size: " . $this->getMaxSize();
	  return false;
	} else {
	  return true;
	}
  }





  protected function checkType($filename, $type) {
	if (empty($type)) {
	  return false;
	} elseif (!in_array($type, $this->_permitted)) {
	  $this->_messages[] = "$filename is not a permitted type of file.";
	  return false;
	} else {
	  return true;
	}
  }




  public function addPermittedTypes($types) {
	$types = (array) $types;
    $this->isValidMime($types);
	$this->_permitted = array_merge($this->_permitted, $types);
  }




  public function getFilenames() {
	return $this->_filenames;
  }


  /**
   * Returns the file extension of the given filename (without the dot).
   *
   *     DGZ_Upload::extension('sunset.jpg')      // 'jpg'
   *     DGZ_Upload::extension('photo.PNG')       // 'PNG'
   *     $uploader->extension($filenames[0])      // instance call also works
   *
   * @param  string $filename  Filename or full path.
   * @return string
   */
  public static function extension(string $filename): string {
	return pathinfo($filename, PATHINFO_EXTENSION);
  }


  /**
   * Derives the thumbnail filename from an original filename.
   * Assumes the default '_thb' suffix unless you pass a custom one.
   *
   *     DGZ_Upload::thumbName('sunset.jpg')          // 'sunset_thb.jpg'
   *     DGZ_Upload::thumbName('hero.PNG', '_sm')     // 'hero_sm.PNG'
   *     $uploader->thumbName($filenames[0])          // instance call also works
   *
   * @param  string $filename  Original filename (not a full path).
   * @param  string $suffix    Thumbnail suffix. Must match whatever was passed
   *                           to DGZ_Thumbnail::setSuffix() — default '_thb'.
   * @return string
   */
  public static function thumbName(string $filename, string $suffix = '_thb'): string {
	$ext  = pathinfo($filename, PATHINFO_EXTENSION);
	$base = pathinfo($filename, PATHINFO_FILENAME);
	return $base . $suffix . ($ext !== '' ? '.' . $ext : '');
  }




  protected function isValidMime($types) {
    $alsoValid = array('image/tiff',
					   'image/webp',
				       'application/pdf',
				       'text/plain',
				       'text/rtf');
  	$valid = array_merge($this->_permitted, $alsoValid);
	foreach ($types as $type) {
	  if (!in_array($type, $valid)) {
		throw new \Exception("$type is not a permitted MIME type");
	  }
	}
  }




	/*
	 * Checks if a file with the same name previously exists in the upload destination and overwrites the previous file if $overwrite is true
	 * or renames the uploaded file and keeps both files if $overwrite is false
	 *
	 * @param variable $name name of uploaded file
	 * @param Boolean $overwrite true or false whether to replace existing file or not
	 */
  protected function createFileName($name, $overwrite) {

	  //get rid of any blank space in the submitted file name
	  $nospaces = str_replace(' ', '_', $name);
	  //mark the file as renamed if that changed the name from what was submitted
	  if ($nospaces != $name) {
	  $this->_renamed = true;
	}
	if (!$overwrite) {
		$existing = scandir($this->_destination);
		//check if an image with that name already exists
		if (in_array($nospaces, $existing)) {
		//if the filename already exists, we need to rename the file, so let's start by finding the character number of the '.' character
		$dot = strrpos($nospaces, '.');
		if ($dot) {
			//get the name of the file up to but without the dot
			$base = substr($nospaces, 0, $dot);
			//store the current file extension as well
			$extension = substr($nospaces, $dot);
		} else {
			//else if the file has no extension, store its name too
			$base = $nospaces;
		  	$extension = '';
		}
		$i = 1;
		do {
			$nospaces = $base . '_' . $i++ . $extension;
		} while (in_array($nospaces, $existing));
			//mark the file as renamed
			$this->_renamed = true;
	  }
	}
	//return the new file name
	return $nospaces;
  }





  protected function processFile($filename, $error, $size, $type, $tmp_name, $path, $modify, $overwrite) {
	$OK = $this->checkError($filename, $error);
	if ($OK) {
	  $sizeOK = $this->checkSize($filename, $size);
	  $typeOK = $this->checkType($filename, $type);
	  if ($sizeOK && $typeOK) {
		$name = $this->createFileName($filename, $overwrite);

		$success = move_uploaded_file($tmp_name, $path . $name);
		if ($success) {
			// add the amended filename to the array of file names
			$this->_filenames[] = $name;

			$message = "$filename uploaded successfully";
			if ($this->_renamed) {
			  $message .= " and renamed $name";
			}
			$this->_messages[] = $message;
		} else {
		  $this->_messages[] = "Could not upload $filename";
		}
	  }
	}
  }

}
