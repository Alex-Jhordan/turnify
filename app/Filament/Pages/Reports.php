<?php

namespace App\Filament\Pages;

use App\Exports\TicketsExport;
use App\Models\Category;
use App\Models\Module;
use App\Models\Ticket;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected string $view = 'filament.pages.reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Filters')
                    ->description('Select the criteria to generate filtered Excel or PDF reports.')
                    ->schema([
                        DatePicker::make('start_date')
                            ->maxDate(now()),

                        DatePicker::make('end_date')
                            ->maxDate(now()),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable(),

                        Select::make('module_id')
                            ->label('Module')
                            ->options(Module::pluck('module_number', 'id'))
                            ->searchable(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('exportExcel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('exportExcel'),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->action('exportPdf'),
        ];
    }

    private function getReportFileName(): string
    {
        $appName = Str::slug(config('app.name', 'turnify'));

        return "{$appName}-tickets-report-" . now()->format('Y-m-d');
    }

    public function exportExcel()
    {
        try {
            $formData = $this->form->getState();
            $fileName = $this->getReportFileName() . '.xlsx';

            return Excel::download(new TicketsExport($formData), $fileName);
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Export failed')->danger()->send();
        }
    }

    public function exportPdf()
    {
        try {
            $formData = $this->form->getState();

            $tickets = Ticket::query()
                ->with(['category', 'module', 'user'])
                ->when($formData['start_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                ->when($formData['end_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
                ->when($formData['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
                ->when($formData['module_id'] ?? null, fn (Builder $q, $id) => $q->where('module_id', $id))
                ->get();

            $pdf = Pdf::loadView('pdf.tickets-report', ['tickets' => $tickets]);
            $fileName = $this->getReportFileName() . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $fileName
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('PDF generation failed')->danger()->send();
        }
    }
}
