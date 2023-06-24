<x-app-layout>
    {{-- {{ dd($getOldaData, $getNewData) }} --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Informations
        </h2>
    </x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 dark:text-gray-200">
        <div class="py-12 flex justify-between items-center">
            <div class="my-3">
                <p>New data:</p>
                <h4>User Token: {{ $getNewData['userToken'] }}</h4>
                <h4>Crypto Token: {{ $getNewData['cryptoToken'] }}</h4>
                <h4>Value in USD: {{ $getNewData['valueInDollar'] }} $</h4>
                <h4>Value in PEPE: {{ $getNewData['currentBalance'] }}</h4>
            </div>
            <div class="my-3">
                <p>Old data:</p>
                @if($getOldaData == null)
                <h4>It's the first time, that is why there's no old data</h4>
                @else
                <h4>User Token: {{ $getOldaData[0]->userToken }}</h4>
                <h4>Crypto Token: {{ $getOldaData[0]->tokenCrypto }}</h4>
                <h4>Value in USD: {{ $getOldaData[0]->dollar }} $</h4>
                <h4>Value in PEPE: {{ $getOldaData[0]->balance }}</h4>
                <h4>Date of old data: {{ $getOldaData[0]->updated_at }}</h4>
                @endif
            </div>
        </div>  
    </div>
    @if($getOldaData !== null)
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 dark:text-gray-200">
            <p class="text-centre">Difference</p>
            <div class="flex justify-between items-center">
                <div class="my-3">
                    <h4>One dollar for one PEPE: {{ $getNewData['valueInDollar'] / $getNewData['currentBalance'] }} $</h4>
                    <h4>Increased/Decreased (For Dollar):
                        {{ ($getNewData['valueInDollar'] - $getOldaData[0]->dollar) * ($getNewData['valueInDollar'] / $getNewData['currentBalance']) }}
                    </h4>
                    <h4>Increased/Decreased (For PEPE): {{$getNewData['currentBalance'] - $getOldaData[0]->balance }} </h4>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
