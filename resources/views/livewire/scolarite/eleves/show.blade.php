@php use App\Models\Eleve;use Carbon\Carbon, App\Enums\GraviteRetard, App\Helpers\Helpers; @endphp
@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="ms-3"><span class="fas fa-fw fa-user mr-1"></span>Élève</h1>
        </div>

        <div class="col-6">
            <ol class="breadcrumb float-right">
                <li class="breadcrumb-item"><a href="{{ route('scolarite') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('scolarite.eleves.index') }}">Élèves</a></li>
                <li class="breadcrumb-item active">{{$eleve->nom}}</li>
            </ol>
        </div>
    </div>

@stop
<div>
    @include('livewire.scolarite.eleves.modals.crud')
    @include('livewire.scolarite.eleves.modals.user')

    <div class="content mt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <x-avatar :model="$eleve"/>
                            {{-- <livewire:profile.edit-avatar-modal :model="$eleve"/>--}}
                            <h3 class="profile-username text-center">{{$eleve->fullName}}</h3>
                            <p class="text-muted text-center">CODE : {{$eleve->code}}</p>
                            <p class="text-muted text-center">CLASSE
                                :
                                @if($inscription)
                                    <a
                                        href="{{route('scolarite.classes.show', ['classe'=>$inscription?->classe])}}">{{$inscription?->classe?->shortCode??'Non encore inscrit !'}}</a>
                                @else
                                    <span>non encore inscrit cette année</span>
                                @endif
                            </p>

                            <p class="text-muted text-center">{{$inscription->categorie?->label()??'N/A'}}</p>
                            <p class="text-muted text-center">ANNÉE SCOLAIRE : {{$annee_courante?->nom??''}}</p>
                        </div>

                    </div>

                    <div class="accordion" id="accordionPerso">
                        <div class="card card-primary">
                            <div class="card-header" id="headingPerso">
                                <h3 class="card-title btn btn-link text-white" data-toggle="collapse"
                                    data-target="#infoPerso" aria-expanded="true"
                                    aria-controls="infoPerso">Information Personnelle</h3>
                                <div class="card-tools">
                                    @can('eleves.update',$eleve)
                                        <span role="button" wire:click.debounce="fillDataToModal" type="button"
                                              title="Mot de passe utilisateur" class=" ml-2 mr-2"
                                              data-toggle="modal"
                                              data-target="#edit-eleve-user-modal">
                                    <span class="fa fa-key"></span></span>
                                    @endif

                                    @can('eleves.update',$eleve)
                                        <span role="button" class="mr-1"
                                              data-toggle="modal"
                                              data-target="#edit-eleve-modal"><span
                                                class="fas fa-pen"></span></span>
                                    @endcan
                                </div>
                            </div>
                            <div id="infoPerso" class="collapse show" aria-labelledby="headingPerso"
                                 data-parent="#accordionPerso">
                                <div class="card-body">
                                    <strong><i class="fas fa-id-card-alt mr-1"></i> No. Permanent</strong>
                                    <p class="text-muted">
                                        {{$eleve->numero_permanent??''}}
                                    </p>
                                    <hr>
                                    <strong><i class="fas fa-user mr-1"></i> Nom</strong>
                                    <p class="text-muted">
                                        {{$eleve->nom}}
                                    </p>
                                    <hr>
                                    <strong><i class="fas fa-venus-mars mr-1"></i> Sexe</strong>
                                    <p class="text-muted">
                                        {{$eleve->sexe?->label()??''}}
                                    </p>
                                    <hr>
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Lieu de naissance</strong>
                                    <p class="text-muted">
                                        {{$eleve->lieu_naissance}}
                                    </p>
                                    <hr>
                                    <strong><i class="fas fa-calendar-alt mr-1"></i> Date de naissance</strong>
                                    <p class="text-muted">
                                        {{$eleve->dateNaissance()?->format('d/m/Y')??''}}
                                        <strong
                                            class="float-right badge bg-gradient-info">{{Carbon::now()->diffInYears($eleve->dateNaissance())}}
                                            ans</strong>
                                    </p>
                                    <hr>
                                    <strong><i class="fas fa-phone-alt mr-1"></i> Téléphone</strong>
                                    <p class="text-muted">{{$eleve->telephone}}</p>
                                    <hr>
                                    <strong><i class="fas fa-envelope mr-1"></i> E-mail</strong>
                                    <p class="text-muted">{{$eleve->email}}</p>
                                    <hr>
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Adresse</strong>
                                    <p class="text-muted">{{$eleve->adresse}}</p>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">État Financier ({{$annee_courante->nom}})</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Inscription : </b> <span
                                        class="float-right">{{Helpers::currencyFormat($inscription?->montant, symbol: 'Fc')}}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>À Recevoir : </b> <span
                                        class="float-right">{{Helpers::currencyFormat($inscription?->perceptionsDues, symbol: 'Fc')}}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Reçu : </b> <span
                                        class="float-right">{{Helpers::currencyFormat($inscription?->perceptionsPaid, symbol: 'Fc')}}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Solde : </b> <span class="float-right"><i
                                            class="badge bg-warning">{{Helpers::currencyFormat(($inscription?->perceptionsBalance), symbol: 'Fc')}}</i></span>
                                </li>
                            </ul>

                        </div>

                    </div>
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">État Financier Général</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>À Recevoir : </b> <span
                                        class="float-right">{{Helpers::currencyFormat($eleve->perceptionsDues, symbol: 'Fc')}}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Reçu : </b> <span
                                        class="float-right">{{Helpers::currencyFormat($eleve->perceptionsPaid, symbol: 'Fc')}}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Solde : </b> <span class="float-right"><i
                                            class="badge bg-warning">{{Helpers::currencyFormat(($eleve->perceptionsBalance), symbol: 'Fc')}}</i></span>
                                </li>
                            </ul>
                        </div>

                    </div>

                    <div class="accordion" id="accordionTuto">
                        <div class="card card-info">
                            <div class="card-header" id="headingTuto">
                                <h3 class="card-title btn btn-link text-white" data-toggle="collapse"
                                    data-target="#infoTuto" aria-expanded="true"
                                    aria-controls="infoTuto"> Responsable / Tuteur</h3>
                                @if(!$eleve->responsable_eleve)
                                    <div class="card-tools">
                                        @can('eleves.update',$eleve)
                                            <span title="Attacher" role="button" class="mr-2"
                                                  data-toggle="modal"
                                                  data-target="#attach-responsable-modal"><span
                                                    class="fas fa-plus"></span></span>
                                        @endcan
                                    </div>
                                @endif
                            </div>
                            @if($eleve->responsable_eleve)
                                <div id="infoTuto" class="collapse show" aria-labelledby="headingTuto"
                                     data-parent="#accordionTuto">
                                    <div class="card-body">
                                        <ul class="list-group list-group-unbordered mb-3">
                                            <li class="list-group-item">
                                                <b>Responsable</b> <span class="float-right">
                                                    @can('responsables.view',$eleve->responsable_eleve?->responsable)
                                                        <a
                                                            href="/scolarite/responsables/{{$eleve->responsable_eleve?->responsable?->id}}">{{$eleve->responsable_eleve?->responsable?->nom??''}}</a>
                                                    @else
                                                        {{$eleve->responsable_eleve?->responsable?->nom??''}}
                                                    @endcan
                                                </span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Relation</b>
                                                <span class="float-right">{{$eleve->responsable_eleve?->relation?->label()??''}}
                                                    @can('eleves.update',$eleve)
                                                        <span
                                                            title="Modifier" role="button" class="ml-1"
                                                            data-toggle="modal"
                                                            data-target="#edit-relation-modal">
                                                            <img src="{{ asset('images/logo.jpg') }}" alt="logo" style="width: 1em; height: 1em; object-fit: contain; vertical-align: -0.125em;">
                                                    </span>
                                                    @endcan
                                                </span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Sexe</b> <span
                                                    class="float-right">{{$eleve->responsable_eleve?->responsable?->sexe?->label()??''}}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Téléphone</b> <span
                                                    class="float-right">{{$eleve->responsable_eleve?->responsable?->telephone??''}}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>E-mail</b> <span
                                                    class="float-right">{{$eleve->responsable_eleve?->responsable?->email??''}}</span>
                                            </li>
                                            <li class="list-group-item">
                                                <b>Adresse</b> <span
                                                    class="float-right">{{$eleve->responsable_eleve?->responsable?->adresse??''}}</span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card card-primary card-tabs">
                        <div class="card-header p-0 pt-1">
                            <ul class="nav nav-tabs">
                                {{-- <li class="nav-item"><a class="nav-link active" href="#presences"
                                                         data-toggle="tab">Présences</a>
                                 </li>--}}
                                <li class="nav-item"><a class="nav-link active" href="#perceptions"
                                                        data-toggle="tab">Frais</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#devoirs"
                                                        data-toggle="tab">Devoirs</a>
                                </li>

                                <li class="nav-item"><a class="nav-link" href="#cursus"
                                                        data-toggle="tab">Cursus Scolaire</a>
                                </li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                {{-- <div class="active tab-pane" id="presences">
                                     <livewire:scolarite.eleve.presence-component :eleve="$eleve"/>
                                 </div>--}}
                                <div class=" tab-pane" id="devoirs">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>NO.</th>
                                                <th>TITRE</th>
                                                <th>COURS</th>
                                                <th>CLASSE</th>
                                                <th>ECHEANCE</th>
                                                <th>STATUS</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($devoirs as $k=>$devoir)
                                                <tr>
                                                    <td>{{ $k+1 }}</td>
                                                    <td>{{ $devoir->titre }}</td>
                                                    <td>
                                                        {{ $devoir->cours->nom }}
                                                    </td>
                                                    <td>
                                                        {{ $devoir->classe->code }}
                                                    </td>
                                                    <td>
                                                        {{ $devoir->echeance_display }}
                                                    </td>
                                                    <td>
                                            <span class="badge bg-{{$devoir->status?->variant()}}">
                                                 {{ $devoir->status?->label() }}
                                            </span>
                                                    </td>

                                                    <td>
                                                        <div class="d-flex float-right">
                                                            <a href="{{route('scolarite.devoirs.show',$devoir )}}"
                                                               title="modifier"
                                                               class="btn btn-sm btn-primary ml-2">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="active tab-pane" id="perceptions">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>DATE</th>
                                                <th>FRAIS</th>
                                                <th>RAISON</th>
                                                <th>MONTANT</th>
                                                <th>PAYÉ</th>
                                                <th>PAYÉ PAR</th>
                                                <th>PAYÉ LE</th>
                                                <th>ECHEANCE</th>
                                                <th style="width: 50px"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($eleve->perceptions as $perception)
                                                <tr class="@if($perception->montant > $perception->paid) bg- @endif table-{{GraviteRetard::color(Carbon::parse($perception->due_date))}}">
                                                    <td>{{ $perception->created_at->format('d-m-Y') }}</td>
                                                    <td>{{ $perception->frais->nom }}</td>
                                                    <td>{{ $perception->custom_property }}</td>
                                                    <td>{{Helpers::currencyFormat($perception->montant)  }}</td>
                                                    <td>{{Helpers::currencyFormat($perception->paid)  }}</td>
                                                    <td>{{  $perception->paid<=0?'':$perception->paid_by }}</td>
                                                    <td>{{ $perception->paid<=0?'':$perception->updated_at->format('d-m-Y') }}</td>
                                                    <td>{!!$perception->montant<=$perception->paid?'OK':GraviteRetard::retard(Carbon::parse($perception->due_date))!!}</td>
                                                    <td>
                                                        @if($perception->montant > $perception->paid)
                                                            <span
                                                                class="fa fa-thumbs-down text-warning"></span>
                                                        @else
                                                            <span
                                                                class="fa fa-thumbs-up text-success"></span>
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class=" tab-pane" id="cursus">
                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="timeline">
                                                @foreach($eleve->inscriptions as $inscription)
                                                    @php
                                                        $resultats =  $eleve->resultatsOfYear(annee_id:$inscription->annee_id);
                                                        $lastResultat = $resultats->last();
                                                        $mention = "Pas d'info";
                                                        if($lastResultat != null)$mention = $lastResultat?->pourcentage >= 50?'Réussite':'Échec';
                                                    @endphp
                                                    <div class="time-label">
                                                                    <span
                                                                        {{--wire:click="getSelectedInscription({{$inscription}})"--}}
                                                                        {{-- role="button"--}} class="bg-green"
                                                                        {{--data-toggle="modal"
                                                                        data-target="#edit-inscription-modal"--}}
                                                                    >{{$inscription?->classe?->shortCode}} ({{$inscription?->annee?->nom}})</span>
                                                    </div>

                                                    <div>
                                                        <i class="fas fa-clock bg-maroon"></i>
                                                        <div class="timeline-item">
                                                                            <span class="time"><i
                                                                                    class="fas fa-clock mr-1"></i>{{$lastResultat?->created_at->format('d-m-Y')}}</span>
                                                            <h3 class="timeline-header"><a>{{$mention}}</a>
                                                                avec {{$lastResultat?->pourcentage}}%</h3>
                                                            <div style="width: 100%" class="timeline-body ">
                                                                <div class="table-responsive-sm">
                                                                    <table class="table">
                                                                        <thead>
                                                                        <tr>
                                                                            <th scope="col">RÉSULTAT</th>
                                                                            <th scope="col">POURCENTAGE</th>
                                                                            <th scope="col">PLACE</th>
                                                                            <th scope="col">CONDUITE</th>
                                                                            <th scope="col"></th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        @foreach($resultats as $resultat)
                                                                            <tr>
                                                                                <th scope="row">{{$resultat->custom_property->longLabel()}}</th>
                                                                                <td>{{$resultat->pourcentage}}
                                                                                    %
                                                                                </td>
                                                                                <td>{{$resultat->place}}</td>
                                                                                <td>{{strtoupper($resultat?->conduite?->value)}}</td>
                                                                                <td>
                                                                                    <div
                                                                                        class="d-flex float-right">
                                                                                        @if($resultat->bulletin)
                                                                                            <a
                                                                                                href="{{route('media.show', $resultat->bulletin)}}"
                                                                                                target="_blank"
                                                                                                type="button"
                                                                                                title="Télécharger bulletin"
                                                                                                class="btn btn-outline-info btn-xs  ml-2">
                                                                                                        <span
                                                                                                            class="fa fa-file"></span>
                                                                                            </a>
                                                                                        @endif

                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach

                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="text-end">
                                                        <a href="{{ route('eleves.passer-classe-superieure', $eleve->id) }}"
                                                           class="btn btn-primary mt-3 px-4">
                                                            Nouvelle classe
                                                        </a>
                                                    </div>

                                                @endforeach

                                                <div>
                                                    <i class="fas fa-clock bg-gray"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

