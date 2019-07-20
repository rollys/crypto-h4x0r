<?php

require_once('crypt.php');
require_once('stego.php');

const TRAMA = 1;
const TRANS = 2;
const CESAR = 3;

if(isset($_POST['generate_abc'])){
    $cryptOrderTrama = @$_POST['crypt_trama_order'];
    $cryptOrderTrans = @$_POST['crypt_trans_order'];
    $cryptOrderCesar = @$_POST['crypt_cesar_order'];
    $cryptKeyTrama = @$_POST['crypt_trama_key'] ;
    $cryptKeyCesar = @$_POST['crypt_cesar_key'] ;
    $cryptKeyTrans = @$_POST['crypt_trans_key'] ;
    $orderProcess = [];
    if(!empty($cryptOrderTrama)){
        $orderProcess[$cryptOrderTrama] = TRAMA;
    }
    if(!empty($cryptOrderTrans)){
        $orderProcess[$cryptOrderTrans] = TRANS;
    }
    if(!empty($cryptOrderCesar)){
        $orderProcess[$cryptOrderCesar] = CESAR;
    }
    ksort($orderProcess);
    $cryptClass = new CrytpHaxor();
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


    $rawOrderTrama = @$_POST['raw_trama_order'];
    $rawOrderTrans = @$_POST['raw_trans_order'];
    $rawOrderCesar = @$_POST['raw_cesar_order'];
    $rawKeyTrama = @$_POST['raw_trama_key'] ;
    $rawKeyCesar = @$_POST['raw_cesar_key'] ;
    $rawKeyTrans = @$_POST['raw_trans_key'] ;
    $orderProcess = [];
    if(!empty($rawOrderTrama)){
        $orderProcess[$rawOrderTrama] = TRAMA;
    }
    if(!empty($rawOrderTrans)){
        $orderProcess[$rawOrderTrans] = TRANS;
    }
    if(!empty($rawOrderCesar)){
        $orderProcess[$rawOrderCesar] = CESAR;
    }
    ksort($orderProcess);
    $cryptClass = new CrytpHaxor();
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
}

if(isset($_POST['crypt_abc'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $textRaw = @$_POST['text_raw'];
    $cryptClass = new CrytpHaxor();
    $textEncrypt = $cryptClass->encrypt($textRaw, $abcCrypt, $abcRaw);
}

if(isset($_POST['decrypt_abc'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $textRaw = @$_POST['text_raw'];
    $cryptClass = new CrytpHaxor();
    $textDecrypt = $cryptClass->decrypt($textRaw, $abcCrypt, $abcRaw);
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
                $stateImg = true;
            }
        }
        $urlImg = $stateImg ? $actual_link .'img/'.$newFileName: '';
        return ["url" => $urlImg, "name" => $newFileName];
    }
    return ["url" => '', "name" => ''];
}

if(isset($_POST['crypt_image_button'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $textRawImage = @$_POST['text_raw_image'];
    $imgArr = uploadImg('raw_image');
    $stego = new StreamSteganography('./img/'.$imgArr['name']);
    $stego->Write($textRawImage);
    $urlImg = $imgArr['url'];
}

if(isset($_POST['decrypt_image_button'])){
    $abcCrypt = @$_POST['abc_crypt'];
    $abcRaw = @$_POST['abc_raw'];
    $textRawImage = @$_POST['text_raw_image'];
    $imgArr = uploadImg('crypt_image');
    $stego = new StreamSteganography('./img/'.$imgArr['name']);
    $urlImg = $imgArr['url'];
    $textCryptImage = $stego->Read();
}

?>

<!DOCTYPE html><html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>H4x0rCrypt App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.min.css"/>
</head>
<body>
<section class="hero is-small is-dark">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title">H4x0rCrypt App</h1>
        </div>
    </div>
</section>
<section class="section">
    <form name="cryptForm" method="post" action="" enctype="multipart/form-data">
        <div class="container">
            <div class="columns is-full">
                <div class="column">
                    <nav class="panel">
                        <p class="panel-heading">
                            SETTINGS PARA EL CRYPTO
                        </p>
                        <div class="panel-block has-text-centered">
                            <div class="column ">
                                <label for="crypt_trama_key" class="label">Clave númerica para la Trama</label>
                                <div class="field has-addons is-grouped is-grouped-centered">
                                    <div class="control">
                                        <input
                                                class="input"
                                                type="number"
                                                placeholder="4231"
                                                name="crypt_trama_key"
                                                id="crypt_trama_key"
                                                maxlength="9"
                                                value="<?php echo @$cryptKeyTrama; ?>"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </div>
                                    <div class="control">
                                        <div class="select is-primary">
                                            <select name="crypt_trama_order">
                                                <option value="0">Selecciona el orden</option>
                                                <option value="1" <?php if(@$cryptOrderTrama==1) echo 'selected'; ?> >1</option>
                                                <option value="2" <?php if(@$cryptOrderTrama==2) echo 'selected'; ?> >2</option>
                                                <option value="3" <?php if(@$cryptOrderTrama==3) echo 'selected'; ?> >3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-block has-text-centered">
                            <div class="column ">
                                <label for="crypt_trans_key" class="label">Clave númerica para la Transposición</label>
                                <div class="field has-addons is-grouped is-grouped-centered">
                                    <div class="control">
                                        <input
                                                class="input"
                                                type="number"
                                                placeholder="32145"
                                                name="crypt_trans_key"
                                                id="crypt_trans_key"
                                                maxlength="9"
                                                value="<?php echo @$cryptKeyTrans; ?>"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </div>
                                    <div class="control">
                                        <div class="select is-primary">
                                            <select name="crypt_trans_order">
                                                <option value="0">Selecciona el orden</option>
                                                <option value="1" <?php if(@$cryptOrderTrans==1) echo 'selected'; ?> >1</option>
                                                <option value="2" <?php if(@$cryptOrderTrans==2) echo 'selected'; ?> >2</option>
                                                <option value="3" <?php if(@$cryptOrderTrans==3) echo 'selected'; ?> >3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-block has-text-centered">
                            <div class="column ">
                                <label for="crypt_cesar_key" class="label">Semilla para Cesar</label>
                                <div class="field has-addons is-grouped is-grouped-centered">
                                    <div class="control">
                                        <input
                                                class="input"
                                                type="number"
                                                placeholder="9"
                                                name="crypt_cesar_key"
                                                id="crypt_cesar_key"
                                                maxlength="9"
                                                value="<?php echo @$cryptKeyCesar; ?>"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </div>
                                    <div class="control">
                                        <div class="select is-primary">
                                            <select name="crypt_cesar_order">
                                                <option value="0">Selecciona el orden</option>
                                                <option value="1" <?php if(@$cryptOrderCesar==1) echo 'selected'; ?> >1</option>
                                                <option value="2" <?php if(@$cryptOrderCesar==2) echo 'selected'; ?> >2</option>
                                                <option value="3" <?php if(@$cryptOrderCesar==3) echo 'selected'; ?> >3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
                <div class="column">
                    <nav class="panel">
                        <p class="panel-heading">
                            SETTINGS PARA EL LLANO
                        </p>
                        <div class="panel-block has-text-centered">
                            <div class="column ">
                                <label for="raw_trama_key" class="label">Clave númerica para la Trama</label>
                                <div class="field has-addons is-grouped is-grouped-centered">
                                    <div class="control">
                                        <input
                                                class="input"
                                                type="number"
                                                placeholder="4231"
                                                name="raw_trama_key"
                                                id="raw_trama_key"
                                                maxlength="9"
                                                value="<?php echo @$rawKeyTrama; ?>"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </div>
                                    <div class="control">
                                        <div class="select is-primary">
                                            <select name="raw_trama_order">
                                                <option value="0">Selecciona el orden</option>
                                                <option value="1" <?php if(@$rawOrderTrama==1) echo 'selected'; ?> >1</option>
                                                <option value="2" <?php if(@$rawOrderTrama==2) echo 'selected'; ?> >2</option>
                                                <option value="3" <?php if(@$rawOrderTrama==3) echo 'selected'; ?> >3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-block has-text-centered">
                            <div class="column ">
                                <label for="raw_trans_key" class="label">Clave númerica para la Transposición</label>
                                <div class="field has-addons is-grouped is-grouped-centered">
                                    <div class="control">
                                        <input
                                                class="input"
                                                type="number"
                                                placeholder="32145"
                                                name="raw_trans_key"
                                                id="raw_trans_key"
                                                maxlength="9"
                                                value="<?php echo @$rawKeyTrans; ?>"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </div>
                                    <div class="control">
                                        <div class="select is-primary">
                                            <select name="raw_trans_order">
                                                <option value="0">Selecciona el orden</option>
                                                <option value="1" <?php if(@$rawOrderTrans==1) echo 'selected'; ?> >1</option>
                                                <option value="2" <?php if(@$rawOrderTrans==2) echo 'selected'; ?> >2</option>
                                                <option value="3" <?php if(@$rawOrderTrans==3) echo 'selected'; ?> >3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-block has-text-centered">
                            <div class="column ">
                                <label for="raw_cesar_key" class="label">Semilla para Cesar</label>
                                <div class="field has-addons is-grouped is-grouped-centered">
                                    <div class="control">
                                        <input
                                                class="input"
                                                type="number"
                                                placeholder="9"
                                                name="raw_cesar_key"
                                                id="raw_cesar_key"
                                                maxlength="9"
                                                value="<?php echo @$rawKeyCesar; ?>"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </div>
                                    <div class="control">
                                        <div class="select is-primary">
                                            <select name="raw_cesar_order">
                                                <option value="0">Selecciona el orden</option>
                                                <option value="1" <?php if(@$rawOrderCesar==1) echo 'selected'; ?> >1</option>
                                                <option value="2" <?php if(@$rawOrderCesar==2) echo 'selected'; ?> >2</option>
                                                <option value="3" <?php if(@$rawOrderCesar==3) echo 'selected'; ?> >3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="columns is-full">
                <input class="button is-danger is-large is-fullwidth" name="generate_abc" type="submit" value="Generar ABCDARIOS">
            </div>
            <div class="columns is-full">
                <div class="column">
                    <label for="raw_trama_key" class="label">ABCDARIOS Crypto</label>
                    <div class="field">
                        <div class="control">
                            <input class="input is-danger" type="text" name="abc_crypt" value="<?php echo @$abcCrypt; ?>" placeholder="" disableed>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <label for="raw_trama_key" class="label">ABCDARIOS Llano</label>
                    <div class="field">
                        <div class="control">
                            <input class="input is-danger" type="text" name="abc_raw" value="<?php echo @$abcRaw; ?>" placeholder="" disableed>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="panel">
                <p class="panel-heading">
                    ENCRITIPTAR TEXTOS
                </p>
                <div class="panel-block has-text-centered">
                    <div class="column is-full">
                        <label for="text_raw" class="label">Texto</label>
                        <div class="field">
                            <div class="control">
                                <textarea name="text_raw" id="text_raw" class="textarea is-warning" placeholder="Hola mundo"><?php echo @$textRaw; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-block has-text-centered">
                    <div class="column">
                        <input class="button is-danger is-large is-fullwidth" name="crypt_abc" type="submit" value="ENCRIPTAR">
                    </div>
                    <div class="column">
                        <input class="button is-success is-large is-fullwidth" name="decrypt_abc" type="submit" value="DESENCRIPTAR">
                    </div>
                </div>
                <div class="panel-block has-text-centered">
                    <div class="column ">
                        <label class="label">Encriptado</label>
                        <div class="field has-addons is-grouped is-grouped-centered">
                            <div class="box">
                                <?php echo @$textEncrypt; ?>
                            </div>
                        </div>
                    </div>
                    <div class="column ">
                        <label class="label">Desencriptado</label>
                        <div class="field has-addons is-grouped is-grouped-centered">
                            <div class="box">
                                <?php echo @$textDecrypt; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            <nav class="panel">
                <p class="panel-heading">
                    ENCRITIPTAR TEXTOS EN IMAGEN
                </p>
                <div class="panel-block has-text-centered">
                    <div class="column is-full">
                        <label for="text_raw_image" class="label">Texto para la imagen</label>
                        <div class="field">
                            <div class="control">
                                <textarea name="text_raw_image" id="text_raw_image" class="textarea is-warning" placeholder="Hola imagen"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-block has-text-centered">
                    <div class="column ">
                        <label class="label">Encriptar</label>
                        <div class="field has-addons is-grouped is-grouped-centered">
                            <div class="file has-name is-fullwidth">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="raw_image">
                                    <span class="file-cta">
                                      <span class="file-icon">
                                        <i class="fas fa-upload"></i>
                                      </span>
                                      <span class="file-label">
                                        Escoger imagen
                                      </span>
                                    </span>
                                    <span class="file-name">image.png</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="column ">
                        <label class="label">Desencriptado</label>
                        <div class="field has-addons is-grouped is-grouped-centered">
                            <div class="file has-name is-fullwidth">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="crypt_image">
                                    <span class="file-cta">
                                      <span class="file-icon">
                                        <i class="fas fa-upload"></i>
                                      </span>
                                      <span class="file-label">
                                        Escoger imagen
                                      </span>
                                    </span>
                                    <span class="file-name">image.png</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-block has-text-centered">
                    <div class="column">
                        <input class="button is-danger is-large is-fullwidth" name="crypt_image_button" type="submit" value="ENCRIPTAR">
                    </div>
                    <div class="column">
                        <input class="button is-success is-large is-fullwidth" name="decrypt_image_button" type="submit" value="DESENCRIPTAR">
                    </div>
                </div>
                <div class="panel-block has-text-centered">
                    <div class="column ">
                        <label class="label">Imagen con texto encriptado</label>
                        <div class="field has-addons is-grouped is-grouped-centered">
                            <img class="" src="<?php echo @$urlImg; ?>">
                        </div>
                    </div>
                    <div class="column ">
                        <label class="label">Texto de la imagen desencriptado</label>
                        <div class="field has-addons is-grouped is-grouped-centered">
                            <div class="box">
                                <?php echo @$textCryptImage; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </form>
</section>

<footer class="footer has-text-centered">
    &copy; Copyright 2019. Developed by <a  href="#" target="_blank" rel="noopener">Rolly Sánchez</a> at Universidad Tecnológica del Perú
</footer>
<script type="text/javascript" src="lib/main.js"></script>
</body>
</html>