<?php

namespace App\Http\Controllers;

use App\Status;
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
use App\Dash\StatusSolicitacao;
use App\Dash\Solcod_Ibge;
use App\Dash\SmgtUpdates;
use App\Dash\Solcitacao_Tempo;

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

/**
 * Class MigrationController
 * @package App\Http\Controllers
 */
class MigrationController extends Controller
{

    /**
     *
     */
    public static function populateDatabase()
    {
        $add = 0;
        $upd = 0;
        $controller = new MigrationController;
        info('Start migration data to SMGT...');
        $date = date('Y-m-d H:i:s');

        $arr = $controller->migrateUnits();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateTeams();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migratePeople();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateProfiles();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        MigrationController::smgtUpdates(2, $date, $add, $upd);

        $add = 0;
        $upd = 0;
        $date = date('Y-m-d H:i:s');

        $arr = $controller->migrateSolicitation();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $controller->migrateSolicitationClass();

        $arr = $controller->migrateSolicitationCiapCid();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationDate();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationTempo();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationDateTimestamp();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationEncaminhamento();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationEncaminhamentoPaciente();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationRegulacao();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationResposta();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationSatisfacao();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationStatus();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        $arr = $controller->migrateSolicitationSolcodIbge();
        $add = $add + $arr['add'];
        $upd = $upd + $arr['upd'];

        MigrationController::smgtUpdates(1, $date, $add, $upd);
        info('Done!');
    }

    /**
     * @param $t
     * @param $date
     * @param $add
     * @param $upd
     */
    public static function smgtUpdates($t, $date, $add, $upd)
    {
        $atualizacao = new SmgtUpdates;
        if ($t == 1) {
            $atualizacao->tabela = 1;
        } else {
            $atualizacao->tabela = 2;
        }
        $atualizacao->usuario = 7;
        $atualizacao->tempoInicio = $date;
        $atualizacao->tempoFim = date('Y-m-d H:i:s');
        $atualizacao->registroAdicionado = $add;
        $atualizacao->registroAtualizado = $upd;
        $atualizacao->registroIgnorado = 0;
        $atualizacao->status = 1;
        $atualizacao->log = 'sucesso';
        $atualizacao->save();
    }

    /**
     * @param $solicitation_id
     * @param $status_id
     * @return null
     */
    public static function statusHistoryDate($solicitation_id, $status_id)
    {
        if (!empty($solicitation_id) && !empty($status_id)) {
            $statusHistoryDate = StatusHistory::where('solicitation_id', $solicitation_id)->where('status_id', $status_id)->get(['created_at'])->last();
            if ($statusHistoryDate) {
                return $statusHistoryDate['created_at'];
            }
        }
        return null;
    }

    /**
     * @param $solicitation_id
     * @param $status_id
     * @return null
     */
    public static function statusHistoryDateLate($solicitation_id, $status_id)
    {
        if (!empty($solicitation_id) && !empty($status_id)) {
            if (in_array($status_id, [8, 10, 12])) {
                if (StatusHistory::where('solicitation_id', $solicitation_id)->where('status_id', $status_id)->doesntExist()) {
                    $status_id = $status_id + 1;
                }
            }
            $statusHistoryDate = StatusHistory::where('solicitation_id', $solicitation_id)->where('status_id', $status_id)->get(['created_at'])->last();
            if ($statusHistoryDate) {
                //                info($statusHistoryDate['created_at']);
                return $statusHistoryDate['created_at'];
            }
        }
        return null;
    }

    /**
     * @param $status_id
     * @return int|null
     */
    public static function status($status_id)
    {
        if (!empty($status_id)) {
            if ($status_id == 1) {
                return 1;
            } elseif ($status_id == 2) {
                return 0;
            } elseif ($status_id == 3) {
                return 1;
            } elseif ($status_id == 4) {
                return 2;
            } elseif ($status_id == 5) {
                return 3;
            } elseif ($status_id == 6) {
                return 4;
            } elseif ($status_id == 7) {
                return 5;
            } elseif ($status_id == 8) {
                return 6;
            } elseif ($status_id == 9) {
                return 7;
            } elseif ($status_id == 10) {
                return 8;
            } elseif ($status_id == 11) {
                return 9;
            } elseif ($status_id == 12) {
                return 10;
            } elseif ($status_id == 13) {
                return 11;
            } elseif ($status_id == 14) {
                return 12;
            } elseif ($status_id == 15) {
                return 13;
            } elseif ($status_id == 16) {
                return 14;
            } elseif ($status_id == 18) {
                return 16;
            } elseif ($status_id == 19) {
                return 17;
            } elseif ($status_id == 20) {
                return 18;
            } elseif ($status_id == 21) {
                return 19;
            } elseif ($status_id == 22) {
                return 20;
            } elseif ($status_id == 23) {
                return 21;
            } elseif ($status_id == 24) {
                return 4;
            } elseif ($status_id == 25) {
                return 22;
            } elseif ($status_id == 27) {
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


    /**
     *
     */
    public function migrateSolicitationSolcodIbge()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));
        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->get();

        info('Migrating solicitations SolcodIbge table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Solcod_Ibge::where('codigo', '=', $solicitation->id)->get()->first();
                if ($solicitacao == NULL) {
                    $solicitacao = new Solcod_Ibge;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                info($solicitation->id);
                $solicitacao->ibge = $solicitation->profile->profile_team != null ?  $solicitation->profile->profile_team->team->unit->city->ibge : null;
                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }

        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationStatus()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->get();

        info('Migrating solicitations Status table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = StatusSolicitacao::where('codigo', '=', $solicitation->id)->get()->first();
                if ($solicitacao == NULL) {
                    $solicitacao = new StatusSolicitacao;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                $solicitacao->situacaoTeleconsultoria = MigrationController::status($solicitation->status_id);
                $solicitacao->atrasoForaLimite = 0;
                $solicitacao->atrasoEtapa = ($solicitation->status_id == 7 || $solicitation->status_id == 9 || $solicitation->status_id == 11 || $solicitation->status_id == 13) ? 1 : 0;

                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationSatisfacao()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->where('status_id', 22)
            ->get();

        info('Migrating solicitations Satisfacao table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            //            info($solicitation->id);
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                if (Evaluation::where('solicitation_id', '=', $solicitation->id)->exists()) {
                    $solicitacao = Satisfacao::where('codigo', '=', $solicitation->id)->get()->first();

                    if ($solicitacao == NULL) {
                        $solicitacao = new Satisfacao;
                        $solicitacao->codigo = $solicitation->id;
                        $j++;
                    }
                    $i++;
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
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationResposta()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->whereNotIn('status_id', [3, 4, 6, 7, 8, 9, 19, 20, 24, 25])
            ->get();

        info('Migrating solicitations Resposta table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            //            info($solicitation->id);
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Resposta::where('codigo', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitation->has('solicitationBySearch');
                    $solicitacao = new Resposta;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                if ($solicitation->type_id == 53) {
                    $relation = 'answer';
                    if ($solicitation->solicitationBySearch->answer == null) {
                        $relation = 'faq';
                    }
                    $solicitacao->codigoTeleconsultor = 276851;
                    $solicitacao->solicitacaoRepetida = 0;
                    $solicitacao->justificativaDevolucaoTeleconsultor = NULL;
                    $solicitacao->solicitacaoResposta = $solicitation->solicitationBySearch()->exists() ? $solicitation->solicitationBySearch->$relation->direct_answer : NULL;
                    $solicitacao->solicitacaoComplemento = $solicitation->solicitationBySearch()->exists() ? $solicitation->solicitationBySearch->$relation->complement : NULL;
                    $solicitacao->solicitacaoAtributos = $solicitation->solicitationBySearch()->exists() ? $solicitation->solicitationBySearch->$relation->attributes : NULL;
                    $solicitacao->solicitacaoEduPermanente = $solicitation->solicitationBySearch()->exists() ? $solicitation->solicitationBySearch->$relation->permanent_education : NULL;
                    $solicitacao->solicitacaoReferencia = $solicitation->solicitationBySearch()->exists() ? $solicitation->solicitationBySearch->$relation->references : NULL;
                    $solicitacao->estrategiaBusca = $solicitation->solicitationBySearch()->exists() ? $solicitation->solicitationBySearch->$relation->tags : NULL;
                    $solicitacao->solsofcod = $solicitation->solicitationBySearch()->exists() ? ($relation == 'answer' ? $solicitation->solicitationBySearch->answer->isSOF : null) : NULL;
                    $solicitacao->respostaAceita = 1;
                } else {
                    $solicitacao->codigoTeleconsultor = $solicitation->lastestSolicitationForward->consultant_profile_id;
                    $solicitacao->solicitacaoRepetida = 0;
                    $solicitacao->justificativaDevolucaoTeleconsultor = NULL;
                    $solicitacao->solicitacaoResposta = $solicitation->answer != NULL ? $solicitation->answer->direct_answer : NULL;
                    $solicitacao->solicitacaoComplemento = $solicitation->answer != NULL ? $solicitation->answer->complement : NULL;
                    $solicitacao->solicitacaoAtributos = $solicitation->answer != NULL ? $solicitation->answer->attributes : NULL;
                    $solicitacao->solicitacaoEduPermanente = $solicitation->answer != NULL ? $solicitation->answer->permanent_education : NULL;
                    $solicitacao->solicitacaoReferencia = $solicitation->answer != NULL ? $solicitation->answer->references : NULL;
                    $solicitacao->estrategiaBusca = $solicitation->answer != NULL ? $solicitation->answer->tags : NULL;
                    $solicitacao->solsofcod = $solicitation->answer != NULL ? $solicitation->answer->isSOF : NULL;
                    $solicitacao->respostaAceita = 1;
                }

                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationRegulacao()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->whereNotIn('status_id', [3, 6, 7, 9, 20, 19, 25])
            ->get();

        info('Migrating solicitations Regulacao table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                info($solicitation->id);
                $solicitacao = Regulacao::where('codigo', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitacao = new Regulacao;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;

                if ($solicitation->type_id == 52) {
                    $solicitacao->codigoRegulador = $solicitation->lastestSolicitationForward->regulator_profile_id;
                    $solicitacao->aceiteTelerregulacao = 1;
                    if ($solicitacao->status_id == 20 || $solicitacao->status_id == 25) {
                        $solicitacao->justificativaDevCan = $solicitation->observation->description;
                    } else {
                        $solicitacao->justificativaDevCan = null;
                    }
                } elseif ($solicitation->type_id == 53) {
                    $solicitacao->codigoRegulador = 276852;
                    $solicitacao->aceiteTelerregulacao = 1;
                    $solicitacao->justificativaDevCan = null;
                }
                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationEncaminhamentoPaciente()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::has('patientForward')
            ->where('profile_id', '<>', 276853)
            ->where('created_at', '>=', $min_date);

        info('Migrating solicitations Encaminhamento Paciente table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = SolEncaminhamentoPaciente::where('solicitacao', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitacao = new SolEncaminhamentoPaciente;
                    $solicitacao->solicitacao = $solicitation->id;
                    $j++;
                }
                $i++;
                $solicitacao->paciente_mae = $solicitation->patientForward->patient->mother_name;
                $solicitacao->paciente_nome = $solicitation->patientForward->patient->person->name;
                $solicitacao->paciente_nascimento = $solicitation->patientForward->patient->person->birthday;
                $solicitacao->paciente_sexo = ($solicitation->patientForward->patient->person->sex == 'F') ? 1 : 0;
                $solicitacao->paciente_cpf = $solicitation->patientForward->patient->person->cpf;
                $solicitacao->paciente_cns = $solicitation->patientForward->patient->cns;

                try {

                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationEncaminhamento()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::has('patientForward')
            ->where('profile_id', '<>', 276853)
            ->where('created_at', '>=', $min_date);

        info('Migrating solicitations Encaminhamento table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = SolEncaminhamento::where('codigo', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitacao = new SolEncaminhamento;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                $solicitacao->intencaoEncaminhamento = $solicitation->patientForward->has_intention;
                $solicitacao->sugestaoEncaminhamento = 0;
                $solicitacao->codigoSugestaoEncaminhamento = 0;
                $solicitacao->cboEspecialidade = ($solicitation->patientForward != null) ? ($solicitation->patientForward->cbo != null ? $solicitation->patientForward->cbo->code : null) : null;
                $solicitacao->evitacaoEncaminhamento = ($solicitation->evaluation != null) ? $solicitation->evaluation->avoided_forwarding : null;
                $solicitacao->inducaoEncaminhamento = ($solicitation->evaluation != null) ? $solicitation->evaluation->induced_forwarding : null;

                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationTempo()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->whereNotIn('status_id', [3, 4, 24, 25, 20])
            ->where('profile_id', '<>', 276853)
            ->get(['id', 'created_at', 'status_id', 'type_id']);

        info('Migrating solicitations Tempo table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Solcitacao_Tempo::where('codigo', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitacao = new Solcitacao_Tempo;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;

                if ($solicitation->lastestSolicitationForward == null && $solicitation->type_id == 53) {
                    $solicitacao->soltpo2 = 1;
                    $solicitacao->soltpo3 = 1;
                    $solicitacao->soltpo4 = 1;
                    $solicitacao->soltpo5 = 1;
                    $solicitacao->soltpo6 = 2;
                    $solicitacao->soltresp = 4;
                    $solicitacao->soltaval = 6;
                    $solicitacao->soltreg = 3;
                } else {
                    if ($solicitation->statusHistory()->where('status_id', 6)->exists() && $solicitation->statusHistory()->where('status_id', 8)->exists()) {
                        $solicitacao->soltpo2 = (strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 8)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 6))) / 60;
                        $solicitacao->soltpo2 > 0 ? $solicitacao->soltpo2 = $solicitacao->soltpo2 = round($solicitacao->soltpo2, 3) : $solicitacao->soltpo2 = null;
                    } else {
                        $solicitacao->soltpo2 = null;
                    }
                    //                    info($solicitacao->soltpo2);

                    if (($solicitation->statusHistory()->where('status_id', 8)->exists() && $solicitation->statusHistory()->where('status_id', 10)->exists())
                        || ($solicitation->statusHistory()->where('status_id', 8)->exists() && $solicitation->statusHistory()->where('status_id', 5)->exists() && $solicitation->type_id == 53)
                    ) {
                        $solicitacao->soltpo3 = (strtotime($solicitation->type_id == 53 ? MigrationController::statusHistoryDateLate($solicitation->id, 5) : MigrationController::statusHistoryDateLate($solicitation->id, 10)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 8))) / 60;
                        $solicitacao->soltpo3 > 0 ? $solicitacao->soltpo3 = $solicitacao->soltpo3 = round($solicitacao->soltpo3, 3) : $solicitacao->soltpo3 = null;
                    } else {
                        $solicitacao->soltpo3 = null;
                    }
                    //                    info($solicitacao->soltpo3);

                    if (($solicitation->statusHistory()->where('status_id', 21)->exists() && $solicitation->statusHistory()->where('status_id', 10)->exists())
                        || ($solicitation->statusHistory()->where('status_id', 5)->exists() && $solicitation->statusHistory()->where('status_id', 21)->exists() && $solicitation->type_id == 53)
                    ) {
                        $solicitacao->soltpo4 = (strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 21)) - strtotime($solicitation->type_id == 53 ? MigrationController::statusHistoryDateLate($solicitation->id, 5) : MigrationController::statusHistoryDateLate($solicitation->id, 10))) / 60;
                        $solicitacao->soltpo4 > 0 ? $solicitacao->soltpo4 = $solicitacao->soltpo4 = round($solicitacao->soltpo4, 3) : $solicitacao->soltpo4 = null;
                    } else {
                        $solicitacao->soltpo4 = null;
                    }
                    //                    info($solicitacao->soltpo4);

                    if ($solicitation->statusHistory()->where('status_id', 21)->exists() && $solicitation->statusHistory()->where('status_id', 5)->exists()) {
                        $solicitacao->soltpo5 = (strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 21)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 5))) / 60;
                        $solicitacao->soltpo5 > 0 ? $solicitacao->soltpo5 = round($solicitacao->soltpo5, 3) : $solicitacao->soltpo5 = null;
                    } else {
                        $solicitacao->soltpo5 = null;
                    }
                    //                    info($solicitacao->soltpo5);

                    if ($solicitation->statusHistory()->where('status_id', 22)->exists() && $solicitation->statusHistory()->where('status_id', 5)->exists()) {
                        $solicitacao->soltpo6 = (strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 22)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 5))) / 60;
                        $solicitacao->soltpo6 > 0 ? $solicitacao->soltpo6 = round($solicitacao->soltpo6, 3) : $solicitacao->soltpo6 = null;
                    } else {
                        $solicitacao->soltpo6 = null;
                    }
                    //                    info($solicitacao->soltresp);

                    if ($solicitation->statusHistory()->where('status_id', 6)->exists() && $solicitation->statusHistory()->where('status_id', 5)->exists()) {
                        $solicitacao->soltresp = (strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 5)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 6))) / 60;
                        $solicitacao->soltresp > 0 ? $solicitacao->soltresp = round($solicitacao->soltresp, 3) : $solicitacao->soltresp = null;
                    } else {
                        $solicitacao->soltresp = null;
                    }
                    //                    info($solicitacao->soltreg);
                    //                    info($solicitacao->codigo);

                    if ($solicitation->statusHistory()->where('status_id', 6)->exists() && $solicitation->statusHistory()->where('status_id', 22)->exists()) {
                        $solicitacao->soltaval = (strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 22)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 6))) / 60;
                        $solicitacao->soltaval > 0 ? $solicitacao->soltaval = round($solicitacao->soltaval, 3) : $solicitacao->soltaval = null;
                    } else {
                        $solicitacao->soltaval = null;
                    }
                    //                    info($solicitacao->soltaval);

                    if (($solicitation->statusHistory()->where('status_id', 6)->exists() && $solicitation->statusHistory()->where('status_id', 10)->exists())
                        || ($solicitation->statusHistory()->where('status_id', 6)->exists() && $solicitation->statusHistory()->where('status_id', 5)->exists() && $solicitation->type_id == 53)
                    ) {
                        $solicitacao->soltreg = (strtotime($solicitation->type_id == 53 ? MigrationController::statusHistoryDateLate($solicitation->id, 5) : MigrationController::statusHistoryDateLate($solicitation->id, 10)) - strtotime(MigrationController::statusHistoryDateLate($solicitation->id, 6))) / 60;
                        //                        info($solicitacao->soltreg);
                        //                        info($solicitacao->codigo);
                        $solicitacao->soltreg > 0 ? $solicitacao->soltreg = round($solicitacao->soltreg, 3) : $solicitacao->soltreg = null;
                    } else {
                        $solicitacao->soltreg = null;
                    }
                }

                if (!($solicitacao->soltpo2 == NULL && $solicitacao->soltpo3 = $solicitacao->soltpo4 == NULL && $solicitacao->soltpo5 == NULL && $solicitacao->soltpo6 == NULL && $solicitacao->soltresp == NULL && $solicitacao->soltaval == NULL && $solicitacao->soltreg == NULL)) {
                    try {
                        $solicitacao->save();
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
        }

        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationDateTimestamp()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->get(['id', 'created_at', 'status_id', 'type_id']);

        info('Migrating solicitations Date Timestamp table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Solicitacao_Datas_Timestamp::where('codigo', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitacao = new Solicitacao_Datas_Timestamp;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;

                if ($solicitation->status_id == 25) {
                    $solicitacao->soldtabertura = $solicitation->created_at;
                    $solicitacao->soldtenvio = null;
                    $solicitacao->regdtreceb = null;
                    $solicitacao->regdtdevol = null;
                    $solicitacao->regdtenvio = null;
                    $solicitacao->consdtacresp = null;
                    $solicitacao->consdtdevol = null;
                    $solicitacao->soldtenvresp = null;
                    $solicitacao->soldtsteleit = null;
                    $solicitacao->soldtavalin = null;
                    $solicitacao->soldtavalfim = null;
                } else {
                    if ($solicitation->type_id == 53) {
                        $solicitacao->soldtabertura = $solicitation->created_at;
                        $solicitacao->soldtenvio = $solicitation->created_at;
                        $solicitacao->regdtreceb = date('Y-m-d H:i:s', strtotime("+1 minutes", strtotime($solicitation->created_at)));
                        $solicitacao->regdtdevol = null;
                        $solicitacao->regdtenvio = date('Y-m-d H:i:s', strtotime("+2 minutes", strtotime($solicitation->created_at)));
                        $solicitacao->consdtacresp = date('Y-m-d H:i:s', strtotime("+3 minutes", strtotime($solicitation->created_at)));
                        $solicitacao->consdtdevol = null;
                        $solicitacao->soldtenvresp = date('Y-m-d H:i:s', strtotime("+4 minutes", strtotime($solicitation->created_at)));
                        $solicitacao->soldtsteleit = date('Y-m-d H:i:s', strtotime("+5 minutes", strtotime($solicitation->created_at)));
                        $solicitacao->soldtavalin = date('Y-m-d H:i:s', strtotime("+6 minutes", strtotime($solicitation->created_at)));
                        $solicitacao->soldtavalfim = date('Y-m-d H:i:s', strtotime("+6 minutes", strtotime($solicitation->created_at)));
                    } else {
                        $solicitacao->soldtabertura = MigrationController::statusHistoryDate($solicitation->id, 6);
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
                    }
                }
                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateSolicitationDate()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));
        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->get(['id', 'created_at', 'status_id', 'type_id']);

        info('Migrating solicitations Date table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {

            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Solicitacao_Datas::where('codigo', '=', $solicitation->id)->get()->first();
                if ($solicitacao == NULL) {
                    $solicitacao = new Solicitacao_Datas;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                if ($solicitation->status_id == 25) {
                    $solicitacao->soldtabertura = date('Y-m-d', strtotime($solicitation->created_at));
                    $solicitacao->soldtenvio = null;
                    $solicitacao->regdtreceb = null;
                    $solicitacao->regdtdevol = null;
                    $solicitacao->regdtenvio = null;
                    $solicitacao->consdtacresp = null;
                    $solicitacao->consdtdevol = null;
                    $solicitacao->soldtenvresp = null;
                    $solicitacao->soldtsteleit = null;
                    $solicitacao->soldtavalin = null;
                    $solicitacao->soldtavalfim = null;
                } else {
                    if ($solicitation->type_id == 53) {
                        $solicitacao->soldtabertura = $solicitation->created_at;
                        $solicitacao->soldtenvio = $solicitation->created_at;
                        $solicitacao->regdtreceb = $solicitation->created_at;
                        $solicitacao->regdtdevol = null;
                        $solicitacao->regdtenvio = $solicitation->created_at;
                        $solicitacao->consdtacresp = $solicitation->created_at;
                        $solicitacao->consdtdevol = null;
                        $solicitacao->soldtenvresp = $solicitation->created_at;
                        $solicitacao->soldtsteleit = $solicitation->created_at;
                        $solicitacao->soldtavalin = $solicitation->created_at;
                        $solicitacao->soldtavalfim = $solicitation->created_at;
                    } else {
                        $solicitacao->soldtabertura = (MigrationController::statusHistoryDate($solicitation->id, 6) != NULL) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 6))) : null;
                        $solicitacao->soldtenvio = (MigrationController::statusHistoryDate($solicitation->id, 6) != NULL) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 6))) : null;
                        $solicitacao->regdtreceb = (MigrationController::statusHistoryDate($solicitation->id, 10) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 10))) : null;
                        $solicitacao->regdtdevol = (MigrationController::statusHistoryDate($solicitation->id, 25) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 25))) : null;
                        $solicitacao->regdtenvio = (MigrationController::statusHistoryDate($solicitation->id, 25) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 25))) : null;
                        $solicitacao->consdtacresp = (MigrationController::statusHistoryDate($solicitation->id, 21) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 21))) : null;
                        $solicitacao->consdtdevol = (MigrationController::statusHistoryDate($solicitation->id, 24) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 24))) : null;
                        $solicitacao->soldtenvresp = (MigrationController::statusHistoryDate($solicitation->id, 5) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 5))) : null;
                        $solicitacao->soldtsteleit = (MigrationController::statusHistoryDate($solicitation->id, 21) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 21))) : null;
                        $solicitacao->soldtavalin = (MigrationController::statusHistoryDate($solicitation->id, 22) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 22))) : null;
                        $solicitacao->soldtavalfim = (MigrationController::statusHistoryDate($solicitation->id, 22) != null) ? date('Y-m-d', strtotime(MigrationController::statusHistoryDate($solicitation->id, 22))) : null;
                    }
                }

                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }


    /**
     *
     */
    public function migrateSolicitationCiapCid()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->whereIn('status_id', [5, 21, 22, 23])
            ->get();

        info('Migrating solicitations CIAP CID table...');
        $i = 0;
        $j = 0;
        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Solicitacao_CIs::where('codigo', '=', $solicitation->id)->get()->first();
                if ($solicitacao == NULL) {
                    $solicitacao = new Solicitacao_CIs;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                $solicitacao->ciap1 = ($solicitation->ciap1 != NULL) ? $solicitation->ciap1->code : NULL;
                $solicitacao->ciap2 = ($solicitation->ciap2 != NULL) ? $solicitation->ciap2->code : NULL;
                $solicitacao->ciap3 = ($solicitation->ciap3 != NULL) ? $solicitation->ciap3->code : NULL;
                $solicitacao->cid1 = ($solicitation->cid1 != NULL) ? $solicitation->cid1->code : NULL;
                $solicitacao->cid2 = ($solicitation->cid2 != NULL) ? $solicitation->cid2->code : NULL;

                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }


    /**
     *
     */
    public function migrateSolicitationClass()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        //        $min_date = date('Y-m-d H:i:s', strtotime('-30 minutes', $today));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));


        $solicitations = Solicitation::where('updated_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->whereNotNull('class_id')
            ->get();

        info('Migrating solicitations class...');
        $i = 0;

        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                if (Solicitacao::where('codigo', '=', $solicitation->id)->whereNull('classificacao')->exists()) {
                    $solicitacao = Solicitacao::where('codigo', '=', $solicitation->id)->get()->first();
                    $solicitacao->classificacao = $solicitation->class_id;
                    $i++;
                    try {
                        $solicitacao->save();
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
        }
        info($i);
    }

    /**
     *
     */
    public function migrateSolicitation()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-40 days', $today));

        $solicitations = Solicitation::where('created_at', '>=', $min_date)
            ->where('profile_id', '<>', 276853)
            ->get();

        info('Migrating solicitations table...');
        $i = 0;
        $j = 0;

        foreach ($solicitations as $solicitation) {
            if ($solicitation->type_id == 52 || ($solicitation->type_id == 53 && $solicitation->solicitationBySearch()->exists())) {
                $solicitacao = Solicitacao::where('codigo', '=', $solicitation->id)->get()->first();

                if ($solicitacao == NULL) {
                    $solicitacao = new Solicitacao;
                    $solicitacao->codigo = $solicitation->id;
                    $j++;
                }
                $i++;
                $solicitacao->solicitacaoTipo = ($solicitation->type_id == 52 || $solicitation->type_id == 53) ? 2 : 1;
                $solicitacao->solcitacao = $solicitation->type_id == 52 ? $solicitation->description : ($solicitation->solicitationBySearch->answer_id != NUll ? $solicitation->solicitationBySearch->answer->solicitation->description : $solicitation->solicitationBySearch->faq->description);
                $solicitacao->solicitacaoAtivo = 0;
                $solicitacao->classificacao = $solicitation->class_id;
                $solicitacao->codigoSolicitante = $solicitation->profile_id;
                $solicitacao->pacienteAtivo = ($solicitation->patientForward != null) ? 1 : 0;
                $solicitacao->mobile = $solicitation->mobile;

                try {
                    $solicitacao->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateProfiles()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-2 days', $today));
        $profiles = Profile::where('updated_at', '>=', $min_date)
            ->get();

        info('Migrating profiles table...');
        $i = 0;
        $j = 0;
        foreach ($profiles as $profile) {
            $perfil = Perfil::where('codigo', '=', $profile->id)->get()->first();

            //            info($profile->id);
            if ($perfil == NULL) {
                $perfil = new Perfil;
                $perfil->codigo = $profile->id;
                $j++;
            }
            $i++;
            $perfil->pessoa = $profile->user->person->id;
            $perfil->cbo = $profile->cbo->code;
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

            $perfil->ativo = ($profile->status_id == 1) ? 1 : 0;

            $perfil->equipe = ($profile->role_id == 7 || $profile->role_id == 5) ? ($profile->profile_team != null ? $profile->profile_team->team_id : 0) : 0;
            $perfil->dataAtualizacao = date('Y-m-d');

            try {
                $perfil->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }


    /**
     *
     */
    public function migratePeople()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-2 days', $today));

        $people = Person::where('updated_at', '>=', $min_date)->get();

        info('Migrating people table...');
        $i = 0;
        $j = 0;
        foreach ($people as $person) {
            $pessoa = Pessoa::where('cpf', '=', $person->cpf)->get()->first();

            if ($pessoa == NULL) {
                $pessoa = new Pessoa;
                $pessoa->codigo = $person->id;
                $j++;
            }
            $i++;
            if ($person->user != NULL) {
                $pessoa->nome = $person->name;
                $pessoa->sexo = ($person->sex == NULL) ? NULL : ($person->sex == 'M' ? 0 : 1);
                $pessoa->nascimento = $person->birthday;
                $pessoa->telefone = $person->telphone;
                $pessoa->celular = $person->celphone;
                $pessoa->email = $person->user->email;
                $pessoa->cpf = $person->cpf;
                $pessoa->dataInclusao = ($person->created_at != null) ? date('Y-m-d', strtotime($person->created_at)) : null;
                $pessoa->dataAtualizacao = date('Y-m-d');

                try {
                    $pessoa->save();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateTeams()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-2 days', $today));
        $teams = Team::where('updated_at', '>=', $min_date)->where('status_id', '<>', 35)->get();

        info('Migrating teams table...');
        $i = 0;
        $j = 0;
        foreach ($teams as $team) {
            $equipe = Equipe::where('codigo', '=', $team->id)->get()->first();
            //            info($team->id);
            if ($equipe == NULL) {
                $equipe = new Equipe;
                $equipe->codigo = $team->id;
                $j++;
            }
            $i++;
            $equipe->nome = $team->description;
            $equipe->ine = $team->ine;
            $equipe->unidade = $team->unit_id;
            $equipe->tipoequipe = $team->type_id != null ? $team->type_id : 0;
            $equipe->ativo = ($team->status_id == 1) ? 1 : 0;
            $equipe->dataAtualizacao = date('Y-m-d');

            try {
                $equipe->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }

    /**
     *
     */
    public function migrateUnits()
    {
        $today = strtotime(date('Y-m-d H:i:s'));
        $min_date = date('Y-m-d H:i:s', strtotime('-2 days', $today));
        $units = Unit::where('updated_at', '>=', $min_date)->where('status_id', '<>', 35)->get();

        info('Migrating units table...');
        $i = 0;
        $j = 0;
        foreach ($units as $unit) {
            $ubs = Unidade::where('cnes', '=', $unit->cnes)->get()->first();

            if ($ubs == NULL) {
                $ubs = new Unidade;
                $ubs->codigo = $unit->id;
                $j++;
            }
            $i++;
            $ubs->cnes = $unit->cnes;
            $ubs->nome = $unit->description;
            $ubs->endereco = $unit->address;
            $ubs->telefone = $unit->telphone;
            $ubs->ibge = $unit->city->ibge;
            $ubs->ativo = ($unit->status_id == 1) ? 1 : 0;
            $ubs->dataAtualizacao = date('Y-m-d');

            try {
                $ubs->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
        info($j);
        info($i - $j);
        return ['add' => $j, 'upd' => $i - $j];
    }
}
