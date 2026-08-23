<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LaboratoryController; 
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CalibrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rota Pública do Digital Asset Links (TWA / Android PWA)
|--------------------------------------------------------------------------
| Esta rota DEVE ficar fora de qualquer middleware de autenticação
| para que o Android consiga validar o aplicativo instalado no celular.
*/
Route::get('.well-known/assetlinks.json', function () {
    $path = public_path('.well-known/assetlinks.json');
    
    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/json'
    ]);
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas e Dashboard (com Middlewares)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// O Dashboard exige autenticação ('auth') E e-mail verificado ('verified')
Route::get('/dashboard', function () {
    return view('dashboard'); 
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Exigem autenticação)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Rotas de Perfil (do Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Rotas de Laboratórios, Membros, Peças e Calibrações (Exigem E-mail verificado)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['verified'])->group(function () {
        
        // 1. Rotas de Gerenciamento de Membros (ACL)
        Route::get('laboratories/{laboratory}/members', [LaboratoryController::class, 'members'])->name('laboratories.members');
        Route::post('laboratories/{laboratory}/members', [LaboratoryController::class, 'addMember'])->name('laboratories.addMember');
        // Rota para ATUALIZAR a função (role) de um membro
        Route::put('laboratories/{laboratory}/members/{membership}', [LaboratoryController::class, 'updateMemberRole'])->name('laboratories.updateMemberRole');
        Route::delete('laboratories/{laboratory}/members/{membership}', [LaboratoryController::class, 'removeMember'])->name('laboratories.removeMember');
        
        // 2. Rotas RESTful para Laboratórios
        Route::resource('laboratories', LaboratoryController::class);
        
        // 3. Rotas de ATIVOS (PEÇAS) - Aninhadas ao Laboratório
        Route::resource('laboratories.assets', AssetController::class)
             ->except(['index']);
        Route::get('laboratories/{laboratory}/assets', [AssetController::class, 'index'])->name('laboratories.assets.index');

        // 4. ROTAS DE CALIBRAÇÃO - Aninhadas ao Asset
        Route::resource('laboratories.assets.calibrations', CalibrationController::class)
             ->only(['create', 'store', 'show']); 

        // Rota para EXPORTAR o Relatório GUM (Impressão Nativa CSS Print)
        Route::get('laboratories/{laboratory}/assets/{asset}/calibrations/{calibration}/pdf', 
         [CalibrationController::class, 'exportPdf'])
            ->name('calibrations.exportPdf');
        
    });

});

// Rotas de Autenticação
require __DIR__.'/auth.php';