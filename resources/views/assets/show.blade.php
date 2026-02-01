<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalhes da Peça:') }} {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 lg:p-8">
                
                @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                
                {{-- Informações Básicas e Ações --}}
                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Informações Básicas</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p><strong>Referência:</strong> {{ $asset->reference ?? 'N/A' }}</p>
                        <p><strong>Unidade de Medida:</strong> {{ $asset->unit }}</p>
                        <p><strong>Valor Nominal:</strong> {{ $asset->nominal_value ?? 'N/A' }}</p>
                    </div>

                    {{-- Exibição da Imagem --}}
                    <div class="md:col-span-2 mt-4 md:mt-0">
                        <h4 class="font-semibold text-gray-700 mb-3">Imagem da Peça</h4>
                        @if ($asset->image_path)
                            <img src="{{ asset('storage/' . $asset->image_path) }}" 
                                 alt="{{ $asset->name }}" 
                                 class="max-w-full h-auto rounded-lg shadow-lg"
                                 style="max-height: 200px; object-fit: cover;">
                        @else
                            <p class="text-gray-500 text-sm">Nenhuma imagem cadastrada.</p>
                        @endif
                    </div>
                </div>

                {{-- Botões de Ação --}}
                @if (Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR'))
                    <div class="mt-8 flex space-x-4">
                        <a href="{{ route('laboratories.assets.calibrations.create', ['laboratory' => $laboratory, 'asset' => $asset]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            + Realizar Nova Calibração
                        </a>
                        <a href="{{ route('laboratories.assets.edit', ['laboratory' => $laboratory, 'asset' => $asset]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400">
                            Editar Peça
                        </a>
                        {{-- Formulário de Exclusão --}}
                        <form action="{{ route('laboratories.assets.destroy', ['laboratory' => $laboratory, 'asset' => $asset]) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Deseja realmente excluir esta peça e todas as suas calibrações?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                Excluir Peça
                            </button>
                        </form>
                    </div>
                @endif
                
                <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4 border-b pb-2">Histórico de Calibrações</h3>
                
                @if ($calibrations->isEmpty())
                    <p class="text-gray-500">Nenhuma calibração registrada para esta peça.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado (Estimativa $\pm U$)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível de Confiança</th>
                                    <th class="px-6 py-3 bg-gray-50">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($calibrations as $calibration)
                                    @php
                                        // A estimativa final é Média + Correção
                                        $finalEstimate = $calibration->mean_value + $calibration->total_correction;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $calibration->date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($finalEstimate, 4) }} 
                                            &plusmn; 
                                            {{ number_format($calibration->expanded_uncertainty, 4) }} {{ $asset->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $calibration->confidence_level }}% (k={{ number_format($calibration->k_factor, 2) }})</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('laboratories.assets.calibrations.show', ['laboratory' => $laboratory, 'asset' => $asset, 'calibration' => $calibration]) }}" class="text-indigo-600 hover:text-indigo-900">Ver Relatório</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>