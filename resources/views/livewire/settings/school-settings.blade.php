@php
    use App\Enums\RolePermission;
    use App\Enums\UserRole;
@endphp
@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{$title}}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">{{$school->name}}</li>
                </ol>
            </div>
        </div>
    </div>
@stop
<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="d-flex align-items-center flex-column">
                            <div class="sw-13 position-relative mb-3">
                                <x-avatar :model="$school"/>
                            </div>
                            <div class="h5 mb-0">{{$school->name}}</div>
                            <div class="text-muted text-center">
                                <span class="badge badge-secondary">{{$school->code}}</span><br>
                            </div>
                            <div class="text-muted">
                                <i class="zmdi zmdi-email"></i>
                                <span
                                    class="align-middle">{{ $school->email }}                                        <i
                                        class="fa fa-check-circle"></i></span>

                            </div>
                            <div class="mt-3">
                                <ul>
                                    <li><strong>Téléphone:</strong> {{ $school->phone }}</li>
                                    <li><strong>Adresse:</strong> {{ $school->address }}</li>
                                    <li><strong>Site Web:</strong> <a href="{{ $school->website }}" target="_blank">{{ $school->website }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                @if ($errors->any())
                    <div class="alert alert-danger bg-red-100 text-red-700 p-4 rounded mb-4">
                        <strong class="font-bold">Whoops! Something went wrong:</strong>
                        <ul class="list-disc pl-5 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card mb-5">
                    <div class="card-header">
                        <h3 class="card-title">Informations de l'école</h3>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="submit"
                              class="form-horizontal">
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form::input
                                        label="Nom de l'école"
                                        placeholder="Masomo Soft"
                                        wire:model="school.name"
                                        required
                                        autocomplete="name"/>
                                </div>
                                <div class="col-md-12">
                                    <x-form::input
                                        label="Téléphone"
                                        wire:model="school.phone"
                                        placeholder="+243 123 456 789"/>
                                </div>
                                <div class="col-md-12">
                                    <x-form::input
                                        label="Email"
                                        wire:model="school.email"
                                        placeholder="school@masomosoft.com"/>
                                </div>
                                <div class="col-md-12">
                                    <x-form::input
                                        label="Addresse"
                                        wire:model="school.address"
                                        placeholder="123 Rue de l'École"/>
                                </div>
                                <div class="col-md-12">
                                    <x-form::input
                                        label="Site Web"
                                        wire:model="school.website"
                                        placeholder="https://www.masomosoft.com"/>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary btn-sm float-right">
                                        <i class="fa fa-save"></i> {{__('Save')}}
                                    </button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
