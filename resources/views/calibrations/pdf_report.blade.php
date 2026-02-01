<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Calibração - {{ $asset->name }}</title>

    {{-- CSS INLINE BÁSICO: Usar estilos internos para garantir que o wkhtmltopdf os leia --}}
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; padding: 0; }
        .container { width: 90%; margin: 0 auto; padding: 20px; }
        h1, h2, h3, h4 { color: #333; margin-top: 15px; margin-bottom: 5px; }
        .header-box { border-left: 5px solid #4f46e5; background-color: #eef2ff; padding: 15px; margin-bottom: 20px; }
        .data-box { border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 9pt; }
        th { background-color: #f7f7f7; }
        .result-value { font-size: 18pt; font-weight: bold; color: #1e3a8a; }
        .result-unit { font-size: 12pt; color: #1e3a8a; }
        .text-success { color: #15803d; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        
        <h1 style="text-align: center; margin-bottom: 30px;">Relatório de Calibração</h1>

        {{-- Cabeçalho do Laboratório/Peça --}}
        <div style="margin-bottom: 20px;">
            <p><strong>Laboratório:</strong> {{ $laboratory->name }}</p>
            <p><strong>Peça Calibrada:</strong> {{ $asset->name }} (Ref: {{ $asset->reference ?? 'N/A' }})</p>
            <p><strong>Data da Calibração:</strong> {{ $calibration->date->format('d/m/Y') }}</p>
            <p><strong>Unidade de Medida:</strong> {{ $asset->unit }}</p>
        </div>

        {{-- Seção 1: Resultado Final --}}
        <div class="header-box">
            <h3 style="margin-top: 0; color: #3730a3;">Resultado da Medição (Estimativa ± U)</h3>
            
            @php
                $finalEstimate = $calibration->mean_value + $calibration->total_correction;
                $unit = $asset->unit;
                $u = number_format($calibration->expanded_uncertainty, 4);
            @endphp

            <p class="result-value">
                {{ number_format($finalEstimate, 4) }} &plusmn; {{ $u }} <span class="result-unit">{{ $unit }}</span>
            </p>
            <p style="font-size: 10pt; color: #4338ca;">
                {{ $calibration->confidence_level }}% de Nível de Confiança (Fator k = {{ number_format($calibration->k_factor, 3) }})
            </p>
        </div>

        {{-- Seção 2: Detalhes do Cálculo --}}
        <h2>Detalhes do Cálculo GUM</h2>

        <div style="display: flex; gap: 20px;">
            <div class="data-box" style="flex: 1;">
                <h4>Dados Principais</h4>
                <p style="font-size: 9pt;">Média das Leituras: <strong>{{ number_format($calibration->mean_value, 4) }}</strong></p>
                <p style="font-size: 9pt;">Soma das Correções: <strong>{{ number_format($calibration->total_correction, 4) }}</strong></p>
            </div>
            <div class="data-box" style="flex: 1;">
                <h4>Incerteza Combinada</h4>
                <p style="font-size: 9pt;">$u_c$ (Padrão Combinada): <strong class="text-success">{{ number_format($calibration->combined_uncertainty, 6) }}</strong></p>
                <p style="font-size: 9pt;">$v_{eff}$ (Graus de Liberdade): {{ number_format($calibration->effective_degrees_of_freedom, 2) }}</p>
            </div>
        </div>

        {{-- Seção 3: Fontes de Incerteza Detalhadas --}}
        <h2 style="margin-top: 25px;">Contribuições da Incerteza</h2>

        {{-- 3.1 Leituras Brutas (Tipo A - Repetibilidade) --}}
        <h3 style="margin-top: 20px;">1. Repetibilidade (Incerteza Tipo A Interna)</h3>
        @php
            // Recálculo aproximado para exibição (usando os valores salvos)
            $uRepSquared = $calibration->combined_uncertainty ** 2 - 
                           ($calibration->uncertaintyA->sum(fn($uA) => ($uA->value / $uA->factor_k) ** 2) + 
                            $calibration->uncertaintyB->sum(fn($uB) => ($uB->value / \App\Services\MetrologyService::DIVISORS[$uB->distribution]) ** 2));
            $uRep = sqrt(max(0, $uRepSquared));
        @endphp
        <p style="font-size: 9pt;">Leituras: {{ $calibration->readings->pluck('value')->join(', ') }}</p>
        <p style="font-size: 9pt;">Incerteza Padrão ($u_{rep}$): <strong class="text-success">{{ number_format($uRep, 6) }} {{ $unit }}</strong></p>

        {{-- 3.2 Correções --}}
        <h3 style="margin-top: 20px;">2. Correções Aplicadas</h3>
        <table>
            <thead>
                <tr><th>Referência</th><th>Valor da Correção</th></tr>
            </thead>
            <tbody>
                @forelse($calibration->corrections as $correction)
                    <tr>
                        <td>{{ $correction->reference }}</td>
                        <td>{{ number_format($correction->value, 6) }} {{ $unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" style="font-style: italic;">Nenhuma correção aplicada.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- 3.3 Incertezas Tipo A (Externas) --}}
        <h3 style="margin-top: 20px;">3. Incertezas Tipo A (Certificados/Padrões)</h3>
        <table>
            <thead>
                <tr><th>Referência</th><th>Incerteza Expandida ($U$)</th><th>Fator K Original</th><th>Incerteza Padrão ($u_i$)</th></tr>
            </thead>
            <tbody>
                @forelse($calibration->uncertaintyA as $uA)
                    @php $u_i = $uA->value / $uA->factor_k; @endphp
                    <tr>
                        <td>{{ $uA->reference }}</td>
                        <td>{{ number_format($uA->value, 6) }} {{ $unit }}</td>
                        <td>{{ number_format($uA->factor_k, 2) }}</td>
                        <td class="text-success">{{ number_format($u_i, 6) }} {{ $unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="font-style: italic;">Nenhuma incerteza Tipo A externa registrada.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- 3.4 Incertezas Tipo B --}}
        <h3 style="margin-top: 20px;">4. Incertezas Tipo B (Estimadas)</h3>
        <table>
            <thead>
                <tr><th>Referência</th><th>Distribuição</th><th>Limite de Erro ('a')</th><th>Incerteza Padrão ($u_i$)</th></tr>
            </thead>
            <tbody>
                @forelse($calibration->uncertaintyB as $uB)
                    @php
                        $divisor = \App\Services\MetrologyService::DIVISORS[$uB->distribution];
                        $u_i = $uB->value / $divisor;
                        $distLabel = match ($uB->distribution) {
                            'RETANGULAR' => 'Retangular', 'TRIANGULAR' => 'Triangular', 'MEIA_FAIXA' => 'Meia Faixa', default => 'Outra',
                        };
                    @endphp
                    <tr>
                        <td>{{ $uB->reference }}</td>
                        <td>{{ $distLabel }} ($\sqrt{n}$)</td>
                        <td>{{ number_format($uB->value, 6) }} {{ $unit }}</td>
                        <td class="text-success">{{ number_format($u_i, 6) }} {{ $unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="font-style: italic;">Nenhuma incerteza Tipo B registrada.</td></tr>
                @endforelse
            </tbody>
        </table>

        <p style="text-align: center; margin-top: 40px; font-size: 8pt; color: #666;">
            Relatório gerado pelo {{ config('app.name') }} em {{ now()->format('d/m/Y H:i:s') }}.
        </p>

    </div>
</body>
</html>