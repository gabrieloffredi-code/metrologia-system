<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Laboratory;
use App\Models\Calibration;
use App\Models\Reading;
use App\Models\Correction;
use App\Models\UncertaintyA;
use App\Models\UncertaintyB;
use App\Services\MetrologyService; // Importe o serviço
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Snappy\Facades\SnappyPdf; // Para Exportação PDF

class CalibrationController extends Controller
{
    protected $metrologyService;

    // CONSTRUTOR: Injeta o MetrologyService
    public function __construct(MetrologyService $metrologyService)
    {
        // Certifique-se de que o MetrologyService foi importado corretamente
        $this->metrologyService = $metrologyService;
    }

    /**
     * Exibe o formulário de Nova Calibração.
     * Rota: laboratories/{laboratory}/assets/{asset}/calibrations/create
     */
    public function create(Laboratory $laboratory, Asset $asset)
    {
        // ACL: Apenas EDITOR ou ADM pode criar calibrações
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR')) {
            abort(403, 'Acesso negado. Apenas Editores ou Administradores podem executar calibrações.');
        }

        // Garante que o asset pertence ao laboratório
        if ($asset->laboratory_id !== $laboratory->id) {
            abort(404);
        }

        // Opções de distribuição para Incerteza Tipo B
        $distributions = [
            'RETANGULAR' => 'Retangular (a / sqrt(3))',
            'TRIANGULAR' => 'Triangular (a / sqrt(6))',
            'MEIA_FAIXA' => 'Meia Faixa Retangular (a / sqrt(12))',
        ];

        return view('calibrations.create', compact('laboratory', 'asset', 'distributions'));
    }

    /**
     * Processa o formulário, realiza o cálculo GUM e armazena os resultados.
     */
    public function store(Request $request, Laboratory $laboratory, Asset $asset)
    {
        // ACL: Apenas EDITOR ou ADM pode salvar calibrações
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR')) {
            abort(403, 'Acesso negado.');
        }

        if ($asset->laboratory_id !== $laboratory->id) {
            abort(404);
        }

        // Estrutura de Validação BÁSICA
        $request->validate([
            'confidence_level' => 'required|numeric|in:90,95,99', // Porcentagem de confiabilidade
            'readings' => 'required|array|min:2', // Deve ter pelo menos 2 leituras
            'readings.*.value' => 'required|numeric',
            'corrections' => 'nullable|array',
            'corrections.*.value' => 'required|numeric',
            'uncertainty_a' => 'nullable|array',
            'uncertainty_a.*.value' => 'required|numeric',
            'uncertainty_a.*.factor_k' => 'required|numeric|min:1',
            'uncertainty_b' => 'nullable|array',
            'uncertainty_b.*.value' => 'required|numeric',
            'uncertainty_b.*.distribution' => 'required|in:RETANGULAR,TRIANGULAR,MEIA_FAIXA',
        ]);
        
        // ------------------------------------------------------------------
        // LÓGICA DE CÁLCULO GUM (Passo 11)
        // ------------------------------------------------------------------
        
        $readings = collect($request->readings)->pluck('value')->toArray();
        $corrections = collect($request->corrections)->pluck('value')->toArray();
        $uAData = $request->uncertainty_a ?? [];
        $uBData = $request->uncertainty_b ?? [];

        // --- 1. MELHOR ESTIMATIVA E INFORMAÇÕES TIPO A ---
        $typeAData = $this->metrologyService->calculateTypeA($readings);
        $totalCorrection = array_sum($corrections);
        $bestEstimate = $typeAData['mean'] + $totalCorrection;
        
        // Soma inicial das variâncias (u_rep^2) e dos termos de Welch-Satterthwaite (v_sum)
        $uTotalSquared = $typeAData['u_rep'] ** 2;
        $vSumWelsat = ($typeAData['u_rep'] ** 4) / $typeAData['v_eff']; 

        // --- 2. INFORMAÇÕES TIPO A EXTERNAS ---
        $extAData = $this->metrologyService->calculateExternalTypeA($uAData);
        $uTotalSquared += $extAData['u_total_squared'];
        $vSumWelsat += $extAData['v_sum_welsat'];

        // --- 3. INFORMAÇÕES TIPO B ---
        $typeBData = $this->metrologyService->calculateTypeB($uBData);
        $uTotalSquared += $typeBData['u_total_squared'];
        $vSumWelsat += $typeBData['v_sum_welsat'];
        
        // --- 4. CÁLCULO FINAL GUM ---
        $combinedData = $this->metrologyService->calculateCombinedUncertainty($uTotalSquared, $vSumWelsat);
        
        $uc = $combinedData['uc'];
        $v_eff = $combinedData['v_eff'];
        
        // --- 5. FATOR K E INCERTEZA EXPANDIDA ---
        $confidenceLevel = (int) $request->confidence_level;
        $kFactor = $this->metrologyService->getKFactor($v_eff, $confidenceLevel);
        $expandedUncertainty = $kFactor * $uc;

        // ------------------------------------------------------------------
        // PERSISTÊNCIA DOS DADOS (SALVAMENTO)
        // ------------------------------------------------------------------
        
        // 1. Salva o cabeçalho da calibração
        $calibration = Calibration::create([
            'asset_id' => $asset->id,
            'date' => now(),
            'confidence_level' => $confidenceLevel,
            'mean_value' => $typeAData['mean'],
            'total_correction' => $totalCorrection,
            'combined_uncertainty' => $uc,
            'expanded_uncertainty' => $expandedUncertainty,
            'k_factor' => $kFactor,
            'effective_degrees_of_freedom' => $v_eff,
        ]);
        
        // 2. Salva Leituras, Correções e Incertezas (Bulk Insertion)
        $calibration->readings()->createMany($request->readings);
        $calibration->corrections()->createMany($request->corrections ?? []);
        $calibration->uncertaintyA()->createMany($uAData);
        $calibration->uncertaintyB()->createMany($uBData);
        
        // Retorno
        return redirect()->route('laboratories.assets.calibrations.show', [
            'laboratory' => $laboratory, 
            'asset' => $asset, 
            'calibration' => $calibration
        ])->with('success', 'Calibração e cálculo GUM realizados com sucesso!');
    }

    /**
     * Exibe o resultado de uma calibração específica.
     */
    public function show(Laboratory $laboratory, Asset $asset, Calibration $calibration)
    {
        // ACL: Apenas VISUALIZADOR ou superior pode ver
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'VISUALIZADOR')) {
            abort(403, 'Acesso negado.');
        }

        if ($asset->laboratory_id !== $laboratory->id || $calibration->asset_id !== $asset->id) {
            abort(404);
        }
        
        // Carrega os dados detalhados para exibição
        $calibration->load(['readings', 'corrections', 'uncertaintyA', 'uncertaintyB']);

        return view('calibrations.show', compact('laboratory', 'asset', 'calibration'));
    }

    /**
     * Exporta o relatório de calibração como PDF.
     */
    public function exportPdf($laboratoryId, $assetId, $calibrationId)
{
    // 1. Busca os modelos usando os IDs da rota aninhada
    $laboratory = \App\Models\Laboratory::findOrFail($laboratoryId);
    $asset = \App\Models\Asset::findOrFail($assetId);
    $calibration = \App\Models\Calibration::findOrFail($calibrationId);

    // 2. Retorna a view HTML que criamos para o CSS Print enviando as variáveis
    return view('laboratories.relatorio-print', compact('laboratory', 'asset', 'calibration'));
}
}