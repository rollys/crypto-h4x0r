<?php 

class CryptHaxor {

    CONST DIRECTION_LETTER_LEFT = 'i';
    CONST DIRECTION_LETTER_RIGHT = 'd';
    public $abc = 'abcdefghijklmnopqrstuvwxyz';

    public function getABCArray($abc)
    {
        return str_split($abc);
    }

    public function getAllKeyArray($key, $abcLength)
    {
        //$key = str_replace('0', '', $key);
        $keyArr = str_split((int)$key);
        return $this->_completeArray($keyArr, $abcLength);
    }

    public function getAllDirectionArray($direction, $abcLength)
    {
        $abcArr = $this->getABCArray($this->abc);
        $abcArrFilter = array_merge($abcArr, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '?', '#', '$', '%', '/', '"', '\'', '&', '@']);
        $abcWithOutID = array_filter(str_replace([$this::DIRECTION_LETTER_LEFT, $this::DIRECTION_LETTER_RIGHT], '', $abcArrFilter));
        $direction = str_replace($abcWithOutID, '', $direction);
        //if(empty($direction)) throw new Exception('Empty directions');
        $directionArr = str_split($direction);
        return $this->_completeArray($directionArr, $abcLength);
    }

    public function idiProcess($abc, $key, $idi)
    {
        //$abcObj = new \stdClass();
        /*
        $a = ["a"=>'o'];
        var_dump($a);
        var_dump((object)$a);
        $c = json_encode($a);
        $d = json_decode($c);
        var_dump($d);exit;
        */
        $keyArr = $this->getAllKeyArray($key, strlen($abc));
        $directionArr = $this->getAllDirectionArray($idi, strlen($abc));
        //var_dump($keyArr);
        //var_dump($directionArr);
        $abcArr = $this->getABCArray($abc);
        $abcIdi = [];
        foreach ($abcArr as $k => $val){
            //$seed = $k!=0? ((int)$keyArr[$k])-1:$keyArr[$k];
            $seed = $keyArr[$k];
            $abc = $this->getABCArray($this->cesarEncode($abc, $this->_getNumberDirection($seed, $directionArr[$k])*-1));
            //var_dump($abc);//exit;
            /*$abcIdi[] = $abc[0];
            array_shift($abc);*/

            if($k == 0){
                $abcIdi[] = $abc[0];
                array_shift($abc);
            }else{
                $abcIdi[] = $abc[count($abc)-1];
                $abc = array_slice($abc,0,-1);
            }
            var_dump($abc);
            //if($k == 5) exit;
        }
        var_dump($abcIdi);exit;
        return $abcIdi;
    }




    //
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
        ksort($abcKeyArr);
        return implode($abcKeyArr);
    }

    public function transpositionEncode($abcArr, $key)
    {
        $keyArr = str_split($key);
        $abcChunkArr = array_chunk($abcArr, count($keyArr));
        $abcTransposition = [];
        $abcTranspositionString = '';
        foreach ($abcChunkArr as $k => $val){
            $keyArrSlice = array_slice($keyArr, 0, count($val));
            $arr = array_combine($keyArrSlice, $val);
            ksort($arr);
            $abcTransposition[] = $arr;
            $abcTranspositionString .= implode($arr);
        }
        return $abcTranspositionString;
    }

    public function cesarEncode($abc, $seed)
    {
        $abcArr = $abc;
        if (!is_array($abc)){
            $abcArr  = $this->getABCArray($abc);
        }

        if($seed<0){
            $seed = (abs($seed) % count($abcArr))*-1;
        }else{
            $seed = (abs($seed) % count($abcArr));
        }
        $abcCesarArr = [];
        foreach ($abcArr as $k => $val){
            $calPos = $k+(int)$seed;
            if($calPos>25){
                $calPos = ($calPos % 25)-1;
            }
            if($calPos<0){
                $calPos = 25 + $calPos + 1;
            }
            $abcCesarArr[$calPos] = $val;
        }
        ksort($abcCesarArr);
        return implode($abcCesarArr);
    }

    public function encrypt($text, $abcCrypt, $abcRaw, $key = '0', $direction = 'i')
    {
        $abcCrypt = strtolower($abcCrypt);
        $abcRaw = strtolower($abcRaw);
        $textArr = str_split(strtolower($text));
        $textWithoutSpace = str_replace(' ', '', trim($text));
        $textEncrypt = '';
        $keyArr = $this->getAllKeyArray($key, strlen($textWithoutSpace));
        $directionArr = $this->getAllDirectionArray($direction, strlen($textWithoutSpace));
        $currentIndex = 0;
        foreach ($textArr as $k => $val){
            if(!empty($val) && $val != ' '){
                $abcRaw = $this->cesarEncode($abcRaw,
                    $this->_getNumberDirection($keyArr[$currentIndex], $directionArr[$currentIndex]));
                $textEncrypt .= $this->_getLetterCryptByLetterRaw($val, $this->_getABCCryptAndRaw($abcCrypt, $abcRaw));
                $currentIndex++;
            }else{
                $textEncrypt .= ' ';
            }
        }
        return $textEncrypt;
    }

    public function decrypt($text, $abcCrypt, $abcRaw, $key = '0', $direction = 'i')
    {
        $abcCrypt = strtolower($abcCrypt);
        $abcRaw = strtolower($abcRaw);
        $textArr = str_split(strtolower(trim($text)));
        $textWithoutSpace = str_replace(' ', '', trim($text));
        $textDecrypt = '';
        $keyArr = $this->getAllKeyArray($key, strlen($textWithoutSpace));
        $directionArr = $this->getAllDirectionArray($direction, strlen($textWithoutSpace));
        $currentIndex = 0;
        foreach ($textArr as $k => $val){
            if(!empty($val) && $val != ' '){
                $abcCrypt = $this->cesarEncode($abcCrypt,
                    -1*$this->_getNumberDirection($keyArr[$currentIndex], $directionArr[$currentIndex]));
                $textDecrypt .= $this->_getLetterRawByLetterCrypt($val, $this->_getABCCryptAndRaw($abcCrypt, $abcRaw));
                $currentIndex++;
            }else{
                $textDecrypt .= ' ';
            }
        }
        return $textDecrypt;
    }

    protected function _getNumberDirection($number, $letterDirection)
    {
        $number = (int)$number;
        return $letterDirection == $this::DIRECTION_LETTER_LEFT ? $number*-1:$number;
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


//$d = new CryptHaxor();
//$d->idiProcess($d->abc,'312', 'idi'); //should be result: xyvstr...