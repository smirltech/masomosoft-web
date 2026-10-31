<?php

namespace App\Http\Livewire\Finance\Perception;

use App\Http\Livewire\BaseComponent;
use App\Models\Annee;
use App\Models\Frais;
use App\Models\Inscription;
use App\Models\Perception;
use App\Models\School;
use App\Traits\HasLivewireAlert;
use App\Traits\TopMenuPreview;
use App\View\Components\AdminLayout;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use URL;

class PerceptionPrintComponent extends BaseComponent
{
    use TopMenuPreview;
    use HasLivewireAlert;

    public Perception $perception;
    public  Inscription $inscription;


    public  Annee $annee;
    public School $school;

    public function mount(Perception $perception): void
    {
        $this->authorize('update', $perception);
        $this->annee = Annee::encours();
        $this->perception = $perception;
        $this->fee = $this->perception->frais;
        $this->inscription = $this->perception->inscription;
        $this->school =School::first();
       // $this->printIt();
    }


    private function printIt(): void
    {

        $this->dispatchBrowserEvent('printIt', ['elementId' => "factPrint", 'type' => 'html', 'maxWidth' => 301]);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.finance.cards.recu')
            ->layout(AdminLayout::class, ['title' => "RECU N° " . $this->perception->reference]);
    }
}
