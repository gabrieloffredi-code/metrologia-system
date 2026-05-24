<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryController extends Controller
{
    /**
     * Exibe a lista de todos os laboratórios do qual o usuário é membro.
     */
    public function index()
    {
        $laboratories = Auth::user()->laboratories; 
        return view('laboratories.index', compact('laboratories'));
    }

    /**
     * Exibe o formulário para criar um novo laboratório.
     */
    public function create()
    {
        return view('laboratories.create');
    }

    /**
     * Armazena um novo laboratório e define o criador como ADM.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $laboratory = Laboratory::create($request->only('name', 'description'));
        
        // Define o usuário criador como ADM
        Membership::create([
            'user_id' => Auth::id(),
            'laboratory_id' => $laboratory->id,
            'role' => 'ADM', 
        ]);

        return redirect()->route('laboratories.index')
                         ->with('success', 'Laboratório criado com sucesso! Você é o Administrador.');
    }

    /**
     * Exibe um laboratório específico e suas peças.
     */
    public function show(Laboratory $laboratory)
    {
        $user = Auth::user();
        $role = $user->getRoleInLaboratory($laboratory->id);
        
        if (!$role) {
            abort(403, 'Acesso negado. Você não é membro deste laboratório.');
        }

        $assets = $laboratory->assets()->get(); 

        return view('laboratories.show', compact('laboratory', 'assets', 'role'));
    }

    /**
     * Exibe o formulário para edição (Apenas ADM).
     */
    public function edit(Laboratory $laboratory)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
             abort(403, 'Acesso negado. Apenas Administradores podem editar.');
        }
        
        return view('laboratories.edit', compact('laboratory'));
    }

    /**
     * Atualiza o laboratório (Apenas ADM).
     */
    public function update(Request $request, Laboratory $laboratory)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
             abort(403, 'Acesso negado.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $laboratory->update($request->only('name', 'description'));

        return redirect()->route('laboratories.show', $laboratory)
                         ->with('success', 'Laboratório atualizado com sucesso.');
    }

    /**
     * Remove o laboratório (Apenas ADM).
     */
    public function destroy(Laboratory $laboratory)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
             abort(403, 'Acesso negado. Apenas Administradores podem excluir.');
        }

        $laboratory->delete();

        return redirect()->route('laboratories.index')
                         ->with('success', 'Laboratório excluído com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Gerenciamento de Membros
    |--------------------------------------------------------------------------
    */

    /**
     * Exibe a tela de gerenciamento de membros (Apenas ADM).
     */
    public function members(Laboratory $laboratory)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
            abort(403, 'Acesso negado. Apenas Administradores podem gerenciar membros.');
        }

        $members = $laboratory->members()->withPivot('id')->get(); 

        return view('laboratories.members', compact('laboratory', 'members'));
    }

    /**
     * Adiciona um novo membro ao laboratório (Apenas ADM).
     */
    public function addMember(Request $request, Laboratory $laboratory)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
            return back()->with('error', 'Apenas Administradores podem adicionar membros.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:ADM,EDITOR,VISUALIZADOR',
        ]);
        
        $userToAdd = User::where('email', $request->email)->first();

        if ($laboratory->members()->where('user_id', $userToAdd->id)->exists()) {
             return back()->with('error', 'Este usuário já é membro do laboratório.');
        }
        
        Membership::create([
            'user_id' => $userToAdd->id,
            'laboratory_id' => $laboratory->id,
            'role' => $request->role,
        ]);

        return back()->with('success', "O usuário {$userToAdd->name} foi adicionado como {$request->role}.");
    }

    /**
     * Updates the role of an existing member (Apenas ADM).
     */
    public function updateMemberRole(Request $request, Laboratory $laboratory, Membership $membership)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
            return back()->with('error', 'Apenas Administradores podem alterar funções de membros.');
        }

        if ($membership->laboratory_id != $laboratory->id) {
            abort(404);
        }

        $request->validate(['role' => 'required|in:ADM,EDITOR,VISUALIZADOR']);
        
        if ($membership->user_id == Auth::id() && $request->role != 'ADM') {
             $otherAdmins = $laboratory->members()->where('role', 'ADM')->where('user_id', '!=', Auth::id())->count();
             if ($otherAdmins == 0) {
                 return back()->with('error', 'Não é possível rebaixar o único Administrador do laboratório.');
             }
        }

        $membership->update(['role' => $request->role]);

        $userName = User::find($membership->user_id)->name;

        return back()->with('success', "Função de {$userName} atualizada para {$request->role} com sucesso.");
    }

    /**
     * Remove um membro (Apenas ADM).
     */
    public function removeMember(Laboratory $laboratory, Membership $membership)
    {
        if (!Auth::user()->hasMinRoleInLaboratory($laboratory->id, 'ADM')) {
            return back()->with('error', 'Apenas Administradores podem remover membros.');
        }
        
        if ($membership->laboratory_id != $laboratory->id) {
            abort(404);
        }

        if ($membership->user_id == Auth::id() && $membership->role == 'ADM') {
            $otherAdmins = $laboratory->members()->where('role', 'ADM')->where('user_id', '!=', Auth::id())->count();
            if ($otherAdmins == 0) {
                 return back()->with('error', 'Não é possível remover o único Administrador do laboratório.');
            }
        }
        
        $membership->delete();
        $userName = User::find($membership->user_id)->name;

        return back()->with('success', "Membro ({$userName}) removido com sucesso.");
    }
}