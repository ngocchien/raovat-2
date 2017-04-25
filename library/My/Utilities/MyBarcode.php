<?php

namespace My\Utilities;

use Zend\Barcode\Barcode;

class MyBarcode {

    public function generate($strCode) {
        if (!$strCode) {
            return false;
        }

        $barcodeOptions = array(
            'text' => $strCode,
            'barHeight' => 40,
            'font' => 3,
        );

        // No required options
        $rendererOptions = array();
        $renderer = Barcode::factory(
                        'code128', 'image', $barcodeOptions, $rendererOptions
        );

        $imageResource = $renderer->draw();

        $savPath = STATIC_PATH . '/uploads/barcode';

        $imageFilename = $savPath . '/' . $strCode . '.png';
        if (!file_exists($imageFilename)) {
            imagepng($imageResource, $imageFilename);
        }
        $imgUrl = STATIC_URL . '/uploads/barcode/' . $strCode . '.png';
        return $imgUrl;
    }

}
