<?php
namespace App\Repository;

use Illuminate\Support\Facades\File;

class UploadRepository {
    public static function save($image) {
        $filename = $image->hashName();
        $destinasi = 'upload/img/';
        $image->move($destinasi, $filename);

        return url($destinasi . $filename);
    }

    public function update($old_image, $image) {
        if ($old_image) {
            $filename = basename($old_image);
            $filepath = public_path('upload/img/' . $filename);
            if (File::exists($filepath) && is_file($filepath)) {
                File::delete($filepath);
            }
        }

        $new_filename = $image->hashName();
        $destinasi = 'upload/img/';
        $image->move($destinasi, $new_filename);

        return url($destinasi . $new_filename);
    }

    public function delete($image) {
        if ($image) {
            $filename = basename($image);
            $filepath = public_path('upload/img/' . $filename);
            if (File::exists($filepath) && is_file($filepath)) {
                File::delete($filepath);
            }
        }
    }
}
