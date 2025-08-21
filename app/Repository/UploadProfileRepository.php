<?php
namespace App\Repository;

use Illuminate\Support\Facades\File;

class UploadProfileRepository {
    public static function save($image) {
        $file = $image;
        $file_name = url('upload/pfp/' . $file->hashName());
        $destinasi = 'upload/pfp/';
        $file->move($destinasi, $file_name);

        return $file_name;
    }

    public function update($old_image, $image) {
        $array = explode('/', $old_image);

        if(isset($array[5])) {
            File::delete(public_path('upload/pfp/' . $array[5]));
        }

        $file = $image;
        $file_name = url('upload/pfp/' . $file->hashName());
        $destinasi = 'upload/pfp/';
        $file->move($destinasi, $file_name);

        return $file_name;
    }

    public function delete($image) {
        $array = explode('/', $image);

        if(isset($array[5])) {
            File::delete(public_path('upload/pfp/' . $array[5]));
        }
    }
}
