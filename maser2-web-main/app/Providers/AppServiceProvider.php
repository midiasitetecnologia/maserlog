<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Code\Validator\Cnpj;
use Code\Validator\Cpf;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
			$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
		}
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Força HTTPS em produção
        if (MASER_APP_ENV != 'local') {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }

        // Validação customizada para CPF/CNPJ
        Validator::extend('documento', function ($attribute, $value, $parameters, $validator) {
            $documentValidator = $parameters[0] == 'cpf' ? new Cpf() : new Cnpj();
            return $documentValidator->isValid($value);
        });

        // Validação customizada maior que
        Validator::extend('maior_que', function ($attribute, $value, $parameters) {
            return isset($parameters[0]) ? rgStringToFloat($value) > rgStringToFloat($parameters[0]) : true;
        });

        // Validação customizada menor que
        Validator::extend('menor_que', function ($attribute, $value, $parameters) {
            return isset($parameters[0]) ? rgStringToFloat($value) < rgStringToFloat($parameters[0]) : true;
        });

        // Validação customizada maior ou igual
        Validator::extend('maior_igual', function ($attribute, $value, $parameters) {
            return isset($parameters[0]) ? rgStringToFloat($value) >= rgStringToFloat($parameters[0]) : true;
        });

        // Validação customizada menor ou igual
        Validator::extend('menor_igual', function ($attribute, $value, $parameters) {
            return isset($parameters[0]) ? rgStringToFloat($value) <= rgStringToFloat($parameters[0]) : true;
        });

        // Validação customizada de hora maior ou igual
        Validator::extend('hora_maior_igual', function ($attribute, $value, $parameters) {
            return isset($parameters[0]) ? strTotime($value) >= strTotime($parameters[0]) : true;
        });

        // Validação customizada de hora maior
        Validator::extend('hora_maior', function ($attribute, $value, $parameters) {
            return isset($parameters[0]) ? strTotime($value) > strTotime($parameters[0]) : true;
        });
    }
}