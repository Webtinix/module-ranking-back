<?php

class Wx_RankingCronCalculateRankingModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax;
    

    /** @var ModeleExport */
    public $export;
    
    /**
     * secure_key
     *
     * @var string
     */
    public $secure_key = '3jvFtA6ng8u1YlZbJqPeKUoMxW4rNw';

    public function display()
    {
        $this->ajax = 1;

        if (!Tools::getValue('secure_key')) {
            # code...
            Tools::redirect('index.php');
        }
        
        if (Tools::getValue('secure_key') != $this->secure_key) {
            # code...
            Tools::redirect('index.php');
        }

        $calculate_ranking = new CalculateRanking();
        $calculate_ranking->calculateRanting();
        
        echo 'Ranking Terminé';
    }
} ?>