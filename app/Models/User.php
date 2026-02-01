<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;



// A classe User deve implementar MustVerifyEmail para a funcionalidade de confirmação de email
class User extends Authenticatable implements MustVerifyEmail 
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos de Metrologia (Laboratórios e Membros)
    |--------------------------------------------------------------------------
    */

    public function memberships()
    {
        // Um usuário pode ter múltiplas associações (Membership) com laboratórios.
        // Nossos helpers de permissão usam este relacionamento
        return $this->hasMany(Membership::class);
    }

    public function laboratories()
    {
        // Recupera os laboratórios dos quais ele é membro.
        return $this->belongsToMany(Laboratory::class, 'memberships')
                    ->withPivot('role'); 
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de Controle de Acesso (ACL)
    |--------------------------------------------------------------------------
    */

    /**
     * Obtém a função (role) do usuário em um laboratório específico.
     * @param int $laboratoryId
     * @return string|null 'ADM', 'EDITOR', 'VISUALIZADOR', ou null se não for membro.
     */
    public function getRoleInLaboratory(int $laboratoryId): ?string
    {
        // Retorna o valor da coluna 'role' da tabela memberships
        return $this->memberships()
                    ->where('laboratory_id', $laboratoryId)
                    ->value('role'); 
    }

    /**
     * Verifica se o usuário tem a permissão mínima no laboratório.
     * @param int $laboratoryId
     * @param string $requiredRole (ADM, EDITOR ou VISUALIZADOR)
     * @return bool
     */
    public function hasMinRoleInLaboratory(int $laboratoryId, string $requiredRole): bool
    {
        $userRole = $this->getRoleInLaboratory($laboratoryId);
        
        if (!$userRole) {
            return false; // Não é membro
        }

        // Definindo a hierarquia: ADM > EDITOR > VISUALIZADOR
        $roleHierarchy = [
            'VISUALIZADOR' => 1,
            'EDITOR' => 2,
            'ADM' => 3,
        ];

        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        // O usuário tem permissão se seu nível for igual ou superior ao nível requerido
        return $userLevel >= $requiredLevel;
    }
}