<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests;
use App\Http\Requests\UserRequest;
use App\Reconcile;
use App\User;
use DB;

class UserController extends Controller
{
    public function index()
    {
        if (Auth()->user()->user_type == 'C') {
            $users = User::where('id', '=', Auth()->user()->id)->get();
        } else {
            $users = User::get();
        }

        return ['status' => true, 'users' => $users];
    }

    public function show($id)
    {
        if (Auth()->user()->user_type == 'C') {
            $id = Auth()->user()->id;
        }

        $users = User::select(
            'users.*',
            'cliente.nome as nome_cliente',
            'cliente.codigo as codigo_cliente',
            'empresa.nome as nome_empresa'
        )
            ->leftJoin('cliente', 'cliente.id', '=', 'users.cliente_id')
            ->leftJoin('empresa', 'empresa.codigo', '=', 'cliente.empresa')
            ->where('users.id', $id)
            ->get();

        return ['status' => true, 'users' => $users];
    }

    public function edit($id)
    {
        if (Auth()->user()->user_type == 'C') {
            $id = Auth()->user()->id;
        }

        $users = User::select(
            'users.*',
            'cliente.nome as nome_cliente',
            'cliente.codigo as codigo_cliente',
            'empresa.nome as nome_empresa'
        )
            ->leftJoin('cliente', 'cliente.id', '=', 'users.cliente_id')
            ->leftJoin('empresa', 'empresa.codigo', '=', 'cliente.empresa')
            ->where('users.id', $id)
            ->get();

        return ['status' => true, 'users' => $users];
    }

    public function store(Requests\UserRequest $request)
    {
        if (Auth()->user()->user_type == 'C') {
            return redirect('pages/not-authorized');
        }

        $data = $request->all();

        //Geramos uma senha, está senha será enviada por e-mail.
        if (($data['user_type'] != 'M') && ($data['active'] == 'N')) {

            $next_id = DB::select("SHOW TABLE STATUS LIKE 'users'");
            $next_id = $next_id[0]->Auto_increment;

            $data['password'] = 'maser' . $next_id;
        }

        $data['password'] = Hash::make($data['password']);
        $data['ass_user_id'] = Auth()->user()->id;

        try {
            User::create($data);
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (Auth()->user()->user_type == 'C') {
            $id = Auth()->user()->id;
        }

        if (!($users = User::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new UserRequest;
        $erros = $validator->ValidarDadosApiUser($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            if (isset($data['password'])) {
                if ($users['password'] != $data['password']) {
                    $data['password'] = bcrypt($data['password']);
                }
            }

            try {
                $users->fill($data);
                $users->save();
            } catch (\Exception $e) {
                $resultado['message'][0] = $e->getMessage();
                return ['status' => false, 'erros' => $resultado];
            }

            return ['status' => true];
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->all();

        if (Auth()->user()->user_type == 'C') {
            $data['id'] = Auth()->user()->id;
        }

        if (!($users = User::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $users['ass_user_id'] = Auth()->user()->id;
            $users->save();
            $users->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionUsers('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function getUsersMotorista(Request $request)
    {
        $user_id = $request->get('user_id');

        $users = DB::table('users')
            ->select('users.id', 'users.name', 'users.email')
            ->leftJoin('motorista', 'motorista.user_id', 'users.id')
            ->where('users.user_type', '=', 'M')
            ->where(function ($query) use ($user_id) {
                $query->whereNull('motorista.user_id')
                    ->orWhere('motorista.user_id', '=', $user_id);
            })
            ->get();

        return ['status' => true, 'users' => $users];
    }

    public function gerarIdLogin()
    {

        $idLogin = rgGenerateStrKey(4);

        return ['status' => true, 'idLogin' => $idLogin];
    }
}
