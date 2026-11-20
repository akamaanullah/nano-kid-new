<?php

declare(strict_types=1);

namespace App\Services;

final class Uploader
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
     */
    public static function image(array $file, string $subdir = 'products'): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Image upload failed.');
        }
        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('Invalid upload.');
        }
        if ((int)($file['size'] ?? 0) > 4 * 1024 * 1024) {
            throw new \RuntimeException('Images must be 4 MB or smaller.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmp);
        if (!isset(self::ALLOWED[$mime])) {
            throw new \RuntimeException('Please upload a JPG, PNG, WEBP or GIF image.');
        }
        $ext = self::ALLOWED[$mime];
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dir = project_root() . '/public/uploads/' . trim($subdir, '/');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Could not create upload folder.');
        }
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('Could not store the image.');
        }
        return $subdir . '/' . $name;
    }
}
