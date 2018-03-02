<?php

class animeList extends TPage
{
    private $datagrid;
    private $form;
    private $pageNavigation;
    private $loaded;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormWrapper(new TQuickForm);
        //grid antigo $this->datagrid = new TQuickGrid;
        
        $opcao = new TCombo('opcao');
        $nome  = new TEntry('nome');

        $items= array();
        $items['nome'] = 'Nome';

        $opcao->addItems($items);
        $opcao->setValue('nome');

        $opcao->setDefaultOption('..::SELECIONE::..');

        $this->form->addQuickField('Busca', $opcao, '80%');
        $this->form->addQuickField('Nome', $nome, '80%');

        $find_button = $this->form->addQuickAction('Buscar', new TAction([$this, 'onSearch']),
            'fa:search');
        $find_button->class = 'btn btn-sm btn-primary';

        $new_button = $this->form->addQuickAction('Novo', new TAction(['animeForm', 'onEdit']),
            'fa:file');
        $new_button->class = 'btn btn-sm btn-primary';
        
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        
        $this->datagrid->addQuickColumn('Anime', 'nome', 'center');
        $this->datagrid->addQuickColumn('Episódio', 'ep', 'left');
        $this->datagrid->addQuickColumn('Assistido', 'dataassistido', 'left');
        $this->datagrid->addQuickColumn('Próximo', 'proxep', 'left');
		$this->datagrid->addQuickColumn('Novo Episódio', 'dataproxep', 'left');
		$this->datagrid->addQuickColumn('Descrição', 'descricao', 'left');

        $actionEdit = new TDataGridAction(array('animeForm', 'onEdit'));
        $actionEdit->setLabel('Editar');
        $actionEdit->setImage( "fa:pencil-square-o blue fa-lg" );
        $actionEdit->setField('id');

        $actionDelete = new TDataGridAction(array($this, 'onDelete'));
        $actionDelete->setLabel('Deletar');
        $actionDelete->setImage( "fa:trash-o red fa-lg" );
        $actionDelete->setField('id');
        
        $this->datagrid->addAction( $actionDelete  );
        $this->datagrid->addAction( $actionEdit );
        
        $this->datagrid->createModel();

        $container = new TVBox();
        $container->style = "width: 100%";
        $container->add(TPanelGroup::pack('Cadastro de Animes', $this->form));
        $container->add(TPanelGroup::pack(NULL, $this->datagrid));

        parent::add($container);
    }
    
	public function onReload( $param = NULL )
    {
        try
        {
            TTransaction::open( "db_mywatching" );
                $repository = new TRepository( "animeRecord" );
                if ( empty( $param[ "order" ] ) )
                {
                    $param[ "order" ] = "id";
                    $param[ "direction" ] = "asc";
                }
                $limit = 10;
                $criteria = new TCriteria();
                $criteria->setProperties( $param );
                $criteria->setProperty( "limit", $limit );
                $objects = $repository->load( $criteria, FALSE );
                $this->datagrid->clear();
                if ( !empty( $objects ) )
                {
                    foreach ( $objects as $object )
                    {
                        $object->dataassistido = TDate::date2br($object->dataassistido);
                        $object->dataproxep = TDate::date2br($object->dataproxep);

                        $this->datagrid->addItem( $object );
                    }
                }
                $criteria->resetProperties();
                $count = $repository->count($criteria);
                //$this->pageNavigation->setCount($count);
                //$this->pageNavigation->setProperties($param);
                //$this->pageNavigation->setLimit($limit); 
            TTransaction::close();
            $this->loaded = true;
        }
        catch ( Exception $ex )
        {
            TTransaction::rollback();
            new TMessage( "error", $ex->getMessage() );
        }
    }

    public function onSearch()
    {
        $data = $this->form->getData();

        try {
            if( !empty( $data->opcao )  ) 
            {
                $filter = [];
                switch ( $data->opcao ) 
                {
                    case "nome":
                        $filter[] = new TFilter( $data->opcao, "LIKE", "%" . $data->nome . "%" );
                        break;
                    default:
                        $filter[] = new TFilter( $data->opcao, "LIKE", $data->nome . "%" );
                        break;
                }

                TSession::setValue('filter_animeRecord', $filter);
                $this->form->setData( $data );
                $this->onReload();
                
            } else {
                TSession::setValue('filter_animeRecord', '');
                $this->onReload();
                $this->form->setData( $data );
                new TMessage( "error", "Selecione uma opcao e informe os dados da busca corretamente!" );
            }

        } catch ( Exception $ex ) {
            TTransaction::rollback();
            $this->form->setData( $data );
            new TMessage( "error",  $ex->getMessage() .'.' );
        }
    }

    public function onDelete( $param = NULL )
    {
        if( isset( $param[ "key" ] ) ) 
        {
            $action_ok = new TAction( [ $this, "Delete" ] );
            $action_cancel = new TAction( [ $this, "onReload" ] );
            $action_ok->setParameter( "key", $param[ "key" ] );

            new TQuestion( "Deseja remover o registro?", $action_ok, $action_cancel,  "Deletar");
        }
    }

    function Delete( $param = NULL )
    {
        try {

            TTransaction::open('db_mywatching');
                $object = new animeRecord($param['key']);
                $object->delete();
            TTransaction::close();

            $this->onReload();
            new TMessage( "info", "Registro deletado com sucesso!" );

        } catch ( Exception $ex ) {
            TTransaction::rollback();
            new TMessage( "error",  $ex->getMessage() .'.' );
        }
    }

    public function show()
    {
        $this->onReload();
        parent::show();
    }
}
