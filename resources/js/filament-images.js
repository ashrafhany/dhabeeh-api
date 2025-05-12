/**
 * تحسينات خاصة بعرض الصور في لوحة تحكم Filament 2
 */
document.addEventListener('DOMContentLoaded', () => {
    // تحديث الصور وإضافة معالجة للصور المكسورة
    function refreshImages() {
        document.querySelectorAll('.filament-tables-image-column img, .product-image').forEach(img => {
            // حفظ المسار الأصلي (بدون المعلمات)
            let originalSrc = img.src.split('?')[0];

            // إضافة timestamp لمنع التخزين المؤقت
            img.src = originalSrc + '?v=' + new Date().getTime();

            // التأكد من تطبيق الأنماط
            img.classList.add('product-image');

            // معالجة الصور المكسورة
            img.onerror = function() {
                this.onerror = null; // منع التكرار المستمر
                this.src = '/images/placeholder-product.jpg';
                this.classList.add('broken-image');
            };

            // تحديث روابط الصور
            if (img.parentElement && img.parentElement.tagName === 'A') {
                img.parentElement.href = img.src;
            }
        });

        // معالجة صور معاينة الملفات المحملة
        document.querySelectorAll('.filament-forms-file-upload-component img').forEach(img => {
            img.classList.add('filament-preview-image');
        });
    }

    // تشغيل عند تحميل الصفحة
    setTimeout(refreshImages, 200);

    // تحديث الصور عند تحديث العرض
    document.addEventListener('livewire:load', function() {
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('message.processed', () => setTimeout(refreshImages, 200));
        }
    });

    // إعادة تحميل الصور عند تبديل الصفحات أو التصفية
    document.addEventListener('turbo:load', refreshImages);
    document.addEventListener('turbolinks:load', refreshImages);

    // التعامل مع تحميل الصور الجديدة
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) {
                setTimeout(refreshImages, 200);
            }
        });
    });

    // مراقبة التغييرات في DOM
    observer.observe(document.body, { childList: true, subtree: true });
});
