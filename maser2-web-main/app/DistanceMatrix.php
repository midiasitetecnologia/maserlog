<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DistanceMatrix extends Model
{
    protected $table = 'distance_matrix';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'api_service',
        'api_account',
        'api_key',
        'api_limit',
        'api_usage',
        'api_priority',
        'active',
        'ass_user_id'
    ];

    /**
     * Retorna o serviço e a API key com menor uso, se disponível.
     * Se não houver nenhuma chave disponível com uso ativo, retorna a chave padrão do Google com o serviço de "Distance Matrix API".
     * 
     * Explicação:
     * Estamos escolhendo o serviço e a chave de API cadastrada com a menor utilização. 
     * O Google Cloud oferece apenas uma certa quantidade de requisições gratuitas "10.000", 
     * Outros serviços, como MapBox tambem tem politicas parecidas, 
     * e após passar esses valor, a cobrança das solicitações extras se torna muito elevada.
     * Desta forma, vamos fazer o balanceamento em contas diferentes para aproveitar os benefícios.
     *
     * @return string
     */
    public function getServiceRoutes()
    {
        $this->resetMonthlyUsage();

        // Busca o Serviço e a API key ativa considerando prioridade, menor uso e dentro do limite.
        $distanceMatrix = $this->where('active', 'S')
            ->whereColumn('api_usage', '<', 'api_limit')
            ->orderBy('api_priority', 'asc')
            ->orderBy('api_usage', 'asc')
            ->first();

        if (!$distanceMatrix) {
            return [
                'api_service' => 'google_cloud',
                'api_key' => rgGetKeyGoogleMapsApi(),
            ];
        }

        // Se encontrou, usa o Serviço e a API key da tabela
        $api_key = $distanceMatrix->api_key;
        $api_service = $distanceMatrix->api_service;

        // Incrementa o uso da API key com a query no banco para evitar concorrência (Incremento atômico)
        $this->where('id', $distanceMatrix->id)->increment('api_usage');

        return [
            'api_service' => $api_service,
            'api_key' => $api_key,
        ];
    }

    private function resetMonthlyUsage()
    {
        // Verifica a última requisição considerando o campo updated_at da tabela
        $latestRequest = $this->where('active', 'S')->orderBy('updated_at', 'desc')->value('updated_at');

        if ($latestRequest) {
            $lastMonth = \Carbon\Carbon::parse($latestRequest)->format('Y-m');
            $currentMonth = now()->format('Y-m');

            if ($lastMonth !== $currentMonth) {
                $this->where('active', 'S')->get()->each(function ($item) {
                    $item->update([
                        'api_usage' => 0,
                    ]);
                });
            }
        }
    }
}
