<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Dash\Solicitacao;
use App\Dash\Resposta;

class UpdateSolicitationContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $file_path = storage_path('app/sol.csv');
        $count = 0;
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                if(is_numeric($data[0]))
                {
                    $command = new UpdateSolicitationContent;
                    $index = (int)$data[0];
                    
                    $solicitacao = Solicitacao::where('codigo', '=', $index)->get()->first();
                    if($solicitacao != NULL)
                    {
                        $solicitacao->solcitacao = $command->replaceString($data[1]);
                        $solicitacao->save();
                    }
                    $resposta = Resposta::where('codigo', '=', $index)->get()->first();
                    info('num '.$num);
                    
                    
                    if($resposta != NULL)
                    {
                        if($num > 2)
                            $resposta->solicitacaoResposta = $command->replaceString($data[2]);
                        if($num > 3)
                            $resposta->solicitacaoComplemento = $command->replaceString($data[3]);
                        if($num > 4)
                            $resposta->solicitacaoAtributos = $command->replaceString($data[4]);
                        if($num > 5)
                            $resposta->solicitacaoEduPermanente = $command->replaceString($data[5]);
                        if($num > 6)
                            $resposta->solicitacaoReferencia = $command->replaceString($data[6]);
                        if($num > 8)
                            $resposta->estrategiaBusca = $command->replaceString($data[8]);
                        $resposta->save();                      
                    }
                    
                    
                }
            }
            fclose($handle);
        }
    }

    private function replaceString($string)
    {
        $string = mb_convert_encoding($string,"UTF-8","auto");

        $content = str_replace("&nbsp", "", $string);
        $content = str_replace("&nbsp;", "", $content);
        $content = str_replace("&lt", "", $content);
        $content = str_replace("&gt", "", $content);
        $content = html_entity_decode($content);
        return $content;
    }
}
