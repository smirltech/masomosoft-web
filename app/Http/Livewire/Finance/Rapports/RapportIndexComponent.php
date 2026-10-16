<?php

namespace App\Http\Livewire\Finance\Rapports;


use App\Models\Annee;
use App\Models\Depense;
use App\Models\Perception;
use App\Models\Revenu;
use App\Models\Section;
use App\Enums\Devise;
use App\Enums\FraisType;
use App\Traits\TopMenuPreview;

use App\Traits\WithPrintToPdf;
use App\View\Components\AdminLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class RapportIndexComponent extends Component
{
    use TopMenuPreview;
    use LivewireAlert;
    use WithPrintToPdf;

    public $date_from;
    public $date_to;
    public ?int $section_id = null;
    public $sections = [];
    public $selectedSectionName = null;
    public $isSectionReport = false;

    public $revenuAuxiliaire = 0;
    public $perception = 0;
    public $frais = [];
    public $perceptionDetails = [];
    public $perceptionTotals = [];
    public $depenses = 0;
    public $depensesTypes = [];
    public $soldeCDF = null;
    public $soldeUSD = null;
    public $annee_id;
    public $anneeNom;
    protected $rules = [
        'date_from' => 'nullable',
        'date_to' => 'nullable',
        'section_id' => 'nullable|integer|exists:sections,id',
    ];
    private $annee;

    public function mount()
    {
        $this->annee = Annee::encours();
        $this->annee_id = $this->annee->id;
        $this->anneeNom = $this->annee->nom;
        $this->date_from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_to = Carbon::now()->format('Y-m-d');
        $this->sections = Section::query()->select('id', 'nom')->orderBy('nom')->get();

    }


    public function render()
    {
        $this->loadData();
        return view('livewire.finance.rapports.index', ['annee' => $this->annee,'title' => $this->title])
            ->layout(AdminLayout::class, ['title' => $this->title]);
    }


    public function  getTitleProperty(): string
    {
        return 'Rapport financier du ' . now()->parse($this->date_from)->format('d-m-Y') . ' au ' . now()->parse($this->date_to)->format('d-m-Y');

    }
    public function loadData(): void
    {
        $this->loadReportContext();
        $this->revenuAuxiliaire = Revenu::sommeBetween(annee_id: $this->annee_id, ddebut: $this->date_from, dfin: $this->date_to);
        $this->loadPerceptionData();

        $this->depenses = Depense::sommeBetween(annee_id: $this->annee_id, ddebut: $this->date_from, dfin: $this->date_to);
        $this->depensesTypes = Depense::sommeDepensesByTypeBetween(annee_id: $this->annee_id, ddebut: $this->date_from, dfin: $this->date_to);

        if ($this->isSectionReport) {
            $this->soldeCDF = null;
            $this->soldeUSD = null;

            return;
        }

        $this->loadGlobalBalances();
    }

    private function loadGlobalBalances(): void
    {
        $this->soldeCDF = Perception::cdf()->sum('montant')
            + Revenu::cdf()->sum('montant')
            - Depense::cdf()->sum('montant');
        $this->soldeUSD = Perception::usd()->sum('montant')
            + Revenu::usd()->sum('montant')
            - Depense::usd()->sum('montant');
    }

    private function loadReportContext(): void
    {
        $section = collect($this->sections)->firstWhere('id', $this->section_id);

        $this->selectedSectionName = data_get($section, 'nom');
        $this->isSectionReport = $this->section_id !== null && $this->selectedSectionName !== null;
    }

    private function loadPerceptionData(): void
    {
        $details = collect(FraisType::cases())
            ->mapWithKeys(fn (FraisType $type) => [$type->value => [
                'label' => $type->label(),
                'cdf_count' => 0,
                'cdf_montant' => 0,
                'usd_count' => 0,
                'usd_montant' => 0,
            ]])
            ->all();

        $perceptions = Perception::query()
            ->without('frais')
            ->select(
                'frais.type as frais_type',
                'perceptions.devise',
                DB::raw('COUNT(perceptions.id) as count'),
                DB::raw('COALESCE(SUM(perceptions.montant), 0) as montant'),
            )
            ->join('frais', 'frais.id', '=', 'perceptions.frais_id')
            ->where('perceptions.annee_id', $this->annee_id)
            ->whereBetween('perceptions.created_at', [
                Carbon::parse($this->date_from)->startOfDay(),
                Carbon::parse($this->date_to)->endOfDay(),
            ])
            ->when($this->section_id !== null, function ($query) {
                $query->whereHas('inscription.classe', function ($query) {
                    $query->where('section_id', $this->section_id);
                });
            })
            ->groupBy('frais.type', 'perceptions.devise')
            ->get();

        foreach ($perceptions as $perception) {
            $fraisType = $perception->frais_type;
            $devise = $perception->devise instanceof Devise ? $perception->devise->value : $perception->devise;
            $prefix = strtolower($devise);

            $details[$fraisType] ??= [
                'label' => FraisType::tryFrom($fraisType)?->label() ?? $fraisType,
                'cdf_count' => 0,
                'cdf_montant' => 0,
                'usd_count' => 0,
                'usd_montant' => 0,
            ];

            $details[$fraisType]["{$prefix}_count"] = (int) $perception->count;
            $details[$fraisType]["{$prefix}_montant"] = (float) $perception->montant;
        }

        $this->perceptionDetails = array_values($details);
        $this->perceptionTotals = [
            'cdf_count' => array_sum(array_column($this->perceptionDetails, 'cdf_count')),
            'cdf_montant' => array_sum(array_column($this->perceptionDetails, 'cdf_montant')),
            'usd_count' => array_sum(array_column($this->perceptionDetails, 'usd_count')),
            'usd_montant' => array_sum(array_column($this->perceptionDetails, 'usd_montant')),
        ];
        $this->perception = $this->perceptionTotals['cdf_montant'] + $this->perceptionTotals['usd_montant'];
        $this->frais = collect($this->perceptionDetails)
            ->mapWithKeys(fn (array $detail) => [$detail['label'] => $detail['cdf_montant'] + $detail['usd_montant']])
            ->all();
    }

    public function updateReport()
    {

    }

    public function updatedSectionId(): void
    {
        $this->validateOnly('section_id');
    }

    public function printIt(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        //$this->dispatchBrowserEvent('printIt', ['elementId' => "factPrint", 'type' => 'html', 'maxWidth' => '100%']);

        $this->validateOnly('section_id');

        return $this->printToPdf('livewire.finance.rapports.modals.printable', $this->all(), $this->title.'.pdf');

    }
}
