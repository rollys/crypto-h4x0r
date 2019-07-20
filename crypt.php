<?php 

class CrytpHaxor {

    public $abc = 'abcdefghijklmnopqrstuvwxyz';

    public function getABCArray($abc)
    {
        return str_split($abc);
    }

    public function getAllKeyArray($key, $abcLength)
    {
        $key = str_replace('0', '', $key);
        $keyArr = str_split($key);
        return $this->_completeArray($keyArr, $abcLength);
    }

    public function getAllIdiArray($idi, $abcArr)
    {
        $abcArrFilter = array_merge($abcArr, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '?', '#', '$', '%', '/', '"', '\'', '&', '@']);
        $abcWithOutID = array_filter(str_replace(['i','d'], '', $abcArrFilter));
        $idi = str_replace($abcWithOutID, '', $idi);
        $idiArr = str_split($idi);
        return $this->_completeArray($idiArr, count($abcArr));
    }

    public function idiProcess($abcArr, $key, $idi)
    {
        $keyArr = $this->getAllKeyArray($key, count($abcArr));
        $idiArr = $this->getAllIdiArray($idi, $abcArr);
        //var_dump($idiArr);exit;
        $abcIdi = [];
        $pos = 0;
        foreach ($keyArr as $k => $num){
            $valIdi = trim(strtolower($idiArr[$k]));
            $directionNum = $num*1;
            if($valIdi === 'i'){
                $directionNum = $num*-1;
            }

            $calPos = $pos + $directionNum;

            if($calPos<0){
                $initPos = 25;
                $haveLetter = false;
                $countPos = 0;
                while (!$haveLetter) {
                    if(!in_array($abcArr[$initPos], $abcIdi)){
                        $countPos++;
                    }
                    if($countPos == abs($calPos)){
                        $abcIdi[] = $abcArr[$initPos];
                        $pos = array_search($abcArr[$initPos], $abcArr);
                        $haveLetter = true;
                    }
                    $initPos--;
                }

            }elseif($calPos>25){
                $initPos = 0;
                $haveLetter = false;
                $countPos = 0;
                while (!$haveLetter) {
                    if(!in_array($abcArr[$initPos], $abcIdi)){
                        $countPos++;
                    }
                    if($countPos == abs($calPos)){
                        $abcIdi[] = $abcArr[$initPos];
                        $pos = array_search($abcArr[$initPos], $abcArr);
                        $haveLetter = true;
                    }
                    $initPos++;
                }
            }else{

                $initPos = $pos;
                $haveLetter = false;
                $countPos = 0;
                while (!$haveLetter) {
                    if(!in_array($abcArr[$initPos], $abcIdi)){
                        $countPos++;
                    }
                    if($countPos == abs($calPos)){
                        $abcIdi[] = $abcArr[$initPos];
                        $pos = array_search($abcArr[$initPos], $abcArr);
                        $haveLetter = true;
                    }
                    if($directionNum<0){
                        $initPos--;
                    }else{
                        $initPos++;
                    }
                }
            }



        }

        return $abcIdi;

    }

    public function tramasEncode($abc, $key)
    {
        $abcStringArr = str_split($abc);
        $keyArr = str_split($key);
        $groupNum = round(count($abcStringArr) / count($keyArr),0, PHP_ROUND_HALF_DOWN);
        $abcArr = str_split($abc, $groupNum);
        $abcKeyArr = [];
        foreach ($abcArr as $k => $val){
            if(isset($keyArr[$k])){
                $abcKeyArr[$keyArr[$k]] = $val;
            }else{
                $abcKeyArr[] = $val;
            }
        }
        //var_dump($abcKeyArr);
        ksort($abcKeyArr);
        //var_dump($abcKeyArr);
        return implode($abcKeyArr);
    }


    public function transpositionEncode($abcArr, $key)
    {
        $keyArr = str_split($key);
        $abcChunkArr = array_chunk($abcArr, count($keyArr));
        //var_dump($abcChunkArr);
        $abcTransposition = [];
        $abcTranspositionString = '';
        foreach ($abcChunkArr as $k => $val){
            $keyArrSlice = array_slice($keyArr, 0, count($val));
            $arr = array_combine($keyArrSlice, $val);
            ksort($arr);
            $abcTransposition[] = $arr;
            $abcTranspositionString .= implode($arr);
        }
        //var_dump($abcTransposition);
        return $abcTranspositionString;
    }

    public function cesarEncode($abcArr, $seed)
    {
        if($seed<0){
            $seed = (abs($seed) % count($abcArr))*-1;
        }else{
            $seed = (abs($seed) % count($abcArr));
        }
        $abcCesarArr = [];
        foreach ($abcArr as $k => $val){
            $calPos = $k+(int)$seed;
            $key = $calPos;
            if($calPos>25){
                $calPos = ($calPos % 25)-1;
            }
            if($calPos<0){
                $calPos = 25 + $calPos +1;
            }
            $abcCesarArr[$calPos] = $val;
        }
        ksort($abcCesarArr);
        // var_dump($abcCesarArr);exit;
        return implode($abcCesarArr);
    }

    public function encrypt($text, $abcCrypt, $abcRaw)
    {
        //var_dump($text);
        $textArr = str_split(strtolower(trim($text)));
        //var_dump($textArr);
        $textEncrypt = '';
        foreach ($textArr as $k => $val){
            if(!empty($val) && $val != ' '){
                //var_dump($val);
                $textEncrypt .= $this->_getLetterCryptByLetterRaw($val, $this->_getABCCryptAndRaw($abcCrypt, $abcRaw));
            }else{
                $textEncrypt .= ' ';
            }
        }
        //exit;
        return $textEncrypt;
    }

    public function decrypt($text, $abcCrypt, $abcRaw)
    {
        $textArr = str_split(strtolower(trim($text)));
        $textEncrypt = '';
        foreach ($textArr as $k => $val){
            if(!empty($val) && $val != ' '){
                $textEncrypt .= $this->_getLetterRawByLetterCrypt($val, $this->_getABCCryptAndRaw($abcCrypt, $abcRaw));
            }else{
                $textEncrypt .= ' ';
            }
        }
        return $textEncrypt;
    }

    protected function _getABCCryptAndRaw($abcCrypt, $abcRaw)
    {
        $abcCryptArr = str_split($abcCrypt);
        $abcRawArr = str_split($abcRaw);
        return array_combine($abcCryptArr, $abcRawArr);
    }

    protected function _getLetterCryptByLetterRaw($letterRaw, $abcCryptAndRawArr)
    {
        $letterCrypt = array_search($letterRaw, $abcCryptAndRawArr);
        return $letterCrypt;
    }

    protected function _getLetterRawByLetterCrypt($letterCrypt, $abcCryptAndRawArr)
    {
        return $abcCryptAndRawArr[$letterCrypt];
    }



    protected function _completeArray($value, $length)
    {
        if (!is_array($value)){
            $value  = str_split($value);
        }
        $arr = [];
        $index = 0;
        for($i=0; $i<$length; $i++){
            if(!isset($value[$index])){
               $index = 0;
            }
            $arr[] = $value[$index];
            $index++;
        }
        return $arr;
    }

}


//$d = new CrytpHaxor();

// var_dump( $d->idiProcess($d->getABCArray(),'80453', 'ids3i'), true);
//var_dump( $d->tramasProcess($d->abc,'4231'));
//var_dump( $d->transpositionEncode($d->getABCArray($abc),'4231'));
// var_dump( $d->cesarEncode($d->getABCArray($abc),'-53'));