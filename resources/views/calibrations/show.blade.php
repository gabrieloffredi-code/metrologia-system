<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Relatório de Calibração:') }} {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">
                
                {{-- Seção 1: Resultado Final --}}
                <div class="mb-8 p-6 bg-indigo-50 border-l-4 border-indigo-600 rounded-md">
                    <h3 class="text-2xl font-bold text-indigo-800 mb-3">{{ __('Resultado da Medição') }}</h3>
                    
                    @php
                        // Cálculo da Estimativa Final (Média + Correção)
                        $finalEstimate = $calibration->mean_value + $calibration->total_correction;
                        $unit = $asset->unit;
                        $u = number_format($calibration->expanded_uncertainty, 4);
                    @endphp

                    <p class="text-3xl font-extrabold text-indigo-900 mt-4">
                        {{ number_format($finalEstimate, 4) }} &plusmn; {{ $u }} {{ $unit }}
                    </p>
                    <p class="text-base text-indigo-700 mt-2">
                        {{ $calibration->confidence_level }}% de Nível de Confiança (Fator k = {{ number_format($calibration->k_factor, 3) }})
                    </p>
                </div>

                {{-- Seção 2: Detalhes do Cálculo --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    
                    {{-- Card 1: Informações da Medição --}}
                    <div class="p-4 border rounded-lg">
                        <h4 class="font-semibold text-gray-700">Estimativa Base</h4>
                        <p class="text-sm">Média das Leituras: <strong>{{ number_format($calibration->mean_value, 4) }} {{ $unit }}</strong></p>
                        <p class="text-sm">Soma das Correções: <strong>{{ number_format($calibration->total_correction, 4) }} {{ $unit }}</strong></p>
                    </div>

                    {{-- Card 2: Incerteza Combinada --}}
                    <div class="p-4 border rounded-lg">
                        <h4 class="font-semibold text-gray-700">Incerteza Padrão Combinada ($u_c$)</h4>
                        <p class="text-sm font-bold text-green-600">{{ number_format($calibration->combined_uncertainty, 6) }} {{ $unit }}</p>
                        <p class="text-xs text-gray-500 mt-1">Graus de Liberdade Efetivos ($v_{eff}$): {{ number_format($calibration->effective_degrees_of_freedom, 2) }}</p>
                    </div>

                    {{-- Card 3: Instrumento --}}
                    <div class="p-4 border rounded-lg">
                        <h4 class="font-semibold text-gray-700">Informações da Peça</h4>
                        <p class="text-sm">Peça: <strong>{{ $asset->name }}</strong></p>
                        <p class="text-sm">Referência: <strong>{{ $asset->reference }}</strong></p>
                        <p class="text-sm">Data da Calibração: <strong>{{ $calibration->date->format('d/m/Y') }}</strong></p>
                    </div>
                </div>

                {{-- Seção 3: Fontes de Incerteza Detalhadas --}}
                <h3 class="text-xl font-medium text-gray-900 mb-4 border-b pb-2">Detalhe das Fontes de Incerteza</h3>

                {{-- 3.1 Leituras Brutas (Tipo A - Repetibilidade) --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-gray-700">1. Repetibilidade (Incerteza Tipo A Interna)</h4>
                    <p class="text-sm text-gray-600 mb-2">
                        Leituras realizadas (N={{ $calibration->readings->count() }}): 
                        {{ $calibration->readings->pluck('value')->join(', ') }}
                    </p>
                    @php
                        // Para mostrar o u_rep, precisamos acessar o valor calculado que está implicitamente no mean_value ou recalcular uma parte simples.
                        // Usaremos o u_c e as demais incertezas para estimar a contribuição, mas para simplificação, exibimos a informação chave:
                        $uRepSquared = $calibration->combined_uncertainty ** 2 - 
                                       ($calibration->uncertaintyA->sum(fn($uA) => ($uA->value / $uA->factor_k) ** 2) + 
                                        $calibration->uncertaintyB->sum(fn($uB) => ($uB->value / \App\Services\MetrologyService::DIVISORS[$uB->distribution]) ** 2));
                        $uRep = sqrt(max(0, $uRepSquared)); // Usa max(0, X) para evitar erro com valor negativo (arredondamento)
                    @endphp
                    <p class="text-sm">Incerteza Padrão ($u_{rep}$): 
                        <strong>{{ number_format($uRep, 6) }} {{ $unit }}</strong>
                    </p>
                </div>

                {{-- 3.2 Correções --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-gray-700">2. Correções Aplicadas</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Referência</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Valor da Correção</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($calibration->corrections as $correction)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $correction->reference }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($correction->value, 6) }} {{ $unit }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="px-4 py-2 text-sm text-gray-500 italic">Nenhuma correção aplicada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3.3 Incertezas Tipo A (Externas) --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-gray-700">3. Incertezas Tipo A (Externas)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Referência</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Incerteza Expandida ($U$)</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Fator K Original</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Incerteza Padrão ($u_i$)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($calibration->uncertaintyA as $uA)
                                    @php
                                        // u_i = U / K
                                        $u_i = $uA->value / $uA->factor_k;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $uA->reference }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($uA->value, 6) }} {{ $unit }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($uA->factor_k, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-green-600 font-medium">{{ number_format($u_i, 6) }} {{ $unit }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-2 text-sm text-gray-500 italic">Nenhuma incerteza Tipo A externa registrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3.4 Incertezas Tipo B --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-lg text-gray-700">4. Incertezas Tipo B (Estimadas)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Referência</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Distribuição</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Limite de Erro ('a')</th>
                                    <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Incerteza Padrão ($u_i$)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($calibration->uncertaintyB as $uB)
                                    @php
                                        // Acessamos as constantes definidas no MetrologyService
                                        $divisor = match ($uB->distribution) {
                                            'RETANGULAR' => \App\Services\MetrologyService::DIVISORS['RETANGULAR'],
                                            'TRIANGULAR' => \App\Services\MetrologyService::DIVISORS['TRIANGULAR'],
                                            'MEIA_FAIXA' => \App\Services\MetrologyService::DIVISORS['MEIA_FAIXA'],
                                            default => 1,
                                        };
                                        $u_i = $uB->value / $divisor;
                                        $distLabel = match ($uB->distribution) {
                                            'RETANGULAR' => 'Retangular ($\sqrt{3}$)',
                                            'TRIANGULAR' => 'Triangular ($\sqrt{6}$)',
                                            'MEIA_FAIXA' => 'Meia Faixa ($\sqrt{12}$)',
                                            default => 'Outra',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $uB->reference }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $distLabel }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($uB->value, 6) }} {{ $unit }}</td>
                                        <td class="px-4 py-2 text-sm text-green-600 font-medium">{{ number_format($u_i, 6) }} {{ $unit }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-2 text-sm text-gray-500 italic">Nenhuma incerteza Tipo B registrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t flex justify-between">
                    <a href="{{ route('laboratories.assets.show', ['laboratory' => $laboratory, 'asset' => $asset]) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                        &larr; Voltar para a Peça
                    </a>
                    
                    {{-- Botão para Download/Exportação PDF (Passo 15) --}}
                    <<a href="{{ route('calibrations.exportPdf', [$laboratory->id, $asset->id, $calibration->id]) }}" class="btn ...">Exportar PDF</a>
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Exportar Relatório (PDF)
                    </a>
                    <a href="{{ route('calibrations.exportPdf', [$laboratory->id, $asset->id, $calibration->id]) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold hover:bg-gray-700">
    🖨️ Imprimir / Salvar em PDF
</a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>