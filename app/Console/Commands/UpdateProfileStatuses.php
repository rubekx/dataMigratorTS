<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Profile;
use App\Solicitation;
use App\StatusHistory;

use App\Http\Controllers\ProfileController;

class UpdateProfileStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of profile when user is been inactive for more than 90 days';

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
     *s
     * @return mixed
     */
    public function handle()
    {
        info('Updating profiles...');
        $this->updateProfileStatus();
        info('Done!');
    }

    private function updateProfileStatus()
    {
        $inactives = 0;
        $actives = 0;
        $profiles = Profile::where('status_id', '=', 1)->get(); //All active profiles 
        foreach ($profiles as $profile) 
        {
            $sol = StatusHistory::join('solicitations', 'statuses_history.solicitation_id', '=', 'solicitations.id')
                    ->where('solicitations.profile_id', '=', $profile->id)
                    ->where('statuses_history.status_id', '=', 6)
                    ->orderByDesc('statuses_history.created_at')
                    ->get()
                    ->first();
            if($sol)
            {
                $hours = (strtotime(date('Y-m-d H:i:s')) - strtotime($sol->updated_at))/3600;
                if($hours > 2160) //Profile inactive (with no solicitation whitin 90 days) 2160 = 90 days
                {
                    $inactives++;
                    $profile->status_id = 2; //Status to 'Inativo'
                    ProfileController::store($profile);
                }
                else
                    $actives++;
            }
            
        }
    }
}
