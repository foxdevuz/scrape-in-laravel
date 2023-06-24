{{-- {{ dd($telegramNotification) }} --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- show messages --}}
    @if(session('success'))
        <div class="alert alert-success" style="text-align: center; color:#034e03; font-size: 1rem; margin-top: 5px;">
            {{ session('success') }}
        </div>
    @elseif (session('error1'))
        <div class="alert alert-success" style="text-align: center; color:#ff0000; font-size: 1rem; margin-top: 5px;">
            {{ session('error1') }}
        </div>
    @endif
    {{-- show messages end --}}

    <div class="py-12 dark:text-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="/getdata">
                @csrf
                <div>
                    <x-input-label for="target" :value="__('Url')" />
                    <x-text-input id="target" class="block mt-1 w-full" type="text" name="targetUrl" placeholder="Enter target url to parse" required/>

                    <x-input-error :messages="$errors->get('error')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-primary-button class="ml-3">
                        Get data
                    </x-primary-button>
                </div>
            </form>

            <div id="forms"> 
                <div id="cryptos">
                            
                </div>
            </div>
            
            <select id="select_page" style="width:30%; margin-top:20px;" class="rounded w-100 operator block font-medium text-sm text-gray-700 dark:text-gray-300"> 
                <option value="">Search crypto</option>
                @for($i = 0; $i < $number; $i++)
                    @foreach ($tokenHref[$i] as $index => $item1)
                        <option value="{{ $item1->href }}">{{ $extractedData[$i][$index] }}</option>
                    @endforeach
                @endfor
            </select>
        </div>
    </div>

    @if(!$telegramNotification == null)
        <div class="py-12 dark:text-gray-200">
            <table style="width:100%; text-align:left;" >
                <tr style="text-align: center;">
                    <th>User's token</th>
                    <th>Crypto token</th>
                    <th>Action</th>
                </tr>
                    @foreach ($telegramNotification as $item )
                        <tr style="text-align: center;">
                            <td>{{ $item->user }}</td>
                            <td>{{ $item->cryptoName }}</td>
                            <td><a href="/deleteSub?name={{ $item->tokens }}&user_id={{ $item->user }}"><i class="fa-solid fa-trash"></i></a></td>
                        </tr>
                    @endforeach
            </table>
        </div>
    @endif
    
</x-app-layout>
