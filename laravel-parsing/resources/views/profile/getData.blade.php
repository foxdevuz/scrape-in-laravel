<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Informations    
        </h2>
    </x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="py-12 flex justify-between items-center">
            <div class="my-3">
                <p>New data:</p>
                <h4>Token: {{ $getNewData['cryptoToken'] }}</h4>
                <h4>Value in USD: {{ $getNewData['valueInDollar'] }} $</h4>
                <h4>Value in PEPE: {{ $getNewData['currentBalance'] }}</h4>
            </div>
            <div class="my-3">
                <p>Old data:</p>
                    @if($getOldaData == null)
                        <h4>It's the first time, that is why there're no old data</h4>
                     @else 
                        <h4>Token: {{ $getOldaData->tokenCrypto }}</h4>
                        <h4>Value in USD: {{ $getOldaData->value_dollar }} $</h4>
                        <h4>Value in PEPE: {{ $getOldaData->balance }}</h4>
                        <h4>Date of old data: {{ $getOldaData->updated_at }}</h4>
                    @endif
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <p class="text-centre">Difference | Benefit | Harm</p>
        <div class="py-12 flex justify-between items-center">
            <div class="my-3">
                <h4>One dollar for one PEPE: {{ $getNewData['currentBalance'] / $getNewData['valueInDollar'] }} $</h4>
                <h4>Increased/Decreased: (For Dollar) {{ (($getNewData['valueInDollar'] - $getOldaData->balance )/$getOldaData->balance)*100 }} %</h4>
                <h4>Increased/Decreased: (For PEPE) {{ (($getNewData['currentBalance'] - $getOldaData->value_dollar )/$getOldaData->value_dollar)*100 }} %</h4>
            </div>
        </div>
    </div>
</x-app-layout>
