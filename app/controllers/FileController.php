<?php
class FileController extends BaseController {
    public function postUpload()
    {
        $file = Input::file('file');

        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();

        $fileName = uniqid(true).'.'.$extension;
        $filePath = __DIR__.'/../../public/upload/';
        $file->move($filePath, $fileName);

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/upload/'.$fileName;

        return Response::json(array('url'=>$url, 'size'=>$size));
    }
}