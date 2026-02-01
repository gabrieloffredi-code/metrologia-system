<?php

namespace App\Services;

use App\Models\TStudentTable;
use Illuminate\Support\Facades\DB; // Necessário para buscar na Tabela T-Student

class MetrologyService
{
    // Constantes dos Divisores GUM (para Tipo B)
    const DIVISORS = [
        'RETANGULAR' => 1.73205080757, // sqrt(3)
        'TRIANGULAR' => 2.44948974278, // sqrt(6)
        'MEIA_FAIXA' => 3.46410161514, // sqrt(12)
    ];

    /**
     * Etapa 1: Calcula Média, Desvio Padrão e Incerteza Padrão Tipo A da Repetibilidade.
     * @param array $readings Array de valores medidos.
     * @return array
     */
    public function calculateTypeA(array $readings): array
    {
        $n = count($readings);
        if ($n < 2) {
            // Em metrologia, para Tipo A, N deve ser > 1
            return [
                'mean' => array_sum($readings),
                'std_dev' => 0.0,
                'u_rep' => 0.0,
                'v_eff' => 0,
            ];
        }

        $mean = array_sum($readings) / $n;
        
        // 1. Desvio Padrão (Fórmula amostral: n-1)
        $sumOfSquares = 0;
        foreach ($readings as $value) {
            $sumOfSquares += ($value - $mean) ** 2;
        }
        $stdDev = sqrt($sumOfSquares / ($n - 1));

        // 2. Incerteza Padrão da Repetibilidade (Tipo A)
        $uRep = $stdDev / sqrt($n);
        
        return [
            'mean' => $mean,
            'std_dev' => $stdDev,
            'u_rep' => $uRep,
            'v_eff' => $n - 1, // Graus de liberdade efetivos para Tipo A
        ];
    }

    /**
     * Etapa 2: Converte Incertezas Tipo A (Certificado) em Padrão.
     * @param array $uAData Array de fontes de incerteza A
     * @return array Contendo a soma quadrática da variância e graus de liberdade.
     */
    public function calculateExternalTypeA(array $uAData): array
    {
        $sumOfVariances = 0.0;
        $vSum = 0.0;
        
        foreach ($uAData as $item) {
            // u_i = U_certificado / k_certificado
            $u_i = $item['value'] / $item['factor_k'];
            $sumOfVariances += $u_i ** 2;
            
            // Graus de liberdade (v_i) para o cálculo de Welch-Satterthwaite (v_eff)
            // Usamos o campo 'degrees_of_freedom' da migração, padrão 50.
            $vSum += ($u_i ** 4) / ($item['degrees_of_freedom'] ?? 50); 
        }

        return [
            'u_total_squared' => $sumOfVariances,
            'v_sum_welsat' => $vSum,
        ];
    }

    /**
     * Etapa 3: Converte Incertezas Tipo B em Padrão.
     * @param array $uBData Array de fontes de incerteza B
     * @return array Contendo a soma quadrática da variância e graus de liberdade.
     */
    public function calculateTypeB(array $uBData): array
    {
        $sumOfVariances = 0.0;
        $vSum = 0.0;
        
        // Graus de liberdade para Tipo B (assumimos grande, padrão da migração é 1000)
        $v_i = 1000; 

        foreach ($uBData as $item) {
            $divisor = self::DIVISORS[$item['distribution']];
            
            // u_i = Limite_a / Divisor
            $u_i = $item['value'] / $divisor;
            $sumOfVariances += $u_i ** 2;
            
            // Cálculo para Welch-Satterthwaite
            $vSum += ($u_i ** 4) / $v_i;
        }

        return [
            'u_total_squared' => $sumOfVariances,
            'v_sum_welsat' => $vSum,
        ];
    }

    /**
     * Etapa 4: Calcula a Incerteza Combinada e os Graus de Liberdade Efetivos (Welch-Satterthwaite).
     * @param float $uTotalSquared Soma das Variâncias (u_A^2 + u_B^2)
     * @param float $vSumWelsat Soma dos termos da fórmula de Welch-Satterthwaite
     * @return array
     */
    public function calculateCombinedUncertainty(float $uTotalSquared, float $vSumWelsat): array
    {
        $uc = sqrt($uTotalSquared);
        
        // Calcula Graus de Liberdade Efetivos (v_eff) - Welch-Satterthwaite
        if ($vSumWelsat > 0) {
            $v_eff = ($uc ** 4) / $vSumWelsat;
        } else {
            // Se não houver incertezas Tipo A ou B com v_i definido, usamos um valor alto
            $v_eff = 10000; 
        }

        return [
            'uc' => $uc,
            'v_eff' => $v_eff,
        ];
    }
    
    /**
     * Etapa 5: Encontra o Fator de Abrangência (k) na Tabela t-Student.
     * @param float $v_eff Graus de Liberdade Efetivos
     * @param int $confidenceLevel Nível de Confiança (90, 95 ou 99)
     * @return float Fator K
     */
    public function getKFactor(float $v_eff, int $confidenceLevel): float
    {
        // Coluna a buscar na tabela
        $column = 'p' . $confidenceLevel;
        
        // Graus de liberdade para consulta (arredondado para baixo)
        $v_lookup = floor($v_eff);

        // Busca o valor mais próximo (menor ou igual) na tabela TStudentTable
        $tStudent = TStudentTable::where('degrees_of_freedom', '<=', $v_lookup)
                                 ->orderBy('degrees_of_freedom', 'desc')
                                 ->first();

        // Se encontrado, retorna o fator K
        if ($tStudent && isset($tStudent->$column)) {
            return (float) $tStudent->$column;
        }
        
        // Fallback: Se não encontrou, assume valor para infinito (distribuição normal)
        switch ($confidenceLevel) {
            case 90: return 1.645;
            case 95: return 1.960; // Valor padrão para k=2
            case 99: return 2.576; // Valor padrão para k=3
            default: return 1.0;
        }
    }
}