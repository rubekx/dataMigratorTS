<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Log;

use App\Solicitation;

use App\StatusHistory;

use App\Http\Controllers\StatusHistoryController;



class UpdateSolicitationsStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solicitation:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the all the solicitations statues to check if they are delayed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Status: 6 - Aguarda telerregulação to //Status: 7 - Aguarda telerregulação atrasada in 24 hours
        info('Starting update...');
        $this->updateSolicitationStatus(6, 7, 24);

        //Status: 10 - Aguarda Teleconsultor to //Status: 11 - Aceite teleconsultor atrasado in 12 hours
        $this->updateSolicitationStatus(10, 11, 12);

        //Status: 5 - Aguarda Leitura to //Status: 23 - Expirada atrasado in 720 hours (30 days)
        $this->updateSolicitationStatus(5, 23, 720);

        $this->updateSolicitationStatusEntireProcess();
        info('Done!');
    }

    private function updateSolicitationStatus($currentStatus, $nextStatus, $timeAmount)
    {
        $solicitations = Solicitation::where('status_id', '=', $currentStatus)->get();
        foreach ($solicitations as $sol)
        {
            $hours = round((strtotime(date('Y-m-d H:i:s')) - strtotime($sol->updated_at))/3600);
            info($hours);
            if ($hours>=$timeAmount) {
                $user = User::where('email', '=', $sol->profile->user->email)->get()->first();
                if($hours == 12)
                {
                    $user->notify(new MailConsultantDelayedSolicitation($sol->id, $user->person->name, $sol->hashId(), 11));  //Modify parameters
                }
                $sol->status_id = $nextStatus;
                $sol->save();
                StatusHistoryController::store($sol);
            }
        }
    }

    private function updateSolicitationStatusEntireProcess()
    {
        $solicitations = Solicitation::where('status_id', '=', 12)->get();
        foreach ($solicitations as $sol)
        {
            $sh = StatusHistory::where('status_id', '=', 6)
                            ->where('solicitation_id', '=', $sol->id)
                            ->orderByDesc('created_at')
                            ->get()
                            ->first();
            info($sh);
            $hours = (strtotime(date('Y-m-d H:i:s')) - strtotime($sh->updated_at))/3600;
            if ($hours>=72) {
                $user = User::where('email', '=', $sol->profile->user->email)->get()->first();
                if($hours == 72)
                {
                    $user->notify(new MailConsultantDelayedSolicitation($sol->id, $user->person->name, $sol->hashId(), 13));  //Modify parameters
                }
                $sol->status_id = 13;
                $sol->save();
                StatusHistoryController::store($sol);
            }
        }
    }


}
