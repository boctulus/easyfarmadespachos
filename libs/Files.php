<?php

/*
    @author  Pablo Bozzolo boctulus@gmail.com
*/

namespace boctulus\EasyFarmaDespachos\libs;

class Files
{
	static function write(string $path, string $string, int $flags = 0) : bool {
		$ok = (bool) @file_put_contents($path, $string, $flags);
		return $ok;
	}

	/*
		Escribe archivo o falla.
	*/
	static function writeOrFail(string $path, string $string, int $flags = 0){
		if (empty($path)){
			throw new \InvalidArgumentException("path is empty");
		}

		if (is_dir($path)){
			throw new \InvalidArgumentException("$path is not a valid file. It's a directory!");
		}

		$dir = Strings::beforeLast($path, DIRECTORY_SEPARATOR);

		static::writableOrFail($dir, "$path is not writable");

		$ok = (bool) @file_put_contents($path, $string, $flags);

		if (!$ok){
			throw new \Exception("$path could not be written");
		}
	}

	static function mkDir($dir, int $permissions = 0777, bool $recursive = true){
		$ok = null;

		if (!is_dir($dir)) {
			$ok = @mkdir($dir, $permissions, $recursive);
		}

		return $ok;
	}
	
	static function mkDirOrFail($dir, int $permissions = 0777, $recursive = true, string $error = "Failed trying to create %s"){
		$ok = null;

		if (!is_dir($dir)) {
			$ok = @mkdir($dir, $permissions, $recursive);
			if ($ok !== true){
				throw new \Exception(sprintf($error, $dir));
			}
		}

		return $ok;
	}

	static function writableOrFail(string $path, string $error = "'%s' is not writable"){
		if (!is_writable($path)){
			throw new \Exception(sprintf($error, $path));
		}
	}

	/*
		Files::logger([
			'x' => 'y'
		]);
	*/
	static function logger($data){	
		if (is_array($data) || is_object($data))
			$data = json_encode($data);
		
		// En /home/www/woo4/wp-content/error.log
		error_log($data);
	}

	static function dump($object){
		$data = var_export($object,  true);
		
		error_log($data);
	}


	static function get_rel_path(){
		$ini = strpos(__DIR__, '/wp-content/');
		$rel_path = substr(__DIR__, $ini);
		$rel_path = substr($rel_path, 0, strlen($rel_path)-4);
		
		return $rel_path;
	}			
	

}







