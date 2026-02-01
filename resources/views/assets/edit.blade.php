<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Peça:') }} {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Rota aninhada: laboratories.assets.update --}}
                    <form method="POST" action="{{ route('laboratories.assets.update', ['laboratory' => $laboratory, 'asset' => $asset]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Indica que é uma requisição de atualização --}}

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nome da Peça / Identificação')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $asset->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="reference" :value="__('Referência / Modelo (Opcional)')" />
                            <x-text-input id="reference" class="block mt-1 w-full" type="text" name="reference" :value="old('reference', $asset->reference)" />
                            <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <x-input-label for="unit" :value="__('Unidade de Medida (Ex: mm, °C)')" />
                                <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit', $asset->unit)" required />
                                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="nominal_value" :value="__('Valor Nominal (Opcional)')" />
                                <x-text-input id="nominal_value" class="block mt-1 w-full" type="number" step="0.0001" name="nominal_value" :value="old('nominal_value', $asset->nominal_value)" placeholder="Ex: 10.0000" />
                                <x-input-error :messages="$errors->get('nominal_value')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="image_path" :value="__('Foto da Peça (Opcional)')" />
                            <input id="image_path" name="image_path" type="file" 
                            accept="image/*" capture="environment"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                            <x-input-error :messages="$errors->get('image_path')" class="mt-2" />
                            @if ($asset->image_path)
                                <p class="text-xs text-gray-500 mt-1">Imagem atual: {{ basename($asset->image_path) }}</p>
                            @endif
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Atualizar Peça') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>