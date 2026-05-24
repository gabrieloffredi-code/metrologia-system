<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Ensaio Metrológico - {{ $laboratorio->nome }}</title>
    <style>
        /* --- ESTILOS PARA EXIBIÇÃO NA TELA --- */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .actions-bar {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: right;
        }
        .btn-print {
            background-color: #2d3748;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-print:hover { background-color: #1a202c; }

        .document-box {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        /* Estrutura do Relatório */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .header-table td { border: 1px solid #cbd5e1; padding: 12px; }
        .title { font-size: 18px; font-weight: bold; text-align: center; text-transform: uppercase; }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 6px 10px;
            margin-top: 20px;
            border-left: 4px solid #2d3748;
        }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; font-size: 12px; }
        .data-table th { background-color: #f8fafc; }

        /* --- ✨ A MÁGICA DO CSS PRINT (PARA O PAPEL) --- */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .actions-bar {
                display: none !important; /* Esconde a barra com o botão de imprimir no papel */
            }
            .document-box {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            @page {
                size: A4;
                margin: 15mm; /* Margens perfeitas da folha */
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Salvar em PDF</button>
    </div>

    <div class="document-box">
        <table class="header-table">
            <tr>
                <td style="width: 25%; text-align: center; font-weight: bold;">SIS-METROLOGIA</td>
                <td class="title">Relatório de Calibração e Incerteza</td>
                <td style="width: 25%; text-align: center; font-size: 11px;">Data: {{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        <div class="section-title">Dados do Laboratório</div>
        <p style="font-size: 13px;"><strong>Identificação:</strong> {{ $laboratorio->nome }}</p>

        <div class="section-title">Componentes e Peças Analisadas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Peça</th>
                    <th>Valor Nominal</th>
                    <th>Incerteza Padronizada</th>
                    <th>Incerteza Expandida (U)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laboratorio->pecas as $peca)
                <tr>
                    <td>{{ $peca->nome }}</td>
                    <td>{{ $peca->valor_nominal }} mm</td>
                    <td>{{ $peca->incerteza_padrao }}</td>
                    <td style="font-weight: bold; color: #1e293b;">{{ $peca->incerteza_expandida }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>