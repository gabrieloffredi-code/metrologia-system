<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Metrológico - {{ $laboratory->name }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .actions-bar { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: right; }
        .btn-print { background-color: #1e293b; color: white; border: none; padding: 10px 20px; font-size: 14px; font-weight: bold; border-radius: 5px; cursor: pointer; }
        .document-box { background: #fff; max-width: 800px; margin: 0 auto; padding: 40px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .header-table td { border: 1px solid #cbd5e1; padding: 12px; }
        .title { font-size: 18px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .section-title { font-size: 14px; font-weight: bold; background-color: #f1f5f9; padding: 6px 10px; margin-top: 20px; border-left: 4px solid #1e293b; }
        
        @media print {
            body { background-color: #fff; padding: 0; }
            .actions-bar { display: none !important; }
            .document-box { box-shadow: none; padding: 0; max-width: 100%; }
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
                <td style="width: 25%; text-align: center; font-weight: bold;">SIS-METROLOGIA</td>
                <td class="title">Relatório de Calibração GUM</td>
                <td style="width: 25%; text-align: center; font-size: 11px;">Data: {{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        <div class="section-title">Dados do Laboratório e Ativo</div>
        <p><strong>Laboratório:</strong> {{ $laboratory->name }}</p>
        <p><strong>Equipamento/Peça:</strong> {{ $asset->name }}</p>

        <div class="section-title">Resultado da Calibração</div>
        <p><strong>Incerteza Expandida (U):</strong> {{ $calibration->expanded_uncertainty ?? 'Calculado com sucesso' }}</p>
        <p><strong>Fator de Abrangência (k):</strong> {{ $calibration->coverage_factor ?? '2.0' }}</p>
    </div>

</body>
</html>