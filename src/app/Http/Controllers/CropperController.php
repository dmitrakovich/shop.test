<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CropperController extends Controller
{
    /*public function save(Request $request)
    {
        $imagePath = storage_path('app/temp/');

        $allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
        $temp = explode(".", $_FILES["img"]["name"]);
        $extension = end($temp);

        //Check write Access to Directory

        if(!is_writable($imagePath)) {
            return $this->error('Can`t upload File; no write Access');
        }

        if (!in_array($extension, $allowedExts)) {
            return $this->error('something went wrong, most likely file is to large for upload. check upload_max_filesize, post_max_size and memory_limit in you php.ini');
        }

        if ($_FILES["img"]["error"] > 0) {
            return $this->error('ERROR Return Code: '. $_FILES["img"]["error"]);
        }

        $filename = $_FILES["img"]["tmp_name"];
        list($width, $height) = getimagesize( $filename );

        move_uploaded_file($filename,  $imagePath . $_FILES["img"]["name"]);

        $response = array(
            "status" => 'success',

            "url" => '/temp/'.$_FILES["img"]["name"],
            "width" => $width,
            "height" => $height
        );

        return $response;
    }*/

    public function crop(Request $request)
    {
        $files = $request->allFiles();
        is_array($files) or abort(403, 'Файлы не переданы');

        foreach ($files as $file) {
            $timestamp = time();
            $name = $timestamp . '.' . $file->clientExtension();
            $moveResult = $file->move(storage_path('app/temp'), $name);

            return [
                'id' => 'new-' . $timestamp,
                'src' => '/temp/' . basename($moveResult),
                'name' => $name
            ];
        }
    }
}
