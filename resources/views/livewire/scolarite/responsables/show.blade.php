@php
    use App\Enums\InscriptionStatus;
    use App\Helpers\Helpers;use App\Models\Annee;
@endphp
@section('title')
    - responsable - {{$responsable->nom}}
@endsection
@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="ms-3"><span class="fas fa-fw fa-person-pregnant mr-1"></span>Responsable</h1>
        </div>

        <div class="col-6">
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ route('scolarite') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('scolarite.responsables.index') }}">Responsables</a></li>
                <li class="breadcrumb-item active">{{$responsable->nom}}</li>
            </ol>
        </div>
    </div>

@stop
<div class="  wire:ignore.self">
    @include('livewire.scolarite.responsables.modals.crud')
    @include('livewire.scolarite.responsables.modals.user')

    <div class="content mt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h1 class="card-title">{{$responsable->nom}}</h1>
                            <div class="card-tools">
                                @can('responsables.update',$responsable)
                                    <span role="button" wire:click.debounce="fillDataToModal" type="button"
                                          title="Mot de passe utilisateur" class=" ml-2" data-toggle="modal"
                                          data-target="#edit-responsable-user-modal">
                                    <span class="fa fa-key"></span></span>
                                @endcan
                                @can('responsables.update',$responsable)
                                    <span role="button" wire:click.debounce="fillDataToModal" type="button"
                                          title="Modifier" class=" ml-4" data-toggle="modal"
                                          data-target="#edit-responsable-modal">
                                    <span class="fa fa-pen"></span></span>
                                @endcan
                                @can('responsables.delete',$responsable)
                                    <span role="button" type="button"
                                          title="supprimer" class=" ml-4 mr-2" data-toggle="modal"
                                          data-target="#delete-responsable-modal">
                                    <span class="fa fa-trash"></span>
                                </span>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Nom : </b> <span class="float-right">{{ $responsable->nom }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Sexe : </b> <span
                                        class="float-right">{{ strtoupper($responsable->sexe->value) }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Phone : </b> <span class="float-right"><a
                                            href="tel:{{ $responsable->telephone }}">{{ $responsable->telephone }}</a></span>
                                </li>
                                <li class="list-group-item">
                                    <b>E-mail : </b> <span class="float-right"><a
                                            href="mailto:{{ $responsable->email }}">{{ $responsable->email }}</a>@if($responsable->user != null)
                                            <span class="ml-4 fa fa-thumbs-up"></span>
                                        @endif  </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Enfants : </b> <span
                                        class="float-right">{{ $responsable->responsable_eleves->count() }}</span>
                                </li>
                            </ul>

                            <div class="">
                                <label>Adresse : </label>
                                {{ $responsable->adresse }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="m-0"><span class="fas fa-fw fa-children mr-1"></span>Enfants</h3>
                            </div>
                            <div class="card-tools d-flex my-auto">
                                {{--<button type="button"
                                        class="btn btn-primary  ml-2" data-toggle="modal"
                                        data-target="#add-classe-modal"><span
                                        class="fa fa-plus"></span></button>--}}
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive m-b-40">
                                <table class="table">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th style="width: 100px">CODE</th>
                                        <th>ENFANT</th>
                                        <th>SEXE</th>
                                        <th>AGE</th>
                                        <th>RELATION</th>
                                        <th>CLASSE</th>
                                        <th style="width: 100px"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($responsable->responsable_eleves as $responsable_eleve)
                                        @if($responsable_eleve->eleve != null)
                                            <tr>
                                                <td>{{ $responsable_eleve->eleve->code??'' }}</td>
                                                <td>{{ $responsable_eleve->eleve->fullName??'' }}</td>
                                                <td>{{ $responsable_eleve->eleve->sexe??'' }}</td>
                                                <td>{{ $responsable_eleve->eleve->age??'' }}</td>
                                                <td>{{ $responsable_eleve?->relation?->reverse($responsable_eleve->eleve->sexe??'')??'' }}
                                                    @can('eleves.update',$responsable_eleve->eleve)
                                                        <span
                                                            wire:click="selectResponsableEleve('{{$responsable_eleve->id??''}}')"
                                                            type="button"
                                                            title="Modifier Relation" class="ml-2" data-toggle="modal"
                                                            data-target="#edit-relation-modal">
                                                        <img src="{{ asset('images/logo.jpg') }}" alt="logo" style="width: 1em; height: 1em; object-fit: contain; vertical-align: -0.125em;">
                                                    </span>
                                                    @endcan
                                                </td>
                                                <td>{{ $responsable_eleve->eleve->currentInscription()->classe->code??'' }}</td>
                                                <td>
                                                    <div class="d-flex float-right">
                                                        @can('eleves.view',$responsable_eleve->eleve)
                                                            <a href="/scolarite/eleves/{{ $responsable_eleve->eleve->id??'' }}"
                                                               title="Voir"
                                                               class="btn btn-warning">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
