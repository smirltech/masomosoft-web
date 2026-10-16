@php use App\Enums\FraisType;use App\Models\Perception;use Carbon\Carbon; @endphp
@section('title')
    CENK - Papports
@endsection
@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="ms-3">Rapports Financiers</h1>
        </div>

        <div class="col-6">
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ route('finance') }}">Accueil</a></li>
                <li class="breadcrumb-item active">Rapports Financiers</li>
            </ol>
        </div>
    </div>

@stop
<div class="container-fluid">
    <div style="display: flex; width: 100%" class="card-header justify-content-center mb-3">
        <div class="card-tools">
            <div style="display: flex;" class="row">
                <div class="col-sm-4">
                    <div style="display: flex;" class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="date_from">Debut : </span>
                        </div>
                        <input aria-label="date_from" aria-describedby="date_from" max="{{$date_to}}"
                               placeholder="date debut" type="date" wire:model="date_from"
                               class="form-control">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="display: flex;" class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="date_to">Fin : </span>
                        </div>
                        <input max="{{Carbon::now()->endOfDay()->format('Y-m-d')}}"
                               min="{{$date_from}}"
                               aria-label="date_to" aria-describedby="date_to"
                               placeholder="date fin"
                               type="date" wire:model="date_to"
                               class="form-control">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="display: flex;" class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="section_id">Section : </span>
                        </div>
                        <select aria-label="section_id" aria-describedby="section_id" wire:model="section_id"
                                class="form-control">
                            <option value="">Toutes les sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @include('livewire.finance.rapports.modals.printable')
        </div>
        <div class="card-footer text-center">
            <button wire:click="printIt" class="btn btn-primary text-center">
                <i class="fa fa-file-pdf"></i>
                Télécharger</button>
        </div>
    </div>
</div>
