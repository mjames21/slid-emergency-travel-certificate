<?php
// FILE: app/Services/Mrz/PreparePassportImagesService.php

namespace App\Services\Mrz;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PreparePassportImagesService
{
    public function handle(string $passportBiodataImagePath): array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($passportBiodataImagePath)) {
            throw new RuntimeException('Passport biodata image not found.');
        }

        $absolutePath = $disk->path($passportBiodataImagePath);
        $imageData = file_get_contents($absolutePath);

        if ($imageData === false) {
            throw new RuntimeException('Could not read passport biodata image.');
        }

        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is required for MRZ image preparation.');
        }

        $image = @imagecreatefromstring($imageData);

        if ($image === false) {
            throw new RuntimeException('Unsupported passport biodata image format.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width < 100 || $height < 100) {
            imagedestroy($image);
            throw new RuntimeException('Passport biodata image is too small to process.');
        }

        $croppedMrz = $this->cropMrzBand($image, $width, $height);

        $mrzImagePath = $this->buildMrzPath($passportBiodataImagePath);

        $mrzAbsolutePath = $disk->path($mrzImagePath);
        $mrzDirectory = dirname($mrzAbsolutePath);

        if (! is_dir($mrzDirectory) && ! mkdir($mrzDirectory, 0775, true) && ! is_dir($mrzDirectory)) {
            imagedestroy($image);
            imagedestroy($croppedMrz);
            throw new RuntimeException('Could not create MRZ image directory.');
        }

        if (! imagejpeg($croppedMrz, $mrzAbsolutePath, 95)) {
            imagedestroy($image);
            imagedestroy($croppedMrz);
            throw new RuntimeException('Could not save cropped MRZ image.');
        }

        imagedestroy($image);
        imagedestroy($croppedMrz);

        return [
            'passport_biodata_image_path' => $passportBiodataImagePath,
            'passport_mrz_image_path' => $mrzImagePath,
        ];
    }

    protected function cropMrzBand(\GdImage $image, int $width, int $height): \GdImage
    {
        $cropX = (int) round($width * 0.05);
        $cropWidth = (int) round($width * 0.90);

        $cropHeight = (int) round($height * 0.22);
        $cropY = $height - $cropHeight - (int) round($height * 0.03);

        if ($cropY < 0) {
            $cropY = 0;
        }

        if (($cropX + $cropWidth) > $width) {
            $cropWidth = $width - $cropX;
        }

        if (($cropY + $cropHeight) > $height) {
            $cropHeight = $height - $cropY;
        }

        $cropped = imagecrop($image, [
            'x' => $cropX,
            'y' => $cropY,
            'width' => $cropWidth,
            'height' => $cropHeight,
        ]);

        if ($cropped === false) {
            throw new RuntimeException('Could not crop MRZ band from passport image.');
        }

        imagefilter($cropped, IMG_FILTER_GRAYSCALE);
        imagefilter($cropped, IMG_FILTER_CONTRAST, -20);

        return $cropped;
    }

    protected function buildMrzPath(string $passportBiodataImagePath): string
    {
        $info = pathinfo($passportBiodataImagePath);

        $directory = $info['dirname'] ?? 'passport-biodata';
        $filename = $info['filename'] ?? 'passport';
        $timestamp = now()->format('YmdHis');

        return $directory . '/mrz/' . $filename . '-mrz-' . $timestamp . '.jpg';
    }
}