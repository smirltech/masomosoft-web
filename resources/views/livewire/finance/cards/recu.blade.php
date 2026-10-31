@php use App\Helpers\Helpers;use Carbon\Carbon; @endphp
<div id="factPrint" class="text-center" style="text-align:center">
    <div style="text-align:center" class="text-center">
        <img width="80px" src="{{$school->avatar}}" alt="cenk logo">
    </div>
    <strong>{{$school->name}}</strong>
    <div class="text-center"  style="margin-bottom: 10px">
        <span class="">Adresse:{{$school->address}}</span>
    </div>
    <div  class="text-center" style="margin-bottom: 10px"><span class="">Telephone:{{$school->phone}}</span>
        <span  class="">Email:{{$school->email}}</span>
    </div>

    <div style="text-align:center" class="text-center">REÇU N°{{$perception?->reference}}</div>
    <div style="text-align:center" class="text-center">
        Date: {{Carbon::now()->format("d-m-Y à H:i:s")}}
    </div>
    <br>
    <div style="text-align:center" class="text-center">Élève :
        <strong>{{$inscription?->eleve->fullName}}</strong></div>
    <br>
    <div style="text-align:center; width: 100%">
                            <span style="text-align:left; margin-right: 10px"
                                  class="">{{$inscription?->classe?->niveau?->label()}} </span>
        <span style="text-align:center; margin-right: 10px"
              class=""> {{$inscription?->classe->parent->nom}} </span>
        <span style="text-align:right" class=""> {{$annee->nom}}</span>

    </div>
    <br>
    <div class="d-flex  justify-content-evenly">
        <strong class="">{{$perception->frais->nom}}</strong>
    </div>
    <br>
    <div class="table-responsive">
        <table style="width:100%" class="table">
            <thead>
            <tr>
                <th>MONTANT DU</th>
                <th>MONTANT PAYE</th>
            </tr>
            </thead>
            <tbody>
            <tr class="text-dark">
            <tr>
                <td>
                    {{number_format($perception->frais_montant)}} {{ $perception->frais->devise }}
                </td>
                <td>
                    {{number_format($perception->montant)}} {{ $perception->frais->devise }}
                </td>
                <td>
            </tr>
            </tbody>
        </table>
    </div>
    <hr>
    <div class="text-right">Total :
        <strong>{{Helpers::currencyFormat($perception?->montant)}}</strong></div>
    <div style="text-align:right" class="text-right">Cash :
        <strong>{{Helpers::currencyFormat($perception?->montant)}} {{ $perception->frais->devise }}</strong></div>
    <div style="text-align:right" class="text-right">Reste :
        <strong>{{Helpers::currencyFormat($perception?->reste)}}
            {{ $perception->frais->devise }} </strong>
    </div>
    @if($perception?->paid_by != null)
        <div style="text-align:right" class="text-right">Payé par :
            <strong>{{$perception?->paid_by}}</strong></div>
    @endif
    <br>

    <div class="w3-small text-center mt-5">
        <img width="80px" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{route('perceptions.verify', $perception)}}"
             alt="QR Code">
    </div>
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <script>

        printJS({
            printable: 'factPrint',
            type: 'html',
            targetStyles: ['*'],
            maxWidth: 300,
            style: "text-align:center",
            onPrintDialogClose: redirectBack
        });


        window.onafterprint = function () {
            redirectBack();
        }

        function redirectBack() {
            location.replace("{{URL::previous()}}");
        }

    </script>
</div>
