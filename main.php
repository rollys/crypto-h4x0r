<?php

require_once('crypt.php');
require_once('stego.php');

const TRAMA = 1;
const TRANS = 2;
const CESAR = 3;

$cryptOrderTrama = @$_POST['crypt_trama_order'];
$cryptOrderTrans = @$_POST['crypt_trans_order'];
$cryptOrderCesar = @$_POST['crypt_cesar_order'];
$cryptKeyTrama = @$_POST['crypt_trama_key'] ;
$cryptKeyCesar = @$_POST['crypt_cesar_key'] ;
$cryptKeyTrans = @$_POST['crypt_trans_key'] ;
//
$rawOrderTrama = @$_POST['raw_trama_order'];
$rawOrderTrans = @$_POST['raw_trans_order'];
$rawOrderCesar = @$_POST['raw_cesar_order'];
$rawKeyTrama = @$_POST['raw_trama_key'] ;
$rawKeyCesar = @$_POST['raw_cesar_key'] ;
$rawKeyTrans = @$_POST['raw_trans_key'] ;
if(isset($_POST['generate_abc'])){

    $orderProcess = [];
    if(!empty($cryptOrderTrama) && !empty($cryptKeyTrama)){
        $orderProcess[$cryptOrderTrama] = TRAMA;
    }
    if(!empty($cryptOrderTrans) && !empty($cryptKeyTrans)){
        $orderProcess[$cryptOrderTrans] = TRANS;
    }
    if(!empty($cryptOrderCesar) && !empty($cryptKeyCesar)){
        $orderProcess[$cryptOrderCesar] = CESAR;
    }
    ksort($orderProcess);
    $cryptClass = new CryptHaxor();
    $abcCrypt = $cryptClass->abc;
    foreach ($orderProcess as $key => $val){
        if($val==TRAMA && !empty($cryptKeyTrama)){
            $abcCrypt = $cryptClass->tramasEncode($abcCrypt, $cryptKeyTrama);
        }
        if($val==TRANS && !empty($cryptKeyTrans)){
            $abcCrypt = $cryptClass->transpositionEncode($cryptClass->getABCArray($abcCrypt), $cryptKeyTrans);
        }
        if($val==CESAR && !empty($cryptKeyCesar)){
            $abcCrypt = $cryptClass->cesarEncode($cryptClass->getABCArray($abcCrypt), $cryptKeyCesar);
        }
    }

    $orderProcess = [];
    if(!empty($rawOrderTrama) && !empty($rawKeyTrama)){
        $orderProcess[$rawOrderTrama] = TRAMA;
    }
    if(!empty($rawOrderTrans) && !empty($rawKeyTrans)){
        $orderProcess[$rawOrderTrans] = TRANS;
    }
    if(!empty($rawOrderCesar) && !empty($rawKeyCesar)){
        $orderProcess[$rawOrderCesar] = CESAR;
    }
    ksort($orderProcess);
    $cryptClass = new CryptHaxor();
    $abcRaw = $cryptClass->abc;
    foreach ($orderProcess as $key => $val){
        if($val==TRAMA && !empty($rawKeyTrama)){
            $abcRaw = $cryptClass->tramasEncode($abcRaw, $rawKeyTrama);
        }
        if($val==TRANS && !empty($rawKeyTrans)){
            $abcRaw = $cryptClass->transpositionEncode($cryptClass->getABCArray($abcRaw), $rawKeyTrans);
        }
        if($val==CESAR && !empty($rawKeyCesar)){
            $abcRaw = $cryptClass->cesarEncode($cryptClass->getABCArray($abcRaw), $rawKeyCesar);
        }
    }

    if(strlen($abcCrypt) !== strlen($abcRaw)) throw new Exception('The length of the alphabet are not equal, check the configuration of the alphabet!');
}

$textRaw = @$_POST['text_raw'];
$dontRepeatKey = @$_POST['dont_repeat_key'];
$dontRepeatIdi = @$_POST['dont_repeat_idi'];
if(isset($_POST['crypt_abc'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $cryptClass = new CryptHaxor();
    $textEncrypt = $cryptClass->encrypt($textRaw, $abcCrypt, $abcRaw, $dontRepeatKey, $dontRepeatIdi);
}

if(isset($_POST['decrypt_abc'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $cryptClass = new CryptHaxor();
    $textDecrypt = $cryptClass->decrypt($textRaw, $abcCrypt, $abcRaw, $dontRepeatKey, $dontRepeatIdi);
}

$textRawImage = @$_POST['text_raw_image'];
if(isset($_POST['crypt_image_button'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $imgArr = uploadImg('raw_image');
    $stego = new StreamSteganography('./img/'.$imgArr['name']);
    $stego->Write($textRawImage);
    $urlImg = $imgArr['url'];
}

if(isset($_POST['decrypt_image_button'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $imgArr = uploadImg('crypt_image');
    $stego = new StreamSteganography('./img/'.$imgArr['name']);
    $urlImg = $imgArr['url'];
    $textCryptImage = $stego->Read();
}

function uploadImg($elem)
{
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $stateImg = false;
    if (isset($_FILES[$elem]) && $_FILES[$elem]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$elem]['tmp_name'];
        $fileName = $_FILES[$elem]['name'];
        $fileSize = $_FILES[$elem]['size'];
        $fileType = $_FILES[$elem]['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $dest_path = '';
        $allowedfileExtensions = array('jpg', 'jpeg', 'png');
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        if (in_array($fileExtension, $allowedfileExtensions)) {

            $uploadFileDir = './img/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path))
            {
                chmod($dest_path, 0777);
                $stateImg = true;
            }
        }
        $urlImg = $stateImg ? $actual_link .'img/'.$newFileName: '';
        return ["url" => $urlImg, "name" => $newFileName];
    }
    return ["url" => '', "name" => ''];
}