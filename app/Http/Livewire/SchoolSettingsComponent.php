<?php

namespace App\Http\Livewire;

use App\Enums\MediaType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Traits\HasLivewireAlert;
use App\Traits\WithFileUploads;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Component;

class SchoolSettingsComponent extends Component
{
    use WithFileUploads, HasLivewireAlert;

    public mixed $avatar = null;

    // mount
    public School $school;
    public Collection $permissions;

    public Collection $roles;
    protected $listeners = ['refresh' => '$refresh'];

    /**
     * @throws Exception
     */
    #[NoReturn]
    public function mount(): void
    {
        $this->school = School::first();
    }

    public function render(): Factory|View|Application
    {
        $this->title = "{$this->school->name}";
        return view('livewire.settings.school-settings')
            ->layoutData(['title' => $this->title, "contentHeaderIcon" => "fas fa-fw fa-user"]);
    }

    //submit
    #[NoReturn]
    public function submit(): void
    {

        //dd($this->school->toArray());
        $this->validate();
        $this->school->save();

        $this->flashSuccess('Paramètres de l\'école mis à jour avec succès !', URL::previous());

    }

    public function rules(): array
    {
        return [
            'school.name' => 'required|string|max:255',
            //'school.code' => 'numeric|string|max:255',
            'school.address' => 'nullable|string|max:255',
            'school.phone' => 'nullable|string|max:255',
            'school.email' => 'nullable|email|max:255',
            'school.website' => 'nullable|url|max:255',
        ];
    }
}
