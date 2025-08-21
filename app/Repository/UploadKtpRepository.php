<?php
namespace App\Repository;

use Illuminate\Support\Facades\File;

class UploadKtpRepository {
    public static function save($image) {
        $file = $image;
        $file_name = rtrim(config('app.url'), '/') . '/upload/ktp/' . $file->hashName();
        $destinasi = 'upload/ktp/';
        $file->move($destinasi, $file_name);

        return $file_name;
    }

    public function update($old_image, $image) {
        $array = explode('/', $old_image);

        if(isset($array[5])) {
            File::delete(public_path('upload/ktp/' . $array[5]));
        }

        $file = $image;
        $file_name = rtrim(config('app.url'), '/') . '/upload/ktp/' . $file->hashName();
        $destinasi = 'upload/ktp/';
        $file->move($destinasi, $file_name);

        return $file_name;
    }

    public function delete($image) {
        $array = explode('/', $image);

        if(isset($array[5])) {
            File::delete(public_path('upload/ktp/' . $array[5]));
        }
    }
}
