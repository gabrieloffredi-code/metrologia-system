<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laboratório:') }} {{ $laboratory->name }} 
                <span class="text-sm text-indigo-600">({{ $role }})</span>
            </h2>
            
            {{-- Botão Gerenciar Membros (Apenas ADM) --}}
            @if (Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM'))
                <a href="{{ route('laboratories.members', $laboratory) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400">
                    Gerenciar Membros
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Peças do Laboratório</h3>
                
                {{-- Ações para EDITOR/ADM --}}
                @if (Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR'))
                    <div class="mb-4">
                        {{-- Rota para criação de nova peça --}}
                        <a href="{{ route('laboratories.assets.create', $laboratory) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            + Adicionar Nova Peça
                        </a>
                    </div>
                @endif

                {{-- Tabela de Peças (Assets) --}}
                <div class="overflow-x-auto">
                    @forelse ($assets as $asset)
                        {{-- Inicio da Tabela --}}
                        @if ($loop->first)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome da Peça</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Calibração</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                        @endif

                                <tr>
                                    {{-- CAMPO CORRIGIDO: NOME DA PEÇA --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->name }}</td>
                                    
                                    {{-- CAMPO CORRIGIDO: DATA DA ÚLTIMA CALIBRAÇÃO --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php
                                            // Busca a calibração mais recente para exibir a data
                                            $lastCalibration = $asset->calibrations->sortByDesc('date')->first();
                                        @endphp
                                        
                                        {{ $lastCalibration ? $lastCalibration->date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{-- AÇÕES NA TABELA DE ASSETS --}}
                                        <a href="{{ route('laboratories.assets.show', ['laboratory' => $laboratory, 'asset' => $asset]) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 mr-3">Detalhes</a>
                                        
                                        @if (Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR'))
                                            <a href="{{ route('laboratories.assets.edit', ['laboratory' => $laboratory, 'asset' => $asset]) }}" 
                                               class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</a>
                                            {{-- O formulário de exclusão deve ser feito em assets.show ou usando JS --}}
                                        @endif
                                    </td>
                                </tr>

                        @if ($loop->last)
                                </tbody>
                            </table>
                        @endif
                    @empty
                        <p class="text-gray-500">Nenhuma peça cadastrada neste laboratório.</p>
                    @endforelse
                </div>

                {{-- Exibição de Membros (Simples) --}}
                <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4 border-b pb-2">Membros Atuais</h3>
                <p class="text-gray-600">
                    Membros: 
                    @foreach ($laboratory->members as $member)
                        {{ $member->name }} ({{ $member->pivot->role }})@if (!$loop->last), @endif
                    @endforeach
                </p>

            </div>
        </div>
    </div>
</x-app-layout>