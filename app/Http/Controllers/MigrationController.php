<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Dash\Pessoa;
use App\Dash\Unidade;
use App\Dash\Equipe;
use App\Dash\Perfil;
use App\Dash\CIAP as CIAP_DASH;
use App\Dash\CID as CID_DASH;
use App\Dash\Solicitacao;
use App\Dash\Solicitacao_Status;
use App\Dash\Solicitacao_Datas;
use App\Dash\Solicitacao_CIs;
use App\Dash\Resposta;
use App\Dash\Regulacao;
use App\Dash\SolEncaminhamentoPaciente;
use App\Dash\SolEncaminhamento;
use App\Dash\Satisfacao;

use App\Person;
use App\User;
use App\FU;
use App\City;
use App\Unit;
use App\Team;
use App\Profile;
use App\CBO;
use App\CIAP;
use App\CID;
use App\Solicitation;
use App\Answer;
use App\SolicitationForward;
use App\SolicitationObservation;
use App\ProfileTeam;
use App\Patient;
use App\PatientForward;
use App\CenterProfile;
use App\Evaluation;
use App\StatusHistory;

class MigrationController extends Controller
{

    public static function populateDatabase()
    {
        $controller = new MigrationController;
        info('Start migration data to SMGT...');
        $controller->migrateUnits();
        $controller->migrateTeams();
        $controller->migratePeople();
        $controller->migrateProfiles();
        $controller->migrateSolicitation();
        $controller->migrateSolicitationCiapCid();
        info('Done!');
    }

    public function migrateSolicitationDate()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get();

        info('Migrating solicitations data table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Solicitacao_CIs::where('codigo', '=', $solicitation->id)->get()->first();
            
            if($solicitacao == NULL) {
                $solicitacao = new Solicitacao_CIs;
                $solicitacao->codigo = $solicitation->id;
            }

            
            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationCiapCid()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get();

        info('Migrating solicitations CIAP CID table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Solicitacao_CIs::where('codigo', '=', $solicitation->id)->get()->first();
            
            if($solicitacao == NULL) {
                $solicitacao = new Solicitacao_CIs;
                $solicitacao->codigo = $solicitation->id;
            }

            $solicitacao->ciap1 = $solicitation->ciap1->code;
            $solicitacao->ciap2 = $solicitation->ciap2->code;
            $solicitacao->ciap3 = $solicitation->ciap3->code;
            $solicitacao->cid1 = $solicitation->cid1->code;
            $solicitacao->cid2 = $solicitation->cid2->code;
            
            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitation()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get();

        info('Migrating solicitations table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Solicitacao::where('codigo', '=', $solicitation->id)->get()->first();
            
            if($solicitacao == NULL) {
                $solicitacao = new Solicitacao;
                $solicitacao->codigo = $solicitation->id;
            }

            $solicitacao->solicitacaoTipo = ($solicitation->type_id == 52) ? 2 : 1;
            $solicitacao->solcitacao = $solicitation->description;
            $solicitacao->solicitacaoAtivo = 0;
            $solicitacao->codigoSolicitante = $solicitation->profile_id;
            $solicitacao->pacienteAtivo = ($solicitation->patientForward != null) ? 1 : 0;
            
            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateProfiles()
    {
        $profiles = Profile::all();

        info('Migrating profiles table...');

        foreach ($profiles as $profile) {
            $perfil = Perfil::where('codigo', '=', $profile->id)->get()->first();
            
            if($perfil == NULL) {
                $perfil = new Perfil;
                $perfil->codigo = $profile->id;
            }

            $perfil->pessoa = $profile->user->person->id;
            $perfil->cbo = $profile->cbo_id;
            $perfil->tipoProfissional = 0;
            if ($profile->role_id == 5) {
                $perfil->atuacao =  7;
            }elseif($profile->role_id == 6){
                $perfil->atuacao =  5;
            }elseif($profile->role_id == 1){
                $perfil->atuacao = 2;
            }else{
                $perfil->atuacao = $profile->role_id;
            }               
            $pessoa->ativo = ($profile->status_id == 2 ) ? 0 : $profile->status_id;
            $perfil->equipe = ($profile->role_id == 7) ? $profile->profile_team->team_id : 0;
            $perfil->dataAtualizacao = date('Y-m-d');
            try {
                $perfil->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }


    public function migratePeople()
    {
        $people = Person::all();

        info('Migrating people table...');

        foreach ($people as $person) {
            $pessoa = Pessoa::where('cpf', '=', $person->cpf)->get()->first();
            
            if($pessoa == NULL) {
                $pessoa = new Pessoa;
                $pessoa->codigo = $person->id;
            }
            if($person->user != NULL)
            {
                $pessoa->nome = $person->name;
                $pessoa->sexo = ($person->sex == 'M') ? 0 : 1;
                $pessoa->nascimento = $person->birthday;
                $pessoa->telefone = $person->telphone;
                $pessoa->celular = $person->celphone;   
                $pessoa->email = $person->user->email;
                $pessoa->cpf = $person->cpf;
                $pessoa->dataInclusao = date('Y-m-d', srtotime($person->created_at));
                $pessoa->dataAtualizacao = date('Y-m-d');

                try {
                    $pessoa->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
    }

    public function migrateTeams()
    {
        $teams = Teams::all();

        info('Migrating teams table...');

        foreach ($teams as $team) {
            $equipe = Equipe::where('codigo', '=', $team->id)->get()->first();
            
            if($equipe == NULL) {
                $equipe = new Equipe;
                $equipe->codigo = $team->id;
            }
            $equipe->nome = $team->description;
            $equipe->ine = $team->ine;
            
            $equipe->ativo = $team->status_id;
            $equipe->dataAtualizacao = date('Y-m-d');

            try {
                $equipe->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
    
    public function migrateUnits()
    {
        $units = Unit::all();
        $originalQnt = Unit::count();

        $saveData = True;
        $savedQnt = 0;
        $confirmedQtd = 0;
        $notSaved = array();

        info('Migrating units table...');

        foreach ($units as $unit) {
            $ubs = Unidade::where('cnes', '=', $unit->cnes)->get()->first();
            
            if($ubs == NULL) {
                $ubs = new Unidade;
                $ubs->codigo = $unit->id;
            }
            $ubs->nome = $unit->description;
            $ubs->endereco = $unit->address;
            $ubs->telefone = $unit->telphone;
            $ubs->ativo = $unit->status_id;
            $ubs->dataAtualizacao = date('Y-m-d');

            try {
                $ubs->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
