<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="mt-6 text-right">
            <x-filament::button type="submit">
                {{ __('حفظ التغييرات') }}
            </x-filament::button>
        </div>
    </form>

    <x-filament::hr />

    <div class="mt-6">
        <h2 class="text-xl font-bold mb-4">{{ __('معاينة المحتوى') }}</h2>

        <div class="p-6 bg-white rounded-xl shadow">
            <div class="prose prose-sm sm:prose max-w-none rtl:text-right ltr:text-left">
                {!! $content !!}
            </div>
        </div>
    </div>
</x-filament::page>
