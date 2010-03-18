<?php
class DataURIFilter
{
	public function convert($s) {
		// thanks to kravco
		//var_dump($file);exit;
		//str_replace('\\', '\\\', $s);
		$regexp = '~
			(?<![a-z])
			url\(                                     ## url(
				\s*                                   ##   optional whitespace
				([\'"])?                              ##   optional single/double quote
				(   (?: (?:\\\\.)+                    ##     escape sequences
					|   [^\'"\\\\,()\s]+              ##     safe characters
					|   (?(1)   (?!\1)[\'"\\\\,() \t] ##       allowed special characters
						|       ^                     ##       (none, if not quoted)
						)
					)*                                ##     (greedy match)
				)
				(?(1)\1)                              ##   optional single/double quote
				\s*                                   ##   optional whitespace
			\)                                        ## )
		~xs';

		return preg_replace_callback(
			$regexp,
			create_function(
				'$matches',
				'return "url(\'" . DataURIFilter::encode($matches[2]) . "\')";'
			),
			$s
		);
	}
	
	public static function encode($file)
	{
		$file = $file;
		$imagesize = getimagesize($file);
		$mime = $imagesize['mime'];
		
		$fd = fopen($file, 'rb');
	    $size = filesize($file);
	    $cont = fread($fd, $size);
	    fclose($fd);
	    $encimg = base64_encode($cont);
		$out = 'data:'.$mime.';base64,'.$encimg;
		
		return $out;
	}
}
?>