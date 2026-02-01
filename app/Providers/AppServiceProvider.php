<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. FORÇAR HTTPS
        // Essencial para que os links de e-mail e assets funcionem via Cloudflare Tunnel.
        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // 2. CONFIGURAÇÃO DE PROXY CONFIÁVEL (CORREÇÃO DO ERRO 403)
        // Isso resolve o "Invalid Signature" ao dizer ao Laravel para confiar nos 
        // cabeçalhos de encaminhamento (X-Forwarded-Proto) enviados pelo túnel.
        Request::setTrustedProxies(
            ['0.0.0.0/0', '2001:db8::/32'], 
            Request::HEADER_X_FORWARDED_FOR | 
            Request::HEADER_X_FORWARDED_HOST | 
            Request::HEADER_X_FORWARDED_PORT | 
            Request::HEADER_X_FORWARDED_PROTO | 
            Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // 3. INICIALIZAÇÃO SEGURA DAS FEATURES DO FORTIFY
        // Adia a execução até que o Fortify esteja pronto, evitando erro de "Class not found".
        $this->app->afterResolving(Fortify::class, function () {
            Fortify::features([
                Features::registration(),
                Features::resetPasswords(),
                Features::emailVerification(), // Habilita a verificação de e-mail
            ]);
        });
    }
}