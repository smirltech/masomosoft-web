@php use App\Enums\RevenuType;use App\Models\Annee;use App\Models\Depense;use App\Models\DepenseType;use App\Models\Revenu;use Illuminate\Support\Carbon; @endphp
<div>
    <div class="">
        <div style="text-align: center;" class=" justify-content-center">
            CENK FINANCE
        </div>
        <br>
    </div>
    <div class="">
        <div class="card">
            <div style="display: flex; justify-content: space-between" class="card-header">
                <div class="card-title">
                    @if($isSectionReport)
                        Rapport financier — Section : {{$selectedSectionName}}
                    @else
                        Rapport financier de la période
                    @endif
                    <div>Du {{Carbon::parse($date_from)->format('d/m/Y')}} au {{Carbon::parse($date_to)->format('d/m/Y')}} — Année scolaire : {{$anneeNom}}</div>
                </div>
                <div class="card-tools d-flex">
                    <div class="ml-2"> Compte : {{auth()->user()->name}}</div>
                    <div class="ml-2"> Date : {{Carbon::now()->format('d-m-Y H:i')}}</div>
                </div>
            </div>
            <div class="card-body m-b-5">
                <div class="row">
                    @if($isSectionReport)
                        <div class="col-12">
                            <div class="alert alert-info py-2">
                                Les revenus auxiliaires, les dépenses et les soldes consolidés sont exclus de ce rapport, car ils ne sont pas attribuables de manière fiable à cette section.
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <h4>ENTREES</h4>
                        <hr>
                        <ol class="list-group">
                            @if($perception != 0)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Perceptions</strong>
                                </li>
                                <table class="table table-bordered table-striped">
                                    <thead class="text-center">
                                    <tr class="titres text-uppercase">
                                        <th rowspan="2">Libellé</th>
                                        <th colspan="2">Fc</th>
                                        <th colspan="2">$</th>
                                    </tr>
                                    <tr class="titres">
                                        <th>Nbre</th>
                                        <th>Montant</th>
                                        <th>Nbre</th>
                                        <th>Montant</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($perceptionDetails as $detail)
                                        @if(!$detail['cdf_count'] && !$detail['usd_count'])
                                            @continue
                                        @endif
                                        <tr class="titres">
                                            <td style="text-align: left">{{$detail['label']}}</td>
                                            <td class="text-center">{{$detail['cdf_count']}}</td>
                                            <td class="text-center">{{number_format($detail['cdf_montant'])}}</td>
                                            <td class="text-center">{{$detail['usd_count']}}</td>
                                            <td class="text-center">{{number_format($detail['usd_montant'])}}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="titres">
                                        <th>Total</th>
                                        <th class="text-center">{{number_format($perceptionTotals['cdf_count'])}}</th>
                                        <th class="text-center">{{number_format($perceptionTotals['cdf_montant'])}}Fc</th>
                                        <th class="text-center">{{number_format($perceptionTotals['usd_count'])}}</th>
                                        <th class="text-center">{{number_format($perceptionTotals['usd_montant'])}}$</th>
                                    </tr>
                                    </tbody>
                                </table>
                            @endif
                            @if(!$isSectionReport && $revenuAuxiliaire != 0)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Revenus Auxiliaires</strong>
                                </li>
                                <table class="table table-bordered table-striped">
                                    <thead class="text-center text-uppercase">
                                    <tr class="titres">
                                        <th>Libellé</th>
                                        <th>Fc</th>
                                        <th>$</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $perceptionCDFCountTotal = 0;
                                        $perceptionUSDCountTotal = 0;
                                        $perceptionCDFTotal = 0;
                                        $perceptionUSDTotal = 0;
                                    @endphp
                                    @foreach(RevenuType::cases() as $k=>$type)
                                        @php
                                            $revenuQuery = Revenu::where('type', $type)
                                            ->whereDate('created_at', '>=', $date_from)
                                            ->whereDate('created_at', '<=', $date_to);

                                           // $perceptionCDFCount = $revenuQuery->clone()->cdf()->count();
                                          //  $perceptionUSDCount = $revenuQuery->clone()->usd()->count();

                                            $perceptionCDF = $revenuQuery->clone()->cdf()->sum('montant');
                                            $perceptionUSD = $revenuQuery->clone()->usd()->sum('montant');


                                            if(!$perceptionUSD && !$perceptionCDF){
                                                continue;
                                            }

                                          //  $perceptionCDFCountTotal += $perceptionCDFCount;
                                           // $perceptionUSDCountTotal += $perceptionUSDCount;
                                            $perceptionCDFTotal += $perceptionCDF;
                                            $perceptionUSDTotal += $perceptionUSD;



                                        @endphp
                                        <tr class="titres">
                                            <td style="text-align: left">{{$type->label()}}</td>
                                            <td class="text-center">{{number_format($perceptionCDF)}}</td>
                                            <td class="text-center">{{number_format($perceptionUSD)}}</td>
                                        </tr>

                                    @endforeach
                                    <tr class="titres">
                                        <th>Total</th>
                                        <th class="text-center">{{number_format($perceptionCDFTotal)}}Fc</th>
                                        <th class="text-center">{{number_format($perceptionUSDTotal)}}$</th>
                                    </tr>
                                    </tbody>
                                </table>
                            @endif
                        </ol>
                    </div>
                    @if(!$isSectionReport)
                    <div class="col-md-6">
                        <h4>SORTIES</h4>
                        <hr>
                        <table class="table table-bordered table-striped">
                            <thead class="text-center text-uppercase">
                            <tr class="titres">
                                <th>Libellé</th>
                                <th>Fc</th>
                                <th>$</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $depensesCDFTotal = 0;
                                $depensesUSDTotal = 0;
                            @endphp
                            @foreach(DepenseType::all() as $depenseType)
                                @php
                                    $depenseQuery = Depense::where('depense_type_id', $depenseType->id)
                                        ->whereDate('date', '>=', $date_from)
                                        ->whereDate('date', '<=', $date_to);

                                        $depensesUSD = $depenseQuery->clone()->usd()->sum('montant');
                                        $depensesCDF = $depenseQuery->clone()->cdf()->sum('montant');

                                        if(!$depensesUSD && !$depensesCDF){
                                            continue;
                                        }

                                        $depensesCDFTotal += $depensesCDF;
                                        $depensesUSDTotal += $depensesUSD;



                                @endphp
                                <tr class="titres">
                                    <td style="text-align: left">{{$depenseType->nom}}</td>
                                    <td class="text-center">
                                        {{number_format($depensesCDF)}}
                                    </td>
                                    <td class="text-center">
                                        {{number_format($depensesUSD)}}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="titres">
                                <th>Total</th>
                                <th class="text-center">{{number_format($depensesCDFTotal)}} Fc</th>
                                <th class="text-center">{{number_format($depensesUSDTotal)}} $</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>SOLDE</h4>
                        <hr>
                        <ol class="list-group">
                            <li class="list-group m-1
                            @if($soldeCDF < 0)
                                list-group-item-danger
                            @else
                                list-group-item-success
                            @endif
                                d-flex justify-content-between align-items-center">
                                <strong>CDF {{number_format($soldeCDF)}} </strong>
                            </li>
                            <li class="list-group m-1
                            @if($soldeUSD < 0)
                                list-group-item-danger
                            @else
                                list-group-item-success
                            @endif
                            d-flex justify-content-between align-items-center">
                                <strong>USD {{number_format($soldeUSD)}}</strong>
                            </li>


                        </ol>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <style>

        table {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: #454342;
        }


        .titres > th, .titres > td, .cotes > td {
            border: 1px solid gray;
            padding: 3px;
        }

        .titres > td, .titres > th {
            text-align: center;
            font-weight: 500;
            padding: 5px;

        }

        .titres > th {
            font-weight: bold;

        }


    </style>

</div>
