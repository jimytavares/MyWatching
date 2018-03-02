<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class AnimeParadosForm extends TPage
{

    private $form;

    public function __construct()
    {

        parent::__construct();


        $this->form = new BootstrapFormBuilder('form_anime_parados');
        $this->form->setFormTitle('Animes Parados');
        $this->form->class = 'form_anime_parados';

        $id           = new THidden('id');
        $ep           = new TDate('ep');
        $qntep        = new TEntry('qntep');
        $situacao     = new TCombo('situacao');
        $comentario   = new TText('comentario');
        $ran_anime_id = new TDBCombo("ran_anime_id", "db_mywatching", "animeRecord", "id", "nome");
        
        $ep->setSize("38%");
        $qntep->setSize("38%");
        $situacao->setSize("38%");
        $comentario->setSize("38%");
        $ran_anime_id->setSize("38%");
        
        $situacao->addItems( [ "10" => "10", "15" => "15", "20" => "20", "25" => "25", "30" => "30" ] );
        
        $this->form->addFields([$id]);
        $this->form->addFields([new TLabel('Nome:')],          [$ran_anime_id]);
        $this->form->addFields([new TLabel('Episódio: ')],         [$ep]);
        $this->form->addFields([new TLabel('Quantidade Ep: ')], [$qntep]);
        $this->form->addFields([new TLabel('Situação: ')], [$situacao]);
        $this->form->addFields([new TLabel('Comentário: ')],   [$comentario]);

        $this->form->addFields([new TLabel('')], [TElement::tag('label', '<i>* Campos obrigatórios</i>' ) ]);

        $this->form->addAction('Salvar', new TAction(array($this, 'onSave')), 'fa:save')->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Voltar', new TAction(array('AnimeParadosList', 'onReload')), 'fa:arrow-left')->class = 'btn btn-sm btn-primary';

        parent::add($this->form);
    }

    function onSave()
    {
        try {

            TTransaction::open('db_mywatching');
                $this->form->validate();
                $cadastro = $this->form->getData('AnimeParadosRecord');
                $cadastro->store();
            TTransaction::close();

            $action_ok = new TAction( [ 'AnimeRankingList', "onReload" ] );

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
                    $object = new AnimeParadosRecord($key);
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
