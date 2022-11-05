<?php
{

                              #R  G  B
    public $colorA = 7944996;     #79 3B 24
    public $colorB = 16696767;    #FE C5 BF


    public $arA = array();
    public $arB = array();
    
    public function AWS_FILTER()
    {
        $this->arA['R'] = ($this->colorA >> 16) & 0xFF;
        $this->arA['G'] = ($this->colorA >> 8) & 0xFF;
        $this->arA['B'] = $this->colorA & 0xFF;
        
        $this->arB['R'] = ($this->colorB >> 16) & 0xFF;
        $this->arB['G'] = ($this->colorB >> 8) & 0xFF;
        $this->arB['B'] = $this->colorB & 0xFF;
    }
    
    public function Getresult($image)
    {
        $x = 0;
        $y = 0;
        $img = $this->_GetImageResource($image, $x, $y);
        if (!$img) {
            return false;
        }

        $result = 0;
        
        $xPoints = array($x/8, $x/4, ($x/8 + $x/4), $x-($x/8 + $x/4), $x-($x/4), $x-($x/8));
        $yPoints = array($y/8, $y/4, ($y/8 + $y/4), $y-($y/8 + $y/4), $y-($y/8), $y-($y/8));
        $zPoints = array($xPoints[2], $yPoints[1], $xPoints[3], $y);

        
        for ($i=1; $i<=$x; $i++) {
            for ($j=1; $j<=$y; $j++) {
                $color = imagecolorat($img, $i, $j);
                if ($color >= $this->colorA && $color <= $this->colorB) {
                    $color = array('R'=> ($color >> 16) & 0xFF, 'G'=> ($color >> 8) & 0xFF, 'B'=> $color & 0xFF);
                    if ($color['G'] >= $this->arA['G'] && $color['G'] <= $this->arB['G'] && $color['B'] >= $this->arA['B'] && $color['B'] <= $this->arB['B']) {
                        if ($i >= $zPoints[0] && $j >= $zPoints[1] && $i <= $zPoints[2] && $j <= $zPoints[3]) {
                            $result += 3;
                        } elseif ($i <= $xPoints[0] || $i >=$xPoints[5] || $j <= $yPoints[0] || $j >= $yPoints[5]) {
                            $result += 0.10;
                        } elseif ($i <= $xPoints[0] || $i >=$xPoints[4] || $j <= $yPoints[0] || $j >= $yPoints[4]) {
                            $result += 0.40;
                        } else {
                            $result += 1.50;
                        }
                    }
                }
            }
        }
        
        imagedestroy($img);
        
        $result = sprintf('%01.2f', ($result * 100) / ($x * $y));
        if ($result > 100) {
            $result = 100;
        }
        return $result;
    }
    
    public function GetresultAndFill($image, $outputImage)
    {
        $x = 0;
        $y = 0;
        $img = $this->_GetImageResource($image, $x, $y);
        if (!$img) {
            return false;
        }

        $result = 0;

        $xPoints = array($x/8, $x/4, ($x/8 + $x/4), $x-($x/8 + $x/4), $x-($x/4), $x-($x/8));
        $yPoints = array($y/8, $y/4, ($y/8 + $y/4), $y-($y/8 + $y/4), $y-($y/8), $y-($y/8));
        $zPoints = array($xPoints[2], $yPoints[1], $xPoints[3], $y);


        for ($i=1; $i<=$x; $i++) {
            for ($j=1; $j<=$y; $j++) {
                $color = imagecolorat($img, $i, $j);
                if ($color >= $this->colorA && $color <= $this->colorB) {
                    $color = array('R'=> ($color >> 16) & 0xFF, 'G'=> ($color >> 8) & 0xFF, 'B'=> $color & 0xFF);
                    if ($color['G'] >= $this->arA['G'] && $color['G'] <= $this->arB['G'] && $color['B'] >= $this->arA['B'] && $color['B'] <= $this->arB['B']) {
                        if ($i >= $zPoints[0] && $j >= $zPoints[1] && $i <= $zPoints[2] && $j <= $zPoints[3]) {
                            $result += 3;
                            imagefill($img, $i, $j, 16711680);
                        } elseif ($i <= $xPoints[0] || $i >=$xPoints[5] || $j <= $yPoints[0] || $j >= $yPoints[5]) {
                            $result += 0.10;
                            imagefill($img, $i, $j, 14540253);
                        } elseif ($i <= $xPoints[0] || $i >=$xPoints[4] || $j <= $yPoints[0] || $j >= $yPoints[4]) {
                            $result += 0.40;
                            imagefill($img, $i, $j, 16514887);
                        } else {
                            $result += 1.50;
                            imagefill($img, $i, $j, 512);
                        }
                    }
                }
            }
        }
        imagejpeg($img, $outputImage);

        imagedestroy($img);

        $result = sprintf('%01.2f', ($result * 100) / ($x * $y));
        if ($result > 100) {
            $result = 100;
        }
        return $result;
    }
    
    public function _GetImageResource($image, &$x, &$y)
    {
        $info = GetImageSize($image);
        
        $x = $info[0];
        $y = $info[1];
        
        switch ($info[2]) {
            case IMAGETYPE_GIF:
                return @ImageCreateFromGif($image);
                
            case IMAGETYPE_JPEG:
                return @ImageCreateFromJpeg($image);
                
            case IMAGETYPE_PNG:
                return @ImageCreateFromPng($image);
                
            default:
                return false;
        }
    }
}

                $filter = new AWS_FILTER;
                $result = $filter->Getresult($_GET["img"]);
                          
                 echo json_encode(["imgnode"=>$result],128|34|256);         
