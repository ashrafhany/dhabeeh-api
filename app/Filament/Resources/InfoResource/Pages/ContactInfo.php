<?php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use App\Models\Info;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;

class ContactInfo extends Page
{
    protected static string $resource = InfoResource::class;
    protected static string $view = 'filament.resources.info-resource.pages.contact-info';
    protected static ?string $title = 'معلومات التواصل';
    protected static ?string $navigationLabel = 'معلومات التواصل';
    protected static ?string $navigationIcon = 'heroicon-o-phone';

    public $phone;
    public $email;
    public $whatsapp;
    public $facebook;
    public $twitter;
    public $instagram;
    public $address;

    public function mount()
    {
        $info = Info::where('title', 'contact')->first();

        if ($info) {
            $contactData = json_decode($info->content, true) ?: [];

            $this->phone = $contactData['phone'] ?? '';
            $this->email = $contactData['email'] ?? '';
            $this->whatsapp = $contactData['whatsapp'] ?? '';
            $this->facebook = $contactData['facebook'] ?? '';
            $this->twitter = $contactData['twitter'] ?? '';
            $this->instagram = $contactData['instagram'] ?? '';
            $this->address = $contactData['address'] ?? '';
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الجوال')
                        ->tel()
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required(),
                    Forms\Components\TextInput::make('whatsapp')
                        ->label('رقم الواتساب')
                        ->tel(),
                    Forms\Components\TextInput::make('facebook')
                        ->label('فيسبوك')
                        ->url(),
                    Forms\Components\TextInput::make('twitter')
                        ->label('تويتر')
                        ->url(),
                    Forms\Components\TextInput::make('instagram')
                        ->label('انستغرام')
                        ->url(),
                    Forms\Components\Textarea::make('address')
                        ->label('العنوان')
                        ->rows(3),
                ]),
        ];
    }

    public function save()
    {
        $contactData = [
            'phone' => $this->phone,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'address' => $this->address,
        ];

        try {
            $info = Info::where('title', 'contact')->first();

            if ($info) {
                $info->content = json_encode($contactData);
                $info->save();
            } else {
                Info::create([
                    'title' => 'contact',
                    'content' => json_encode($contactData)
                ]);
            }

            $this->notify('success', 'تم حفظ معلومات التواصل بنجاح');
        } catch (\Exception $e) {
            Log::error('فشل في حفظ معلومات التواصل: ' . $e->getMessage());
            $this->notify('danger', 'حدث خطأ أثناء حفظ المعلومات');
        }
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('حفظ التغييرات')
                ->action('save')
                ->color('primary'),
        ];
    }
}
