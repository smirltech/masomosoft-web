@php use App\Models\Annee; @endphp
@extends('adminlte::page')

@section('meta_tags')
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.ico') }}">
    <meta property="og:image" content="{{ asset('images/icons/logo.png') }}">
@stop

@section('title')
    {{$title ?? null}}
@endsection

@section('content_header')
    @if($title ?? null)
        <div class="row">
            <div class="col-6">
                <h1 class="ms-3"><i class="fa fa-{{$icon??null}} mr-2"></i>{{$title??null}}</h1>
            </div>

            <div class="col-6">
                <ol class="breadcrumb float-right">
                    @foreach($breadcrumbs??[] as $breadcrumb)
                        <li class="breadcrumb-item"><a href="{{$breadcrumb['url']}}">{{$breadcrumb['label']}}</a></li>
                        @if($loop->last)
                            <li class="breadcrumb-item active">{{$title}}</li>
                        @endif
                    @endforeach
                </ol>
            </div>
        </div>
    @endif
@stop

@section('content')
    <div class="p-3">
        {{ $slot }}
    </div>
@endsection

@section('footer')
    <strong>Copyright© {{ date('Y') }} {{config('app.name')}}</strong>
    {{__('All rights reserved.')}}
    <div class="float-right d-none d-sm-inline-block">
        Année scolaire : .<strong>{{Annee::encours()->nom}}</strong> | {{date('d.m.Y H:i')}}
    </div>
@stop
@push('css')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .sidebar-dark-primary {
            background-color: var(--dark) !important;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link {
            color: var(--white) !important;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--primary) !important;
        }
    </style>
@endpush
@push('js')
    @livewireChartsScripts
    <livewire:modals/>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts/>
    <x-livewire-alert::flash/>
    <x-modals::scripts/>
    <x-form::scripts/>
@endpush
