<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
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
        </div>
    </div>
</x-app-layout>
