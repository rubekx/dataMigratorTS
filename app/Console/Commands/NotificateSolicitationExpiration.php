<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Solicitation;
use App\StatusHistory;
use App\User;
use App\Notifications\MailSolicitationExpireNotification;
use App\Notifications\MailSolicitantReceivedAnswer;

use Illuminate\Notifications\Notifiable;
use Vinkla\Hashids\Facades\Hashids;

use DateTime;
use DateInterval;

class NotificateSolicitationExpiration extends Command
{
    use Notifiable;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:solicitant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all solicitations close to be expired and notify their solicitants';

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
        $solicitations = Solicitation::where('status_id', '=', 5)->get(); //Status: Aguarda leitura

        foreach ($solicitations as $s) 
        {
            $hashedId = $s->answer->hashId();
            $sh_sent = StatusHistory::where('solicitation_id', '=', $s->id)
                                ->where('status_id', '=', 6)
                                ->orderByDesc('updated_at')
                                ->get()
                                ->first();
            
            $sol_sent_date = date('d/m/Y', strtotime($sh_sent->created_at));
            
            $sh_answ = StatusHistory::where('solicitation_id', '=', $s->id)
                                ->where('status_id', '=', 5)
                                ->orderByDesc('updated_at')
                                ->get()
                                ->first();

            $sol_answ_date = date('d/m/Y', strtotime($sh_answ->created_at));
            
            $date = $sh_answ->created_at;
            $date->add(new DateInterval('P30D'));
            $expiring_date = date('d/m/Y', strtotime($date));

            $datediff = strtotime(date('Y-m-d H:i:s')) - strtotime($sh_answ->created_at);

            $days = round($datediff / (60 * 60 * 24));
            
            if($days == 20 || $days == 25 || $days == 28 || $days == 29) //10 days
            {
                $name = $s->profile->user->person->name;
            
                $email = $s->profile->user->email;
                $user = User::where('email', '=', $email)->get()->first();
                
                $user->notify(new MailSolicitationExpireNotification($email, $name, $s->id, $sol_sent_date, $sol_answ_date, $expiring_date, $hashedId));
            }
            
            //Send email that solicitation was answered after 10 days

            if($days == 10) //10 days
            {
                $name = $s->profile->user->person->name;
            
                $email = $s->profile->user->email;
                $user = User::where('email', '=', $email)->get()->first();
                
                $user->notify(new MailSolicitantReceivedAnswer($name, $s->id, $sol_sent_date, $hashedId));
            }
        }

        
    }
}
