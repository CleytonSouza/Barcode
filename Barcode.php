<?php
/*CleytonSouza */
final class Barcode {
	
	const f2B = "11";
	const f2W = "00";
	const f2b = "10";
	const f2w = "01";


	private $_code = array();


	private $_codes_39 = array(
		32 => '100011011001110110',
		36 => '100010001000100110',
		37 => '100110001000100010',
		42 => '100010011101110110',
		43 => '100010011000100010',
		45 => '100010011001110111',
		46 => '110010011001110110',
		47 => '100010001001100010',
		48 => '100110001101110110',
		49 => '110110001001100111',
		50 => '100111001001100111',
		51 => '110111001001100110',
		52 => '100110001101100111',
		53 => '110110001101100110',
		54 => '100111001101100110',
		55 => '100110001001110111',
		56 => '110110001001110110',
		57 => '100111001001110110',
		65 => '110110011000100111',
		66 => '100111011000100111',
		67 => '110111011000100110',
		68 => '100110011100100111',
		69 => '110110011100100110',
		70 => '100111011100100110',
		71 => '100110011000110111',
		72 => '110110011000110110',
		73 => '100111011000110110',
		74 => '100110011100110110',
		75 => '110110011001100011',
		76 => '100111011001100011',
		77 => '110111011001100010',
		78 => '100110011101100011',
		79 => '110110011101100010',
		80 => '100111011101100010',
		81 => '100110011001110011',
		82 => '110110011001110010',
		83 => '100111011001110010',
		84 => '100110011101110010',
		85 => '110010011001100111',
		86 => '100011011001100111',
		87 => '110011011001100110',
		88 => '100010011101100111',
		89 => '110010011101100110',
		90 => '100011011101100110'
	);


	public $barcode_bar_thick = 3;


	public $barcode_bar_thin = 1;


	public $barcode_bg_rgb = array(255, 255, 255);


	public $barcode_height = 80;


	public $barcode_padding = 5;


	public $barcode_text = true;


	public $barcode_text_size = 3;

	
	public $barcode_use_dynamic_width = true;


	public $barcode_width = 400;


	public function  __construct($code = null) {
	
		$code = (string)strtoupper($code);

		
		$i = 0;
		while(isset($code[$i])) {
			$this->_code[] = $code[$i++];
		}

		
		array_unshift($this->_code, "*");
		array_push($this->_code, "*");
	}

	public function draw($filename = null) {
		
		if(!function_exists("imagegif")) {
			return false;
		}

		
		if(!is_array($this->_code) || !count($this->_code)) {
			return false;
		}

		
		$bars = array();

		
		$pos = $this->barcode_padding;

		
		$barcode_string = null;

		
		$i = 0;
		foreach($this->_code as $k => $v) {
			
			if(isset($this->_codes_39[ord($v)])) {
				
				$code = ( $i ? self::f2w : null ) . $this->_codes_39[ord($v)];

				
				if($code) {
					
					$barcode_string .= " {$v}";

					
					$w = 0;
					$f2 = $fill = null;

					
					for($j = 0; $j < strlen($code); $j++) {
						
						$f2 .= (string)$code[$j];

						
						if(strlen($f2) == 2) {
							
							$fill = $f2 == self::f2B || $f2 == self::f2b ? "_000" : "_fff";

							
							$w = $f2 == self::f2B || $f2 == self::f2W ? $this->barcode_bar_thick : $this->barcode_bar_thin;

							
							if($w && $fill) {
								
								$bars[] = array($pos, $this->barcode_padding, $pos - 1 + $w,
									$this->barcode_height - $this->barcode_padding - 1, $fill);

								
								$pos += $w;
							}

							
							$f2 = $fill = null;
							$w = 0;
						}
					}
				}
				$i++;
			
			} else {
				unset($this->_code[$k]);
			}
		}

		
		if(!count($bars)) {
			
			return false;
		}

		
		$bc_w = $this->barcode_use_dynamic_width ? $pos + $this->barcode_padding : $this->barcode_width;

		
		if(!$this->barcode_use_dynamic_width && $pos > $this->barcode_width) {
			return false;
		}

		
		$img = imagecreate($bc_w, $this->barcode_height);
		$_000 = imagecolorallocate($img, 0, 0, 0);
		$_fff = imagecolorallocate($img, 255, 255, 255);
		$_bg = imagecolorallocate($img, $this->barcode_bg_rgb[0], $this->barcode_bg_rgb[1], $this->barcode_bg_rgb[2]);

		
		imagefilledrectangle($img, 0, 0, $bc_w, $this->barcode_height, $_bg);

		
		for($i = 0; $i < count($bars); $i++) {
			imagefilledrectangle($img, $bars[$i][0], $bars[$i][1], $bars[$i][2], $bars[$i][3], $$bars[$i][4]);
		}

		
		if($this->barcode_text) {
			
			$barcode_text_h = 10 + $this->barcode_padding;
			imagefilledrectangle($img, $this->barcode_padding, $this->barcode_height - $this->barcode_padding - $barcode_text_h,
				$bc_w - $this->barcode_padding, $this->barcode_height - $this->barcode_padding, $_fff);

			
			$font_size = $this->barcode_text_size;
			$font_w = imagefontwidth($font_size);
			$font_h = imagefontheight($font_size);

			
			$txt_w = $font_w * strlen($barcode_string);
			$pos_center = ceil((($bc_w - $this->barcode_padding) - $txt_w) / 2);

			
			$txt_color = imagecolorallocate($img, 0, 255, 255);

			
			imagestring($img, $font_size, $pos_center, $this->barcode_height - $barcode_text_h - 2,
				$barcode_string, $_000);
		}

		
		if($filename) {
			imagegif($img, $filename);
		
		} else {
			header("Content-type: image/gif");
			imagegif($img);
		}
		
		imagedestroy($img);

		
		return true;
	}
}
?>