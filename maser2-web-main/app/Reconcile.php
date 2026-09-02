<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reconcile extends Model
{

	public function TratarExceptionTipoVeiculo($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'PRIMARY',
					'tabela' => 'tipos de veículo'
				],
				[
					'constraint' => 'veiculo_cod_tipo_veiculo_foreign',
					'tabela' => 'veículos'
				],
			);

		$msgerro = $this->MontaMsgCrudException('Tipos de Veículo', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function TratarExceptionColetaFixa($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'coleta_fixa_bloq_coleta_fixa_id_foreign',
					'tabela' => 'coletas fixas - bloqueios'
				],
				[
					'constraint' => 'coleta_coleta_fixa_id_foreign',
					'tabela' => 'coletas'
				],
			);

		$msgerro = $this->MontaMsgCrudException('Coletas Fixas', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function TratarExceptionColeta($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'coleta_nf_coleta_id_foreign',
					'tabela' => 'notas fiscais'
				],
				[
					'constraint' => 'coleta_pos_coleta_id_foreign',
					'tabela' => 'coletas posição'
				],
			);

		$msgerro = $this->MontaMsgCrudException('Coletas', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function TratarExceptionEmpresa($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'PRIMARY',
					'tabela' => 'empresa'
				],
			);

		$msgerro = $this->MontaMsgCrudException('Empresa', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function TratarExceptionDistanceMatrix($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'PRIMARY',
					'tabela' => 'serviços api'
				],
			);

		$msgerro = $this->MontaMsgCrudException('Serviços API', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function TratarExceptionVeiculo($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'PRIMARY',
					'tabela' => 'veículo'
				],
				[
					'constraint' => 'coleta_placa_coleta_foreign',
					'tabela' => 'coletas'
				],
				[
					'constraint' => 'coleta_placa_entrega_foreign',
					'tabela' => 'coletas'
				],
				[
					'constraint' => 'coleta_placa_baldeacao_foreign',
					'tabela' => 'coletas'
				]
			);

		$msgerro = $this->MontaMsgCrudException('Veículo', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function TratarExceptionUsers($funcao, $e)
	{
		$funcao_str = $this->CrudExtenso($funcao);

		$constraints =
			array(
				[
					'constraint' => 'motorista_user_id_foreign',
					'tabela' => 'motorista'
				],
				[
					'constraint' => 'wisdom_user_user_id_foreign',
					'tabela' => 'sabedoria'
				],
				[
					'constraint' => 'notif_user_id_foreign',
					'tabela' => 'notificações'
				]
			);

		$msgerro = $this->MontaMsgCrudException('Usuários', $funcao_str, $constraints, $e);

		return $msgerro;
	}

	public function CrudExtenso($funcao)
	{

		$funcao_str = 'executar esta operação com';

		if (strtolower($funcao) == 'delete') {

			$funcao_str = 'excluir';
		} else {

			if (strtolower($funcao) == 'update') {

				$funcao_str = 'alterar';
			} else {

				if (strtolower($funcao) == 'insert') {
					$funcao_str = 'inserir';
				}
			}
		}

		return $funcao_str;
	}


	public function MontaMsgCrudException($tab_origem, $funcao_str, $constraints, $e)
	{

		$msgerro = 'Ocorreu um erro inesperado ao executar a operação "' . $funcao_str . '"' . ' na tabela: ' . $tab_origem .
			' => ' . 'Erro: ' . $e->getMessage();

		foreach ($constraints as $regconst) {

			if ($funcao_str === "inserir") {

				if (strpos($e, $regconst['constraint'])) {
					$msgerro = 'Você não pode ' . $funcao_str . ' este registro, pois já está cadastrado.';
					break;
				}
			} else {

				if (strpos($e, $regconst['constraint'])) {
					$msgerro = 'Você não pode ' . $funcao_str . ' este registro, pois existem referências a ele na tabela ' . $regconst['tabela'] . '.';
					break;
				}
			}
		}

		return $msgerro;
	}
}
