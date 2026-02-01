<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * Exibe a lista de Assets (Peças) de um laboratório (Apenas VISUALIZADOR+).
     */
    public function index(Laboratory $laboratory)
    {
        // ACL: Apenas VISUALIZADOR ou superior pode ver a lista de assets
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'VISUALIZADOR')) {
            abort(403, 'Acesso negado. Você não tem permissão para visualizar peças neste laboratório.');
        }

        $assets = $laboratory->assets()->get();
        // Nota: Esta view 'assets.index' não foi criada, usamos 'laboratories.show'
        // Mas o método está aqui para completar o Resource
        return view('assets.index', compact('laboratory', 'assets'));
    }

    /**
     * Exibe o formulário de criação de nova peça (Apenas EDITOR+).
     */
    public function create(Laboratory $laboratory)
    {
        // ACL: Apenas EDITOR ou ADM pode criar peças
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR')) {
            abort(403, 'Acesso negado. Apenas Editores ou Administradores podem cadastrar peças.');
        }

        return view('assets.create', compact('laboratory'));
    }

    /**
     * Armazena uma nova peça no banco de dados (Apenas EDITOR+).
     */
    public function store(Request $request, Laboratory $laboratory)
    {
        // ... (ACL permanece o mesmo) ...
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR')) {
            abort(403, 'Acesso negado.');
        }

        // 1. Validação (mantida)
        $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'unit' => 'required|string|max:10',
            'nominal_value' => 'nullable|numeric|max:9999999.9999', 
            // Validação do arquivo
            'image_path' => 'nullable|image|max:2048', // 2MB max, deve ser uma imagem
        ]);

        $data = $request->except('image_path');
        $data['laboratory_id'] = $laboratory->id;

        // 2. Lógica de Upload de Imagem
        if ($request->hasFile('image_path')) {
            // Salva o arquivo no disco 'public' (pasta /storage/app/public/assets)
            // O nome do arquivo será um hash único para segurança
            $path = $request->file('image_path')->store('assets', 'public');
            $data['image_path'] = $path;
        }

        // 3. Criação do Asset
        Asset::create($data);

        return redirect()->route('laboratories.show', $laboratory)
                         ->with('success', 'Peça cadastrada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma peça (Apenas VISUALIZADOR+).
     */
    public function show(Laboratory $laboratory, Asset $asset)
    {
        // ACL: Apenas VISUALIZADOR ou superior pode ver os detalhes
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'VISUALIZADOR')) {
            abort(403, 'Acesso negado.');
        }
        
        // Garante que a peça pertence ao laboratório
        if ($asset->laboratory_id !== $laboratory->id) {
            abort(404);
        }

        // Puxa as calibrações mais recentes
        $calibrations = $asset->calibrations()->latest()->get();

        return view('assets.show', compact('laboratory', 'asset', 'calibrations'));
    }

    /**
     * Exibe o formulário de edição de peça (Apenas EDITOR+).
     */
    public function edit(Laboratory $laboratory, Asset $asset)
    {
        // ACL: Apenas EDITOR ou ADM pode editar
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR')) {
            abort(403, 'Acesso negado. Apenas Editores ou Administradores podem editar peças.');
        }
        if ($asset->laboratory_id !== $laboratory->id) { abort(404); }

        return view('assets.edit', compact('laboratory', 'asset'));
    }

    /**
     * Atualiza a peça no banco de dados (Apenas EDITOR+).
     */
    public function update(Request $request, Laboratory $laboratory, Asset $asset)
    {
        // ... (ACL permanece o mesmo) ...

        $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'unit' => 'required|string|max:10',
            'nominal_value' => 'nullable|numeric|max:9999999.9999',
            'image_path' => 'nullable|image|max:2048', // Arquivo de imagem é opcional na atualização
        ]);
        
        $data = $request->except('image_path');

        // 1. Lógica de Upload na Atualização
        if ($request->hasFile('image_path')) {
            // Deleta a imagem antiga, se existir
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }
            // Salva a nova imagem
            $path = $request->file('image_path')->store('assets', 'public');
            $data['image_path'] = $path;
        }

        $asset->update($data);
        
        return redirect()->route('laboratories.show', $laboratory)
                         ->with('success', 'Peça atualizada com sucesso!');
    }

    /**
     * Remove a peça do banco de dados (Apenas EDITOR+).
     */
    public function destroy(Laboratory $laboratory, Asset $asset)
    {
        // ACL: Apenas EDITOR ou ADM pode remover
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'EDITOR')) {
            abort(403, 'Acesso negado. Apenas Editores ou Administradores podem excluir peças.');
        }
        if ($asset->laboratory_id !== $laboratory->id) { abort(404); }

        $asset->delete();

        return redirect()->route('laboratories.show', $laboratory)
                         ->with('success', 'Peça excluída com sucesso.');
    }
}