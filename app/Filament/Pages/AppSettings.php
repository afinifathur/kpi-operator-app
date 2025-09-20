<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AppSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Konfigurasi';
    protected static ?string $title           = 'App Settings';
    protected static string $view             = 'filament.pages.app-settings';

    // form state
    public ?int $near_threshold_pct = null;
    public ?int $shift_minutes_default = null;
    public ?string $theme = 'light'; // light/dark

    public function mount(): void
    {
        $this->near_threshold_pct   = (int) Setting::getValue('near_threshold_pct', 80);
        $this->shift_minutes_default= (int) Setting::getValue('shift_minutes_default', 420);
        $this->theme                = (string) Setting::getValue('ui_theme', 'light');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('near_threshold_pct')
                ->label('Threshold MENDEKATI (%)')
                ->numeric()->minValue(50)->maxValue(150)->required()
                ->helperText('Batas bawah 80–<100% = MENDEKATI'),
            Forms\Components\TextInput::make('shift_minutes_default')
                ->label('Default Menit per Shift')
                ->numeric()->minValue(240)->maxValue(720)->required()
                ->helperText('Misal 420 menit = 7 jam kerja efektif'),
            Forms\Components\Select::make('theme')
                ->label('Tema UI')
                ->options(['light'=>'Light','dark'=>'Dark'])
                ->native(false),
        ])->columns(2);
    }

    public function save(): void
    {
        Setting::putValue('near_threshold_pct', $this->near_threshold_pct);
        Setting::putValue('shift_minutes_default', $this->shift_minutes_default);
        Setting::putValue('ui_theme', $this->theme);

        // (opsional) segera terapkan tema ke panel (jika kamu implementasikan)
        // saat ini hanya disimpan; tema default panel sudah "light"

        Notification::make()->title('Settings disimpan')->success()->send();
    }
}
