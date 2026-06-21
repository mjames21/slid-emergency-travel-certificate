{{-- FILE: resources/views/components/forms/passport-biodata-capture.blade.php --}}
@props([
    'field' => 'passport_biodata_image',
    'mrzField' => 'passport_mrz_crop_image',
    'previewField' => 'passport_biodata_preview_url',
    'mrzPreviewField' => 'passport_mrz_crop_preview_url',
    'removeAction' => 'removePassportBiodata',
    'readAction' => 'readPassportMrz',
    'label' => 'Passport Biodata',
])

@php
    $hasPrimaryUpload = ! empty($this->{$field});
    $hasPrimaryPreview = ! empty($this->{$previewField});
    $hasMrzUpload = ! empty($this->{$mrzField});
    $hasMrzPreview = ! empty($this->{$mrzPreviewField});

    $primaryPreviewSrc = $hasPrimaryUpload
        ? $this->{$field}->temporaryUrl()
        : ($hasPrimaryPreview ? $this->{$previewField} : null);

    $mrzPreviewSrc = $hasMrzUpload
        ? $this->{$mrzField}->temporaryUrl()
        : ($hasMrzPreview ? $this->{$mrzPreviewField} : null);
@endphp

<div
    x-data="window.passportCaptureCropper()"
    class="space-y-3"
>
    <label class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</label>

    @if (! $primaryPreviewSrc)
        <div class="flex flex-wrap gap-3">
            <label class="cursor-pointer rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Upload Image
                <input type="file" class="hidden" accept="image/*" wire:model="{{ $field }}">
            </label>

            <button
                type="button"
                @click="openCamera"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
            >
                Capture with Camera
            </button>
        </div>

        <p class="text-xs text-gray-500">
            Capture the full passport biodata page. After preview, crop the MRZ zone for reading.
        </p>
    @endif

    <input
        x-ref="capturedFileInput"
        type="file"
        accept="image/*"
        wire:model="{{ $field }}"
        class="hidden"
    >

    <input
        x-ref="mrzCropFileInput"
        type="file"
        accept="image/*"
        wire:model="{{ $mrzField }}"
        class="hidden"
    >

    @error($field)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    @error($mrzField)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div wire:loading wire:target="{{ $field }}" class="text-sm text-gray-500">
        Uploading passport biodata...
    </div>

    <div wire:loading wire:target="{{ $mrzField }}" class="text-sm text-gray-500">
        Uploading MRZ crop...
    </div>

    <div x-show="uploading" x-cloak class="text-sm text-gray-500">
        Processing captured image...
    </div>

    @if ($primaryPreviewSrc)
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Passport Biodata Preview</div>
                    <div class="text-xs text-gray-500">Review the image, then crop the MRZ section.</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="openCropper('{{ $primaryPreviewSrc }}')"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-white"
                    >
                        Crop MRZ
                    </button>

                    <button
                        type="button"
                        wire:click="{{ $readAction }}"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-white"
                    >
                        Read MRZ
                    </button>

                    <button
                        type="button"
                        wire:click="{{ $removeAction }}"
                        class="rounded-lg border border-red-300 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50"
                    >
                        Remove
                    </button>

                    <label class="cursor-pointer rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-white">
                        Replace
                        <input type="file" class="hidden" accept="image/*" wire:model="{{ $field }}">
                    </label>

                    <button
                        type="button"
                        wire:click="{{ $removeAction }}"
                        @click="openCamera"
                        class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white hover:bg-gray-800"
                    >
                        Retake
                    </button>
                </div>
            </div>

            <img
                src="{{ $primaryPreviewSrc }}"
                alt="Passport biodata preview"
                class="h-56 rounded-xl border border-gray-200 bg-white object-contain"
            >

            @if ($mrzPreviewSrc)
                <div class="mt-4">
                    <div class="text-sm font-semibold text-gray-900">MRZ Crop Preview</div>
                    <div class="text-xs text-gray-500">This cropped image will be used for MRZ reading.</div>
                    <img
                        src="{{ $mrzPreviewSrc }}"
                        alt="MRZ crop preview"
                        class="mt-2 h-24 rounded-lg border border-gray-200 bg-white object-contain"
                    >
                </div>
            @endif
        </div>
    @endif

    <div
        x-show="cameraOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-6xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Capture Passport Biodata</h3>
                    <p class="text-sm text-gray-500">Capture the full biodata page. Crop the MRZ after capture.</p>
                </div>

                <button
                    type="button"
                    @click="closeCamera"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Close
                </button>
            </div>

            <div class="p-6">
                <div class="mb-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Auto capture</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            <span x-text="countdown"></span>s
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 md:col-span-2">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Guide</div>
                        <div class="mt-1 text-sm text-gray-700">
                            Fit the whole passport biodata page inside the frame. Keep it flat and well lit.
                        </div>
                    </div>
                </div>

                <template x-if="captureError">
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" x-text="captureError"></div>
                </template>

                <div class="relative mx-auto overflow-hidden rounded-2xl border border-gray-300 bg-black">
                    <video
                        x-ref="video"
                        autoplay
                        playsinline
                        muted
                        class="block h-auto max-h-[72vh] w-full"
                    ></video>

                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="w-[82%] aspect-[1.42/1] rounded-3xl border-4 border-white shadow-[0_0_0_9999px_rgba(0,0,0,0.40)]"></div>
                    </div>

                    <div class="pointer-events-none absolute left-1/2 top-4 -translate-x-1/2 rounded-full bg-black/60 px-4 py-2 text-xs font-medium text-white">
                        Keep the passport page fully inside the frame
                    </div>

                    <div class="pointer-events-none absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-4 py-2 text-xs font-medium text-white">
                        After capture, crop only the MRZ lines
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="closeCamera"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="resetCountdown"
                        :disabled="uploading || isCapturing"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 disabled:opacity-60 hover:bg-gray-50"
                    >
                        Restart 10s
                    </button>

                    <button
                        type="button"
                        @click="captureImage()"
                        :disabled="uploading || isCapturing || !cameraReady"
                        class="rounded-lg bg-gray-900 px-5 py-2 text-sm font-medium text-white disabled:opacity-60 hover:bg-gray-800"
                    >
                        <span x-show="!isCapturing">Snap Now</span>
                        <span x-show="isCapturing" x-cloak>Capturing...</span>
                    </button>
                </div>

                <canvas x-ref="canvas" class="hidden"></canvas>
            </div>
        </div>
    </div>

    <div
        x-show="cropperOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-6xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Crop MRZ Section</h3>
                    <p class="text-sm text-gray-500">Drag and resize the box to cover only the MRZ lines at the bottom.</p>
                </div>

                <button
                    type="button"
                    @click="closeCropper"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Close
                </button>
            </div>

            <div class="p-6">
                <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Crop only the two machine-readable lines, not the whole passport page.
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <img x-ref="cropperImage" alt="Crop MRZ source" class="max-w-full">
                </div>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="closeCropper"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="saveMrzCrop"
                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    >
                        Save MRZ Crop
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (!window.passportCaptureCropper) {
    window.passportCaptureCropper = function () {
        return {
            cameraOpen: false,
            cropperOpen: false,
            stream: null,
            uploading: false,
            countdown: 10,
            countdownTimer: null,
            cropper: null,
            isCapturing: false,
            cameraReady: false,
            captureError: '',

            async openCamera() {
                try {
                    this.captureError = '';
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    });

                    this.cameraOpen = true;
                    this.cameraReady = false;
                    this.countdown = 10;

                    this.$nextTick(() => {
                        const video = this.$refs.video;
                        if (!video) {
                            this.captureError = 'Camera view could not be initialized.';
                            return;
                        }

                        video.srcObject = this.stream;

                        const onReady = () => {
                            this.cameraReady = true;
                            video.removeEventListener('loadedmetadata', onReady);
                            video.removeEventListener('canplay', onReady);
                        };

                        video.addEventListener('loadedmetadata', onReady);
                        video.addEventListener('canplay', onReady);
                        video.play().catch(() => {});
                    });

                    this.startCountdown();
                } catch (error) {
                    this.captureError = 'Unable to access camera. Allow camera permission, or use Upload Image instead.';
                    console.error(error);
                }
            },

            startCountdown() {
                this.stopCountdown();

                this.countdownTimer = setInterval(() => {
                    if (!this.cameraOpen || !this.cameraReady || this.isCapturing || this.uploading) {
                        return;
                    }

                    this.countdown--;

                    if (this.countdown <= 0) {
                        this.captureImage();
                    }
                }, 1000);
            },

            stopCountdown() {
                if (this.countdownTimer) {
                    clearInterval(this.countdownTimer);
                    this.countdownTimer = null;
                }
            },

            resetCountdown() {
                this.countdown = 10;
                this.captureError = '';
                this.startCountdown();
            },

            closeCamera() {
                this.stopCountdown();
                this.isCapturing = false;
                this.cameraReady = false;
                this.captureError = '';

                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }

                this.stream = null;
                this.cameraOpen = false;
                this.countdown = 10;
            },

            async makeBlobFromCanvas(canvas, quality = 0.78) {
                return await new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error('Canvas blob creation failed.'));
                            return;
                        }
                        resolve(blob);
                    }, 'image/jpeg', quality);
                });
            },

            setHiddenInputFile(input, file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            },

            async captureImage() {
                if (!this.cameraOpen || this.isCapturing || !this.cameraReady) {
                    return;
                }

                this.isCapturing = true;
                this.captureError = '';
                this.stopCountdown();

                try {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const hiddenInput = this.$refs.capturedFileInput;

                    if (!video || video.videoWidth <= 0 || video.videoHeight <= 0) {
                        throw new Error('Camera frame is not ready.');
                    }

                    if (!hiddenInput) {
                        throw new Error('Hidden upload input was not found.');
                    }

                    const sourceWidth = video.videoWidth;
                    const sourceHeight = video.videoHeight;

                    const cropWidth = Math.round(sourceWidth * 0.82);
                    const cropHeight = Math.round(cropWidth / 1.42);
                    const cropX = Math.round((sourceWidth - cropWidth) / 2);
                    const cropY = Math.round((sourceHeight - cropHeight) / 2);

                    const maxWidth = 1200;
                    const scale = Math.min(1, maxWidth / cropWidth);
                    const outputWidth = Math.round(cropWidth * scale);
                    const outputHeight = Math.round(cropHeight * scale);

                    canvas.width = outputWidth;
                    canvas.height = outputHeight;

                    const context = canvas.getContext('2d', { alpha: false });
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, outputWidth, outputHeight);
                    context.drawImage(
                        video,
                        cropX,
                        cropY,
                        cropWidth,
                        cropHeight,
                        0,
                        0,
                        outputWidth,
                        outputHeight
                    );

                    const blob = await this.makeBlobFromCanvas(canvas, 0.78);
                    const sizeMb = blob.size / (1024 * 1024);

                    if (sizeMb > 8) {
                        throw new Error('Captured image is too large after compression.');
                    }

                    const file = new File(
                        [blob],
                        `passport-biodata-${Date.now()}.jpg`,
                        {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        }
                    );

                    this.uploading = true;
                    this.setHiddenInputFile(hiddenInput, file);

                    setTimeout(() => {
                        this.uploading = false;
                        this.isCapturing = false;
                        this.closeCamera();
                    }, 1200);
                } catch (error) {
                    this.uploading = false;
                    this.isCapturing = false;
                    console.error(error);
                    this.captureError = error?.message || 'Capture failed. Please try again.';
                    this.resetCountdown();
                }
            },

            openCropper(src) {
                this.cropperOpen = true;

                this.$nextTick(() => {
                    const image = this.$refs.cropperImage;
                    if (!image) {
                        this.captureError = 'Cropper image could not be initialized.';
                        return;
                    }

                    image.src = src;

                    image.onload = () => {
                        if (this.cropper) {
                            this.cropper.destroy();
                        }

                        if (typeof Cropper === 'undefined') {
                            this.captureError = 'Cropper.js is not loaded. Add it to your layout first.';
                            return;
                        }

                        this.cropper = new Cropper(image, {
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.35,
                            responsive: true,
                            background: false,
                            movable: true,
                            zoomable: true,
                            scalable: false,
                            rotatable: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            data: {
                                x: image.naturalWidth * 0.08,
                                y: image.naturalHeight * 0.80,
                                width: image.naturalWidth * 0.84,
                                height: image.naturalHeight * 0.14,
                            },
                        });
                    };
                });
            },

            closeCropper() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                this.cropperOpen = false;
            },

            async saveMrzCrop() {
                if (!this.cropper) {
                    return;
                }

                try {
                    const canvas = this.cropper.getCroppedCanvas({
                        fillColor: '#fff',
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    const hiddenInput = this.$refs.mrzCropFileInput;

                    if (!hiddenInput) {
                        throw new Error('Hidden MRZ crop input was not found.');
                    }

                    const blob = await this.makeBlobFromCanvas(canvas, 0.9);

                    const file = new File(
                        [blob],
                        `passport-mrz-${Date.now()}.jpg`,
                        {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        }
                    );

                    this.setHiddenInputFile(hiddenInput, file);
                    this.closeCropper();
                } catch (error) {
                    console.error(error);
                    this.captureError = error?.message || 'Failed to save MRZ crop.';
                }
            }
        };
    }
}
</script>