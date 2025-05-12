<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

class FilamentProductProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // تسجيل ملف CSS المخصص
        Filament::registerRenderHook(
            'head.end',
            fn (): string => '<link rel="stylesheet" href="' . asset('css/filament-custom.css') . '?v=' . md5(filemtime(public_path('css/filament-custom.css')) ?? time()) . '">'
        );

        // إضافة CSS لمعالجة الصور في لوحة التحكم Filament 2
        Filament::registerRenderHook(
            'styles.end',
            fn (): string => <<<'HTML'
            <style>
                /* تحسين عرض الصور في الجدول */
                .product-image {
                    object-fit: cover !important;
                    width: 48px !important;
                    height: 48px !important;
                    border-radius: 50% !important;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }

                /* إصلاح ارتفاع الصورة في الجدول */
                .filament-tables-image-column .filament-tables-image-column__image {
                    max-height: 48px !important;
                }

                /* معالجة الصور المكسورة */
                .broken-image {
                    filter: grayscale(1);
                    opacity: 0.8;
                    border: 1px dashed #ccc;
                }

                /* تحسين معاينة الصور المحملة */
                .filament-preview-image {
                    max-width: 150px;
                    border-radius: 8px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    transition: transform 0.2s;
                }

                .filament-preview-image:hover {
                    transform: scale(1.05);
                }
            </style>
            HTML
        );

        // تسجيل ملف JavaScript المخصص للتعامل مع الصور
        Filament::registerRenderHook(
            'scripts.start',
            fn (): string => '<script src="' . asset('js/filament-images.js') . '?v=' . md5(filemtime(public_path('js/filament-images.js')) ?? time()) . '"></script>'
        );
    }
}
