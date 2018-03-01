<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class AnimeRankingForm extends TPage
{

    private $form;

    public function __construct()
    {

        parent::__construct();


        $this->form = new BootstrapFormBuilder('form_anime_ranking');
        $this->form->setFormTitle('Ranking de Animes');
        $this->form->class = 'form_anime_ranking';

        $id           = new THidden('id');
        $ano          = new TDate('ano');
        $nota         = new TEntry('nota');
        $comentario   = new TText('comentario');
        $ran_anime_id = new TDBCombo("ran_anime_id", "db_mywatching", "animeRecord", "id", "nome");
        
        $ano->setSize("38%");
        $nota->setSize("38%");
        $comentario->setSize("38%");
        $ran_anime_id->setSize("38%");
        
        $this->form->addFields([$id]);
        $this->form->addFields([new TLabel('Nome:')],          [$ran_anime_id]);
        $this->form->addFields([new TLabel('Nota: ')],         [$nota]);
        $this->form->addFields([new TLabel('Ano do Anime: ')], [$ano]);
        $this->form->addFields([new TLabel('Comentario: ')],   [$comentario]);

        $this->form->addFields([new TLabel('')], [TElement::tag('label', '<i>* Campos obrigatórios</i>' ) ]);

        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save')->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Voltar', new TAction(array('animeList', 'onReload')), 'fa:arrow-left')->class = 'btn btn-sm btn-primary';

        parent::add($this->form);
    }

    function onSave()
    {
        try {

            TTransaction::open('db_mywatching');
                $this->form->validate();
                $cadastro = $this->form->getData('AnimeRankingRecord');
                $cadastro->store();
            TTransaction::close();

            $action_ok = new TAction( [ 'animeList', "onReload" ] );

            new TMessage( "info", "Registro salvo com sucesso!", $action_ok );

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    function onEdit($param)
    {
        try {

            if (isset($param['key'])) 
            {
                $key = $param['key'];

                TTransaction::open('sample');
                    $object = new AnimeRankingRecord($key);
                    $this->form->setData($object);
                TTransaction::close();
            }
        } catch (Exception $e) {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage() . "<br/>");
            TTransaction::rollback();
        }
    }

}

?>
