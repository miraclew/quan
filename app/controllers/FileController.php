<?php
class FileController extends BaseController {
    public function postUpload()
    {
        $file = Input::file('file');
        if ($file == null) {
            return JR::fail(Code::PARAMS_INVALID);
        }

        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();

        $randFileName = uniqid(true);
        $fileName = $randFileName.'_orig.'.$extension;
        $filePath = __DIR__.'/../../public/upload/';
        $file->move($filePath, $fileName);

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/upload/';
        $mimeType = $file->getClientMimeType();
        $type = explode('/', $mimeType);
        if ($type[0] == 'image') {
            $dstFile = $randFileName.'.'.$extension;
            $this->makePhotoThumb($filePath.$fileName, $filePath.$dstFile, 160, 160);

            return JR::ok(array('url_orig'=>$url.$fileName, 'size'=>$size,'url'=>$url.$dstFile));
        } else {
            return JR::ok(array('url'=>$url.$fileName, 'size'=>$size));
        }

    }

    function makePhotoThumb($srcFile,$dstFile,$dstW,$dstH) {
        $data = @getimagesize($srcFile);
        if($data[0]>$dstW || $data[1]>$dstW){
            if($data[0]>$data[1]){
                $dstH   =   round($dstW*$data[1]/$data[0]);
            }else{
                $dstW   =   round($dstH*$data[0]/$data[1]);
            }
        }else{
            copy($srcFile,$dstFile);
        }
        switch ($data[2]) {
            case 1: //图片类型，1是GIF图
                $im = @ImageCreateFromGIF($srcFile);
                break;
            case 2: //图片类型，2是JPG图
                $im = @imagecreatefromjpeg($srcFile);
                break;
            case 3: //图片类型，3是PNG图
                $im = @ImageCreateFromPNG($srcFile);
                break;
        }
        $srcW=ImageSX($im);
        $srcH=ImageSY($im);
        $ni=imagecreatetruecolor($dstW,$dstH);
        imagecopyresized($ni,$im,0,0,0,0,$dstW,$dstH,$srcW,$srcH);
        ImageJpeg($ni,$dstFile,100);
        //ImageJpeg($ni); //在显示图片时用，把注释取消，可以直接在页面显示出图片。
  }
}