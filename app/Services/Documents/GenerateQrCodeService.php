<?php

// FILE: app/Services/Documents/GenerateQrCodeService.php

namespace App\Services\Documents;

use App\Models\Permit;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class GenerateQrCodeService
{
    public function handle(Permit $permit): string
    {
        $path = 'qrcodes/' . $permit->permit_no . '.svg';
        $url = route('verify.permit', $permit->verification_code);

        $renderer = new ImageRenderer(
            new RendererStyle(180, 1),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($url);

        Storage::disk('local')->put($path, $svg);

        return $path;
    }
}
