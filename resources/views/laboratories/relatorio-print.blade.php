<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Calibração - GUM</title>
    <style>
        /* --- ESTILOS VISUAIS (TELA E PAPEL) --- */
        body { font-family: 'Arial', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; }
        .actions-bar { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: right; }
        .btn-print { background-color: #0f172a; color: white; border: none; padding: 10px 20px; font-size: 14px; font-weight: bold; border-radius: 5px; cursor: pointer; }
        .btn-print:hover { background-color: #1e293b; }
        
        .document-box { background: #fff; max-width: 850px; margin: 0 auto; padding: 50px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        
        /* Tabelas estruturadas */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .header-table td { border: 2px solid #0f172a; padding: 15px; text-align: center; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        
        .section-title { font-size: 13px; font-weight: bold; background-color: #f1f5f9; padding: 8px 12px; margin-top: 25px; margin-bottom: 10px; border-left: 4px solid #0f172a; text-transform: uppercase; }
        
        .grid-info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; font-size: 13px; }
        .grid-info p { margin: 4px 0; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: center; font-size: 12px; }
        .data-table th { background-color: #f8fafc; font-weight: bold; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .footer-sign { margin-top: 50px; text-align: center; font-size: 13px; border-top: 1px solid #94a3b8; padding-top: 10px; display: inline-block; width: 250px; }

        /* --- CONFIGURAÇÃO DE IMPRESSÃO --- */
        @media print {
            body { background-color: #fff; padding: 0; color: #000; }
            .actions-bar { display: none !important; }
            .document-box { box-shadow: none; padding: 0; max-width: 100%; }
            .section-title { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Confirmar Impressão / Salvar PDF</button>
    </div>

    <div class="document-box">
        
        <table class="header-table">
            <tr>
                <td style="width: 25%; font-weight: bold; font-size: 16px;">SIS-METROLOGIA</td>
                <td class="title">Relatório de Ensaio e Calibração<br><span style="font-size: 12px; font-weight: normal; lowercase;">Avaliação de Incerteza pelo Método GUM</span></td>
                <td style="width: 25%; font-size: 12px;">
                    <strong>ID Relatório:</strong> #{{ $calibration->id }}<br>
                    <strong>Data:</strong> {{ now()->format('d/m/Y') }}
                </td>
            </tr>
        </table>

        <div class="section-title">1. Identificação do Ensaio</div>
        <div class="grid-info">
            <div>
                <p><strong>Laboratório:</strong> {{ $laboratory->name }}</p>
                <p><strong>Descrição do Lab:</strong> {{ $laboratory->description ?? 'Não informada' }}</p>
            </div>
            <div>
                <p><strong>Ativo/Equipamento:</strong> {{ $asset->name }}</p>
                <p><strong>Cadastrado em:</strong> {{ $calibration->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="section-title">2. Ciclos de Medição Obtidos</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ponto</th>
                    <th>Valor Nominal</th>
                    <th>Medição 1</th>
                    <th>Medição 2</th>
                    <th>Medição 3</th>
                    <th>Média ($\bar{x}$)</th>
                    <th>Desvio Padrão ($s$)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>01</td>
                    <td>{{ $calibration->nominal_value ?? '---' }} mm</td>
                    <td>{{ $calibration->measurement_1 ?? '---' }}</td>
                    <td>{{ $calibration->measurement_2 ?? '---' }}</td>
                    <td>{{ $calibration->measurement_3 ?? '---' }}</td>
                    <td>{{ $calibration->average ?? '---' }}</td>
                    <td>{{ $calibration->standard_deviation ?? '---' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">3. Componentes da Incerteza (Orçamento de Incerteza)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fonte de Incerteza</th>
                    <th>Tipo</th>
                    <th>Distribuição</th>
                    <th>Incerteza Padrão ($u_i$)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left;">Repetibilidade (Incerteza Tipo A)</td>
                    <td>A</td>
                    <td>Normal</td>
                    <td>{{ $calibration->uncertainty_a ?? '---' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;">Resolução do Equipamento (Incerteza Tipo B)</td>
                    <td>B</td>
                    <td>Retangular</td>
                    <td>{{ $calibration->uncertainty_b ?? '---' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">4. Resultado da Expressão Metrológica</div>
        <table class="data-table" style="border: 2px solid #0f172a;">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th>Incerteza Combinada ($u_c$)</th>
                    <th>Graus de Liberdade ($v_{eff}$)</th>
                    <th>Fator de Abrangência ($k$)</th>
                    <th>Incerteza Expandida ($U$)</th>
                </tr>
            </thead>
            <tbody>
                <tr style="font-size: 14px; font-weight: bold;">
                    <td>{{ $calibration->combined_uncertainty ?? '---' }}</td>
                    <td>{{ $calibration->degrees_of_freedom ?? 'Infitinitos (Normal)' }}</td>
                    <td>{{ $calibration->coverage_factor ?? '2.00' }}</td>
                    <td style="background-color: #f8fafc; color: #0284c7; font-size: 16px;">
                        ± {{ $calibration->expanded_uncertainty ?? '---' }} mm
                    </td>
                </tr>
            </tbody>
        </table>

        <p style="font-size: 11px; font-style: italic; color: #64748b; margin-top: -10px;">
            Nota: A incerteza expandida relatada é baseada em uma incerteza padrão combinada multiplicada por um fator de abrangência k que fornece um nível de confiança de aproximadamente 95.45%.
        </p>

        <div style="text-align: right; margin-top: 60px;">
            <div class="footer-sign">
                <strong>Responsável Técnico</strong><br>
                {{ Auth::user()->name }}
            </div>
        </div>

    </div>

</body>
</html>