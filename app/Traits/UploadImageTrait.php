<?php

namespace App\Traits;

trait UploadImageTrait
{

    function upload_image($path, $file)
    {
        // dd($uploaded_file);
        if ($file) {
            $filenameWithExt = $file->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = 'image_' . $filename . '_' . time() . '.' . $extension;
            $file->move(public_path($path), $fileNameToStore);
            return request()->getSchemeAndHttpHost() . $path . '' . $fileNameToStore;
        }

        return '';
    }
}