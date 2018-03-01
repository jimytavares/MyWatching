<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class animeForm extends TPage
{

    private $form;

    public function __construct()
    {

        parent::__construct();


        $this->form = new BootstrapFormBuilder('form_anime');
        $this->form->setFormTitle('Cadastro de Animes');
        $this->form->class = 'form_anime';

        $id            = new THidden('id');
        $nome          = new TEntry('nome');
        $ep            = new TEntry('ep');
        $dataassistido = new TDate('dataassistido');
        $proxep        = new TEntry('proxep');
        $dataproxep    = new TDate('dataproxep');
        $descricao     = new TText('descricao');
        
        $nome->setMaxLength(100);
        $nome->setSize("38%");
        $ep->setSize("38%");
        $dataassistido->setSize("38%");
        $proxep->setSize("38%");
        $dataproxep->setSize("38%");
        $descricao->setSize("38%");
        
        $this->form->addFields([$id]);
        $this->form->addFields([new TLabel('Nome:')],         [$nome]);
        $this->form->addFields([new TLabel('Episódio: ')],     [$ep]);
        $this->form->addFields([new TLabel('Data: ')],         [$dataassistido]);
        $this->form->addFields([new TLabel('Próximo Ep: ')],   [$proxep]);
        $this->form->addFields([new TLabel('Data Prox Ep: ')], [$dataproxep]);
        $this->form->addFields([new TLabel('Descrição: ')],    [$descricao]);

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
                $cadastro = $this->form->getData('animeRecord');
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
                    $object = new animeRecord($key);
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
