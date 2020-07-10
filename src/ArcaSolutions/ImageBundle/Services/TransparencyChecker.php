<?php

namespace ArcaSolutions\ImageBundle\Services;

class TransparencyChecker
{
    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $imagePath
     * @return boolean
     */
    public function hasTransparency($imagePath)
    {
        $image = imagecreatefrompng($imagePath);

        $w = imagesx($image);
        $h = imagesy($image);

        if ($w > 50 || $h > 50) {
            $thumb = imagecreatetruecolor(10, 10);
            imagealphablending($thumb, false);
            imagecopyresized($thumb, $image, 0, 0, 0, 0, 10, 10, $w, $h);
            $image = $thumb;
            $w = imagesx($image);
            $h = imagesy($image);
        }

        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $rgba = imagecolorat($image, $i, $j);
                if (($rgba & 0x7F000000) >> 24) {
                    return true;
                }
            }
        }

        return false;
    }
}