<?php

use App\Enums\Devise;
use App\Enums\FraisType;
use App\Http\Livewire\Finance\Rapports\RapportIndexComponent;
use App\Models\Annee;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Frais;
use App\Models\Inscription;
use App\Models\Perception;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

function createReportPerception(User $user, Annee $annee, Classe $classe, Frais $frais, string $createdAt, float $montant): Perception
{
    $eleve = Eleve::create([
        'nom' => 'Élève ' . Str::uuid(),
        'matricule' => Str::upper(Str::random(12)),
    ]);

    $inscription = Inscription::create([
        'eleve_id' => $eleve->id,
        'classe_id' => $classe->id,
        'annee_id' => $annee->id,
        'montant' => 0,
    ]);

    return Perception::create([
        'user_id' => $user->id,
        'frais_id' => $frais->id,
        'inscription_id' => $inscription->id,
        'montant' => $montant,
        'devise' => Devise::CDF,
        'frais_montant' => $montant,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->annee = Annee::create([
        'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-31',
        'encours' => true,
    ]);
    $this->autreAnnee = Annee::create([
        'date_debut' => '2025-09-01',
        'date_fin' => '2026-07-31',
        'encours' => false,
    ]);

    $this->sectionA = Section::create(['nom' => 'Section A', 'code' => 'A']);
    $this->sectionB = Section::create(['nom' => 'Section B', 'code' => 'B']);
    $this->classeA = Classe::create(['code' => 'A-1', 'niveau' => 1, 'section_id' => $this->sectionA->id]);
    $this->classeB = Classe::create(['code' => 'B-1', 'niveau' => 1, 'section_id' => $this->sectionB->id]);
    $this->frais = Frais::create([
        'nom' => 'Minerval',
        'montant' => 100,
        'type' => FraisType::MINERVAL,
        'devise' => Devise::CDF,
        'annee_id' => $this->annee->id,
    ]);

    createReportPerception($this->user, $this->annee, $this->classeA, $this->frais, '2026-10-01 00:00:00', 100);
    createReportPerception($this->user, $this->annee, $this->classeA, $this->frais, '2026-10-31 23:59:59', 150);
    createReportPerception($this->user, $this->annee, $this->classeB, $this->frais, '2026-10-31 23:59:59', 200);
    createReportPerception($this->user, $this->annee, $this->classeA, $this->frais, '2026-09-30 23:59:59', 300);

    $perceptionAutreAnnee = createReportPerception($this->user, $this->annee, $this->classeA, $this->frais, '2026-10-15 12:00:00', 400);
    $perceptionAutreAnnee->update(['annee_id' => $this->autreAnnee->id]);
});

it('conserves all sections while respecting the reporting period and school year', function () {
    Livewire::test(RapportIndexComponent::class)
        ->set('date_from', '2026-10-01')
        ->set('date_to', '2026-10-31')
        ->assertSet('section_id', null)
        ->assertSet('perceptionTotals.cdf_count', 3)
        ->assertSet('perceptionTotals.cdf_montant', 450.0);
});

it('includes only perceptions from the selected section', function () {
    Livewire::test(RapportIndexComponent::class)
        ->set('date_from', '2026-10-01')
        ->set('date_to', '2026-10-31')
        ->set('section_id', $this->sectionA->id)
        ->assertSet('perceptionTotals.cdf_count', 2)
        ->assertSet('perceptionTotals.cdf_montant', 250.0);
});

it('rejects an unknown section identifier', function () {
    Livewire::test(RapportIndexComponent::class)
        ->set('section_id', 999999)
        ->assertHasErrors(['section_id' => 'exists']);
});

it('keeps the global report headings and downloads the shared printable view', function () {
    Livewire::test(RapportIndexComponent::class)
        ->set('date_from', '2026-10-01')
        ->set('date_to', '2026-10-31')
        ->assertSee('Rapport financier de la période')
        ->assertSee('SORTIES')
        ->assertSee('SOLDE')
        ->call('printIt')
        ->assertFileDownloaded();
});

it('renders a section report without non-attributable global amounts', function () {
    Livewire::test(RapportIndexComponent::class)
        ->set('date_from', '2026-10-01')
        ->set('date_to', '2026-10-31')
        ->set('section_id', $this->sectionA->id)
        ->assertSee('Section A')
        ->assertSee('Les revenus auxiliaires')
        ->assertDontSee('SORTIES')
        ->assertDontSee('SOLDE')
        ->call('printIt')
        ->assertFileDownloaded();
});

it('excludes soft-deleted perceptions from global totals and balances', function () {
    $perceptionSupprimee = createReportPerception(
        $this->user,
        $this->annee,
        $this->classeA,
        $this->frais,
        '2026-10-15 12:00:00',
        50
    );
    $perceptionSupprimee->delete();

    Livewire::test(RapportIndexComponent::class)
        ->set('date_from', '2026-10-01')
        ->set('date_to', '2026-10-31')
        ->assertSet('perceptionTotals.cdf_count', 3)
        ->assertSet('perceptionTotals.cdf_montant', 450.0)
        ->assertSet('soldeCDF', 1150.0);
});
