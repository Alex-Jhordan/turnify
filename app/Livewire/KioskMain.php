<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\IdentityLookupService;
use Illuminate\View\View;
use Livewire\Component;

class KioskMain extends Component
{
    public string $document_type = 'dni';

    public string $document_number = '';

    public string $name = '';

    public bool $name_found = false;

    public bool $is_searching = false;

    public ?int $category_id = null;

    public bool $is_priority = false;

    public string $idempotency_key = '';

    public int $step = 1;

    public function mount(): void
    {
        $this->idempotency_key = (string) str()->uuid();
    }

    public function appendDigit(string $digit): void
    {
        if (! preg_match('/^\d$/', $digit) || strlen($this->document_number) >= 12) {
            return;
        }

        $this->document_number .= $digit;
    }

    public function removeDigit(): void
    {
        $this->document_number = substr($this->document_number, 0, -1);
    }

    public function nextStep(IdentityLookupService $service): void
    {
        $this->validateCurrentStep();

        if ($this->step === 1) {
            $this->is_searching = true;
            
            $lookupName = $service->lookup($this->document_type, $this->document_number);

            if ($lookupName) {
                $this->name = $lookupName;
                $this->name_found = true;
            } else {
                $this->name = '';
                $this->name_found = false;
            }

            $this->is_searching = false;
        }

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    private function validateCurrentStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'document_type' => ['required', 'in:dni,passport,ce'],
                'document_number' => ['required', 'digits_between:6,12'],
            ]);
        }

        if ($this->step === 2) {
            $this->validate([
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'category_id' => ['required', 'integer', 'exists:categories,id'],
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.kiosk-main', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
