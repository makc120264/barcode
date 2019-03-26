<?php

/**
 * This class generates a barcode and displays it in a browser.
 */
class Barcode {

    /**
     *
     * @var string 
     */
    protected $font;

    /**
     *
     * @var string 
     */
    protected $characterFont;

    /**
     *
     * @var string 
     */
    protected $file;

    /**
     *
     * @var string 
     */
    protected $sku;

    /**
     *
     * @var array
     */
    protected $colorsComponent = [];

    /**
     *
     * @var string 
     */
    protected $fontSize;

    /**
     *
     * @var array 
     */
    protected $resultImage = [];

    /**
     *
     * @var array 
     */
    protected $srcImage = [];

    /**
     *
     * @var array 
     */
    protected $barCodeParam = [];

    /**
     *
     * @var array 
     */
    protected $characterParam = [];

    public function __construct() 
    {
        $this->file = "images/barcode.png";
        $this->font = $_SERVER['DOCUMENT_ROOT'] . '/fonts/free3of9/free3of9.ttf';
        $this->characterFont = $_SERVER['DOCUMENT_ROOT'] . '/fonts/arial.ttf';
        $this->sku = strtoupper($this->sanitize($_POST['sku']));
        $this->fontSize = 40;
        $this->colorsComponent = [
            'red' => 255,
            'green' => 255,
            'blue' => 255
        ];
        $this->resultImage = [
            'x' => 0,
            'y' => 0,
            'width' => ((strlen($this->sku) * 20) + 41),
            'height' => 80,
            'height_1' => 40,
        ];
        $this->srcImage = [
            'x' => 0,
            'y' => 0,
            'width' => 10,
            'height' => 10,
        ];
        $this->barCodeParam = [
            'angle' => 0,
            'x' => 1,
            'y' => 40
        ];
        $this->characterParam = [
            'fontSize' => 10,
            'angle' => 0,
            'x' => 1,
            'y' => 55
        ];
    }

    /**
     * 
     * @return resource
     */
    public function getBarCode() 
    {
        $blank = imagecreatefrompng($this->file);
        imagealphablending($blank, true);
        imagesavealpha($blank, true);
        $basis = imagecolorallocate($blank, $this->colorsComponent['red'], $this->colorsComponent['green'], $this->colorsComponent['blue']);
        $barImage = imagecreatetruecolor($this->resultImage['width'], $this->resultImage['height']);
        imagecopyresized(
                $barImage, 
                $blank, 
                $this->resultImage['x'], 
                $this->resultImage['y'], 
                $this->srcImage['x'], 
                $this->srcImage['y'], 
                $this->resultImage['width'], 
                $this->resultImage['height_1'], 
                $this->srcImage['width'], 
                $this->srcImage['height']
        );
        imagettftext(
                $barImage, 
                $this->fontSize, 
                $this->barCodeParam['angle'], 
                $this->barCodeParam['x'], 
                $this->barCodeParam['y'], 
                $basis, 
                $this->font, 
                '*' . $this->sku . '*'
        );

        imagettftext(
                $barImage, 
                $this->characterParam['fontSize'], 
                $this->characterParam['angle'], 
                $this->characterParam['x'], 
                $this->characterParam['y'], 
                $basis, 
                $this->characterFont, 
                $this->sku
        );

        return $barImage;
    }

    /**
     * 
     * @param string $input
     * @return string
     */
    private function cleanInput($input)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // javascript
            '@<[\/\!]*?[^<>]*?>@si', // HTML tags
            '@<style[^>]*?>.*?</style>@siU', // style tags
            '@<![\s\S]*?--[ \t\n\r]*>@', // multi-level comments
            '@select@si',
            '@drop@si',
            '@insert@si',
            '@update@si',
            '@www@si',
            '@http@si',
            '@https@si',
            '@:\/\/@',
        );

        $output = preg_replace($search, '', $input);

        return $output;
    }

    /**
     * 
     * @param string $input
     * @return string
     */
    private function sanitize($input) 
    {
        $output = $input;
        if (is_array($input)) {
            foreach ($input as $var => $val) {
                $val = htmlspecialchars($val);
                $output[$var] = self::sanitize($val);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $input = stripslashes($input);
            }
            $input = $this->cleanInput($input);
            $output = htmlspecialchars($input);
        }

        return $output;
    }

}

header("Content-type: image/png");
$barCode = new Barcode();
$barImage = $barCode->getBarCode();
//show the image
imagepng($barImage);
imagedestroy($barImage);
