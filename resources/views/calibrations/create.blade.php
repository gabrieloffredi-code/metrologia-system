<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nova Calibração para:') }} {{ $asset->name }} ({{ $asset->unit }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('laboratories.assets.calibrations.store', ['laboratory' => $laboratory, 'asset' => $asset]) }}" class="p-6 text-gray-900">
                    @csrf

                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Configuração do Resultado</h3>
                        <x-input-label for="confidence_level" :value="__('Nível de Confiança Desejado (%)')" />
                        <select id="confidence_level" name="confidence_level" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full md:w-1/4" required>
                            <option value="95" selected>95% (Comum)</option>
                            <option value="99">99%</option>
                            <option value="90">90%</option>
                        </select>
                        <x-input-error :messages="$errors->get('confidence_level')" class="mt-2" />
                    </div>

                    <div class="mb-8 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">1. Leituras e Incerteza Tipo A (Repetibilidade)</h3>
                        <p class="text-sm text-gray-600 mb-4">Insira os valores medidos. O Desvio Padrão será calculado automaticamente.</p>
                        
                        <div id="readings-container">
                            @for ($i = 0; $i < 3; $i++)
                                <div class="flex space-x-2 mb-2 reading-item">
                                    <x-text-input type="number" step="any" name="readings[{{ $i }}][value]" placeholder="Leitura #{{ $i+1 }} em {{ $asset->unit }}" class="w-full" required />
                                </div>
                            @endfor
                        </div>
                        <button type="button" id="add-reading-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">+ Adicionar Leitura</button>
                        <x-input-error :messages="$errors->get('readings')" class="mt-2" />
                    </div>

                    <div class="mb-8 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">2. Correções (Ajustes Sistêmicos)</h3>
                        <p class="text-sm text-gray-600 mb-4">Erros conhecidos que devem ser somados ou subtraídos da média.</p>
                        
                        <div id="corrections-container">
                            <div class="flex space-x-2 mb-2 correction-item">
                                <x-text-input type="text" name="corrections[0][reference]" placeholder="Referência (Ex: Erro do Certificado)" class="w-2/3" />
                                <x-text-input type="number" step="any" name="corrections[0][value]" placeholder="Valor (+/-)" class="w-1/3" />
                            </div>
                        </div>
                        <button type="button" id="add-correction-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">+ Adicionar Correção</button>
                        <x-input-error :messages="$errors->get('corrections')" class="mt-2" />
                    </div>
                    
                    <div class="mb-8 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">3. Incertezas Tipo A (Externas, de Certificados)</h3>
                        <p class="text-sm text-gray-600 mb-4">Incerteza Expandida ($U$) de uma fonte externa (Ex: Padrão de Referência).</p>
                        
                        <div id="u-a-container">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-2 u-a-item">
                                <x-text-input type="text" name="uncertainty_a[0][reference]" placeholder="Referência" class="col-span-2" />
                                <x-text-input type="number" step="any" name="uncertainty_a[0][value]" placeholder="Valor U (Incerteza Expandida)" class="col-span-2" />
                                <x-text-input type="number" step="0.01" name="uncertainty_a[0][factor_k]" placeholder="Fator K" value="2" class="col-span-1" />
                            </div>
                        </div>
                        <button type="button" id="add-u-a-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">+ Adicionar Incerteza Tipo A</button>
                        <x-input-error :messages="$errors->get('uncertainty_a')" class="mt-2" />
                    </div>

                    <div class="mb-8 border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">4. Incertezas Tipo B (Estimadas)</h3>
                        <p class="text-sm text-gray-600 mb-4">Incertezas baseadas em limites de erro e distribuições (Ex: Resolução).</p>
                        
                        <div id="u-b-container">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-2 u-b-item">
                                <x-text-input type="text" name="uncertainty_b[0][reference]" placeholder="Referência (Ex: Resolução/2)" class="col-span-2" />
                                <x-text-input type="number" step="any" name="uncertainty_b[0][value]" placeholder="Limite de Erro 'a' (+/-)" class="col-span-2" />
                                <select name="uncertainty_b[0][distribution]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full col-span-1" required>
                                    @foreach($distributions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="button" id="add-u-b-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">+ Adicionar Incerteza Tipo B</button>
                        <x-input-error :messages="$errors->get('uncertainty_b')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Calcular e Salvar Resultado') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ... (fim da tag </form> e da div .bg-white) ... --}}
            </div>
        </div>
    </div>
    
    {{-- NOVO BLOCO SCRIPT: Colocado aqui para garantir que carregue DEPOIS do DOM --}}
    <script>
        // 1. VARIÁVEIS PHP PASSADAS DE FORMA SEGURA
        const ASSET_UNIT = @json($asset->unit);
        const DISTRIBUTIONS = @json($distributions);
        
        let lineCount = { 
            'readings-container': 3,
            'corrections-container': 1,
            'u-a-container': 1,
            'u-b-container': 1
        };
        
        // Funções de Template (sem alteração)
        const getSelectOptionsHtml = () => {
            let options = '';
            for (const [key, label] of Object.entries(DISTRIBUTIONS)) {
                options += `<option value="${key}">${label}</option>`;
            }
            return options;
        };
        const UB_OPTIONS_HTML = getSelectOptionsHtml();

        const readingTemplate = (index) => `
            <input type="number" step="any" name="readings[${index}][value]" placeholder="Leitura #${index + 1} em ${ASSET_UNIT}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required />
        `;
        const correctionTemplate = (index) => `
            <input type="text" name="corrections[${index}][reference]" placeholder="Referência (Ex: Erro do Certificado)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-2/3" />
            <input type="number" step="any" name="corrections[${index}][value]" placeholder="Valor (+/-)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-1/3" />
        `;
        const uaTemplate = (index) => `
            <input type="text" name="uncertainty_a[${index}][reference]" placeholder="Referência" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm col-span-2" />
            <input type="number" step="any" name="uncertainty_a[${index}][value]" placeholder="Valor U (Incerteza Expandida)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm col-span-2" />
            <input type="number" step="0.01" name="uncertainty_a[${index}][factor_k]" placeholder="Fator K" value="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm col-span-1" />
        `;
        const ubTemplate = (index) => `
            <input type="text" name="uncertainty_b[${index}][reference]" placeholder="Referência (Ex: Resolução/2)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm col-span-2" />
            <input type="number" step="any" name="uncertainty_b[${index}][value]" placeholder="Limite de Erro 'a' (+/-)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm col-span-2" />
            <select name="uncertainty_b[${index}][distribution]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full col-span-1" required>
                ${UB_OPTIONS_HTML}
            </select>
        `;
        
        // Função principal para adicionar linhas
        function addLine(containerId, templateFunction) {
            const container = document.getElementById(containerId);
            const newIndex = lineCount[containerId]++;
            
            const newElement = document.createElement('div');
            
            // ... (resto da lógica de addLine mantida, garantindo que o appendChild seja o final) ...
             if (containerId === 'readings-container') {
                newElement.className = 'flex space-x-2 mb-2 reading-item items-center';
            } else if (containerId === 'corrections-container') {
                newElement.className = 'flex space-x-2 mb-2 correction-item items-center';
            } else if (containerId === 'u-a-container' || containerId === 'u-b-container') {
                newElement.className = 'grid grid-cols-1 md:grid-cols-6 gap-2 mb-2 ' + containerId.replace('-container', '-item');
            }
            
            newElement.innerHTML = templateFunction(newIndex);
            
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'text-red-600 hover:text-red-800 text-sm font-medium ml-2';
            removeButton.textContent = '×'; 
            removeButton.onclick = function() {
                newElement.remove(); 
            };
            
            if (containerId === 'u-a-container' || containerId === 'u-b-container') {
                const inputWrapper = document.createElement('div');
                inputWrapper.className = 'col-span-1 flex items-center';
                inputWrapper.appendChild(removeButton);
                newElement.appendChild(inputWrapper);
            
                newElement.classList.add('md:grid-cols-6');
                newElement.classList.remove('md:grid-cols-5'); // Apenas para garantir
            } else {
                newElement.appendChild(removeButton);
            }
            
            container.appendChild(newElement);
            console.log('Linha adicionada com índice:', newIndex);
        }


        // 3. ANEXAR LISTENERS APÓS DEFINIÇÃO GLOBAL
        // Usamos addEventListener, sem o DOMContentLoaded, pois o script está no final do body
        document.getElementById('add-reading-btn').addEventListener('click', function() {
            addLine('readings-container', readingTemplate);
        });
        
        document.getElementById('add-correction-btn').addEventListener('click', function() {
            addLine('corrections-container', correctionTemplate);
        });
        
        document.getElementById('add-u-a-btn').addEventListener('click', function() {
            addLine('u-a-container', uaTemplate);
        });
        
        document.getElementById('add-u-b-btn').addEventListener('click', function() {
            addLine('u-b-container', ubTemplate);
        });
        
        console.log('Listeners anexados com sucesso.'); // Debug final
    </script>
</x-app-layout>


