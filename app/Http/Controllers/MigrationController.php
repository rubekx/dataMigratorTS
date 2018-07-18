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
use App\Dash\Solicitacao_Datas_Timestamp;
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
        $controller->migrateSolicitationDate();
        $controller->migrateSolicitationDateTimestamp();
        $controller->migrateSolicitationEncaminhamento();
        $controller->migrateSolicitationEncaminhamentoPaciente();
        $controller->migrateSolicitationRegulacao();
        $controller->migrateSolicitationResposta();
        $controller->migrateSolicitationSatisfacao();
        $controller->migrateSolicitationStatus();

        info('Done!');
    }

    public static function statusHistoryDate($solicitation_id, $status_id)
    {
        if (!empty($solicitation_id) && !empty($status_id)) {
            return StatusHistory::where('solicitation_id', $solicitation_id)->where('status_id', $status_id)->get('created_at');
        }
        return null;
    }

    public static function status($status_id)
    {
        if (!empty($status_id)) {
            if ($status_id == 27) {
                return 1;
            } elseif ($status_id == 28) {
                return 2;
            } elseif ($status_id == 29) {
                return 3;
            } elseif ($status_id == 30) {
                return 4;
            } elseif ($status_id == 31) {
                return 5;
            } elseif ($status_id == 32) {
                return 1;
            } elseif ($status_id == 33) {
                return 2;
            } elseif ($status_id == 34) {
                return 3;
            }
        }
        return null;
    }


    public function migrateSolicitationStatus()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get();

        info('Migrating solicitations Resposta table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Satisfacao::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new Satisfacao;
                $solicitacao->codigo = $solicitation->id;
            }

            $solicitacao->situacaoTeleconsultoria = $solicitation->status_id;
            $solicitacao->atrasoForaLimite = 0;
            $solicitacao->atrasoEtapa = ($solicitation->status_id == 7 || $solicitation->status_id == 9 || $solicitation->status_id == 11 || $solicitation->status_id == 13) ? 1 : 0;

            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationSatisfacao()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->where('status_id', 22)->get();

        info('Migrating solicitations Resposta table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Satisfacao::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new Satisfacao;
                $solicitacao->codigo = $solicitation->id;
            }

            $solicitacao->satisfacaoResposta = MigrationController::status($solicitation->evaluation->satisfaction_status_id);
            $solicitacao->classificacaoResposta = MigrationController::status($solicitation->evaluation->attendance_status_id);
            $solicitacao->criticasSugestoes = $solicitation->evaluation->description;
            $solicitacao->evitacaoEncaminhamento = $solicitation->evaluation->avoided_forwarding;
            $solicitacao->inducaoEncaminhamento = $solicitation->evaluation->induced_forwarding;

            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationResposta()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)
            ->whereNotIn('status_id', [3, 4, 6, 7, 8, 9, 10, 11, 19, 20, 24, 25])
            ->get();

        info('Migrating solicitations Resposta table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Resposta::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new Resposta;
                $solicitacao->codigo = $solicitation->id;
            }

            $solicitacao->codigoTeleconsultor = $solicitation->solicitationForward->consultant_profile_id;
            $solicitacao->solicitacaoRepetida = 0;
            $solicitacao->justificativaDevolucaoTeleconsultor = $solicitation->answers;
            $solicitacao->solicitacaoResposta = $solicitation->answers->direct_answer;
            $solicitacao->solicitacaoComplemento = $solicitation->answers->complement;
            $solicitacao->solicitacaoAtributos = $solicitation->answers->attributes;
            $solicitacao->solicitacaoEduPermanente = $solicitation->answers->permanent_education;
            $solicitacao->solicitacaoReferencia = $solicitation->answers->references;
            $solicitacao->estrategiaBusca = $solicitation->answers->tags;
            $solicitacao->solsofcod = $solicitation->answers->isSOF;
            $solicitacao->respostaAceita = 1;

            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationRegulacao()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get();

        info('Migrating solicitations Regulacao table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Regulacao::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new Regulacao;
                $solicitacao->codigo = $solicitation->id;
            }
            $solicitacao->codigoRegulador = $solicitation->solicitationForward->regulator_profile_id;
            $solicitacao->aceiteTelerregulacao = 1;

            if ($solicitacao->status_id == 20 || $solicitacao->status_id == 25) {
                $solicitacao->justificativaDevCan = $solicitation->observation->description;
            } else {
                $solicitacao->justificativaDevCan = null;
            }

            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationEncaminhamentoPaciente()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::select(
            'patients.cns as cns',
            'patients.mother_name as mother_name',
            'patient_forwards.patient_id as patient')
            ->join('patient_forwards', 'patient_forwards.solicitation_id', '=', 'solicitations.id')
            ->where('solicitations.updated_at', '>=', $min_date)
            ->whereNotNull('patient_forwards.patient_id')
            ->get();

        info('Migrating solicitations Encaminhamento Paciente table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = SolEncaminhamentoPaciente::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new SolEncaminhamentoPaciente;
                $solicitacao->solicitacao = $solicitation->id;
            }
            $solicitacao->paciente_mae = $solicitation->mother_name;
            $solicitacao->paciente_nome = $solicitation->patient->person->name;
            $solicitacao->paciente_nascimento = $solicitation->patient->person->birthday;
            $solicitacao->paciente_sexo = ($solicitation->patient->person->sex == 'F') ? 1 : 0;
            $solicitacao->paciente_cpf = $solicitation->patient->person->cpf;
            $solicitacao->paciente_cns = $solicitation->cns;

            try {

                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationEncaminhamento()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::has('patientForward')->where('solicitation.updated_at', '>=', $min_date)
            ->where()
            ->get();

        info('Migrating solicitations Encaminhamento table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = SolEncaminhamento::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new SolEncaminhamento;
                $solicitacao->codigo = $solicitation->id;
            }
            $solicitacao->intencaoEncaminhamento = $solicitation->patientForward->has_intention;
            $solicitacao->sugestaoEncaminhamento = 0;
            $solicitacao->codigoSugestaoEncaminhamento = 0;
            $solicitacao->cboEspecialidade = $solicitation->patientForward->cbo_id;
            $solicitacao->evitacaoEncaminhamento = $solicitation->evaluation->avoided_forwarding;
            $solicitacao->inducaoEncaminhamento = $solicitation->evaluation->induced_forwarding;

            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationDateTimestamp()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get();

        info('Migrating solicitations Date Timestamp table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Solicitacao_Datas_Timestamp::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new Solicitacao_Datas_Timestamp;
                $solicitacao->codigo = $solicitation->id;
            }
            $solicitacao->soldtabertura = $solicitation->created_at;
            $solicitacao->soldtenvio = MigrationController::statusHistoryDate($solicitation->id, 6);
            $solicitacao->regdtreceb = MigrationController::statusHistoryDate($solicitation->id, 10);
            $solicitacao->regdtdevol = MigrationController::statusHistoryDate($solicitation->id, 25);
            $solicitacao->regdtenvio = MigrationController::statusHistoryDate($solicitation->id, 10);
            $solicitacao->consdtacresp = MigrationController::statusHistoryDate($solicitation->id, 21);
            $solicitacao->consdtdevol = MigrationController::statusHistoryDate($solicitation->id, 24);
            $solicitacao->soldtenvresp = MigrationController::statusHistoryDate($solicitation->id, 5);
            $solicitacao->soldtsteleit = MigrationController::statusHistoryDate($solicitation->id, 21);
            $solicitacao->soldtavalin = MigrationController::statusHistoryDate($solicitation->id, 22);
            $solicitacao->soldtavalfim = MigrationController::statusHistoryDate($solicitation->id, 22);

            try {
                $solicitacao->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function migrateSolicitationDate()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('updated_at', '>=', $min_date)->get('created_at');

        info('Migrating solicitations Date table...');

        foreach ($solicitations as $solicitation) {
            $solicitacao = Solicitacao_Datas::where('codigo', '=', $solicitation->id)->get()->first();

            if ($solicitacao == NULL) {
                $solicitacao = new Solicitacao_Datas;
                $solicitacao->codigo = $solicitation->id;
            }
            $solicitacao->soldtabertura = $solicitation->created_at;
            $solicitacao->soldtenvio = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 6)));
            $solicitacao->regdtreceb = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 10)));
            $solicitacao->regdtdevol = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 25)));
            $solicitacao->regdtenvio = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 10)));
            $solicitacao->consdtacresp = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 21)));
            $solicitacao->consdtdevol = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 24)));
            $solicitacao->soldtenvresp = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 5)));
            $solicitacao->soldtsteleit = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 21)));
            $solicitacao->soldtavalin = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 22)));
            $solicitacao->soldtavalfim = date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 22)));


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

            if ($solicitacao == NULL) {
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

            if ($solicitacao == NULL) {
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

            if ($perfil == NULL) {
                $perfil = new Perfil;
                $perfil->codigo = $profile->id;
            }

            $perfil->pessoa = $profile->user->person->id;
            $perfil->cbo = $profile->cbo_id;
            $perfil->tipoProfissional = 0;
            if ($profile->role_id == 5) {
                $perfil->atuacao = 7;
            } elseif ($profile->role_id == 6) {
                $perfil->atuacao = 5;
            } elseif ($profile->role_id == 1) {
                $perfil->atuacao = 2;
            } else {
                $perfil->atuacao = $profile->role_id;
            }
            $perfil->ativo = ($profile->status_id == 2) ? 0 : $profile->status_id;
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

            if ($pessoa == NULL) {
                $pessoa = new Pessoa;
                $pessoa->codigo = $person->id;
            }
            if ($person->user != NULL) {
                $pessoa->nome = $person->name;
                $pessoa->sexo = ($person->sex == 'M') ? 0 : 1;
                $pessoa->nascimento = $person->birthday;
                $pessoa->telefone = $person->telphone;
                $pessoa->celular = $person->celphone;
                $pessoa->email = $person->user->email;
                $pessoa->cpf = $person->cpf;
                $pessoa->dataInclusao = date('Y-m-d', strtotime($person->created_at));
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
        $teams = Team::all();

        info('Migrating teams table...');

        foreach ($teams as $team) {
            $equipe = Equipe::where('codigo', '=', $team->id)->get()->first();

            if ($equipe == NULL) {
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

        info('Migrating units table...');

        foreach ($units as $unit) {
            $ubs = Unidade::where('cnes', '=', $unit->cnes)->get()->first();

            if ($ubs == NULL) {
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
