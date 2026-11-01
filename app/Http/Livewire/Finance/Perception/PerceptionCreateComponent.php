<?php

namespace App\Http\Livewire\Finance\Perception;

use App\Enums\FraisType;
use App\Http\Livewire\BaseComponent;
use App\Models\Frais;
use App\Models\Inscription;
use App\Models\Perception;
use App\Traits\HasLivewireAlert;
use App\Traits\TopMenuPreview;
use App\View\Components\AdminLayout;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Auditable;

class PerceptionCreateComponent extends BaseComponent
{
    use TopMenuPreview;
    use HasLivewireAlert;

    public $searchCode;

    public $annee_id;
    public $user_id;
    public $fee_id;
    public ?Frais $fee = null;
    public $montant;
    public $paid;
    public $paid_by;
    public $eleveNom;
    public $classe_id;
    public $due_date;
    public $inscription;
    protected $rules = [
        'inscription_id' => 'nullable',
        'inscription.full_name' => 'nullable',
    ];

    protected $listeners = ['refreshComponent' => '$refresh'];
    private $frais = [];
    private $inscriptions = [];
    public Perception $perception;

    public function mount(Inscription $inscription, string $perception_id = null): void
    {
        $this->authorize('create', Perception::class);

        $this->perception = $perception_id ? Perception::find($perception_id) : new Perception();
        $this->inscription = $inscription;
        $this->frais = $this->buildFrais();

    }


    //updatedFeeId
    public function updatedPerceptionFraisId($value): void
    {
        $this->fee = Frais::find($value);
        $this->feeSelected($value);
    }

    public function feeSelected($value): void
    {
        $this->fee = Frais::find($value);
        $this->perception->frais_montant = $this->fee->montant ?? null;
        $this->perception->devise = $this->fee->devise ?? null;

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.finance.perceptions.create',
            ['inscriptions' => $this->inscriptions, 'frais' => $this->frais])
            ->layout(AdminLayout::class, ['title' => 'Nouvelle Perception']);

    }

    public function requiresRate(): bool
    {
        return $this->perception->devise != Frais::find($this->perception->frais_id)->devise;
    }

    public function submit(): void
    {
        $this->validate();
        $this->perception->inscription_id = $this->inscription->id;
        $this->perception->save();

        // $this->flashSuccess("Frais imputé avec succès !", route('finance.perceptions.print', $this->perception->id));
        $this->success("Frais imputé avec succès !");
        $this->emit('refreshComponent');
        $this->emit('hideModal');

    }


    public function rules(): array
    {
        return [
            'perception.frais_id' => 'required',
            'perception.montant' => 'required|numeric',
            'perception.frais_montant' => 'required|numeric',
            'perception.devise' => 'required',
            'perception.paid_by' => 'nullable',
            'perception.taux' => ['numeric', 'nullable'],
            //if frais, frais type is minerval
            'perception.custom_property' => Rule::requiredIf(function () {
                return Frais::find($this->perception->frais_id)->type == FraisType::MINERVAL;
            }),
        ];
    }

    private function buildFrais(): array
    {
        $frais = [];
        foreach (Frais::orderBy('nom')->get() as $f) {
            if ($f->section && $this->inscription->section?->code != $f->section) {
                continue;
            }

            if ($f->option_id && $this->inscription->option?->id != $f->option_id) {
                continue;
            }

            $frais[] = $f;
        }
        return $frais;
    }

}
