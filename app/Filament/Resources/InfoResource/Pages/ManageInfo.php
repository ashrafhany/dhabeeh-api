<?php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use App\Models\Info;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;

class ManageInfo extends Page
{
    protected static string $resource = InfoResource::class;
    protected static string $view = 'filament.resources.info-resource.pages.manage-info';
    protected static ?string $title = 'إدارة محتوى التطبيق';
    protected static ?string $navigationLabel = 'إدارة محتوى التطبيق';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public $type;
    public $content;

    public function mount()
    {
        $this->form->fill();

        if (!$this->type) {
            $this->type = 'about';
        }

        $this->loadInfo();
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    Select::make('type')
                        ->label('نوع المحتوى')
                        ->options([
                            'about' => 'عن التطبيق',
                            'privacy_policy' => 'سياسة الخصوصية',
                            'terms' => 'الشروط والأحكام',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function () {
                            $this->loadInfo();
                        }),
                    RichEditor::make('content')
                        ->label('المحتوى')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'h2',
                            'h3',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'undo',
                        ]),
                ]),
        ];
    }

    protected function loadInfo()
    {
        $info = Info::where('title', $this->type)->first();

        if ($info) {
            $this->content = $info->content;
        } else {
            // تعيين محتوى افتراضي لكل نوع
            switch ($this->type) {
                case 'about':
                    $this->content = 'هذا التطبيق خاص ببيع الذبائح وتوصيلها.';
                    break;
                case 'privacy_policy':
                    $this->content = 'سياسة الخصوصية هنا...';
                    break;
                case 'terms':
                    $this->content = 'الشروط والأحكام هنا...';
                    break;
                default:
                    $this->content = '';
            }
        }
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            $info = Info::where('title', $this->type)->first();

            if ($info) {
                $info->content = $this->content;
                $info->save();
            } else {
                Info::create([
                    'title' => $this->type,
                    'content' => $this->content
                ]);
            }

            $this->notify('success', 'تم حفظ المحتوى بنجاح');
        } catch (\Exception $e) {
            Log::error('فشل في حفظ المحتوى: ' . $e->getMessage());
            $this->notify('danger', 'حدث خطأ أثناء حفظ المحتوى');
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
