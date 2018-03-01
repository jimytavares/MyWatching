
<?php

class AnimeRankingList extends TPage
{
    private $datagrid;
    private $form;
    private $pageNavigation;
    private $loaded;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormWrapper(new TQuickForm);
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid);
        
        $opcao = new TCombo('opcao');
        $nome  = new TEntry('nome');

        $items= array();
        $items['nome'] = 'Nome';

        $opcao->addItems($items);
        $opcao->setValue('nome');

        $opcao->setDefaultOption('..::SELECIONE::..');

        $this->form->addFields( [ new TLabel( 'ID:' ) ], [ $id ] );        
        $this->form->addFields( [ new TLabel( 'Nome:' )  ], [ $nome ] );

        $find_button = $this->form->addQuickAction( 'Buscar', new TAction(array($this, 'onSearch')), 'fa:search');
        $find_button->class = 'btn btn-sm btn-primary';

        $new_button = $this->form->addQuickAction( 'Novo' , new TAction(array('AnimeRankinForm', 'onEdit')), 'fa:file');
        $new_button->class = 'btn btn-sm btn-primary';
        
        $this->datagrid->addQuickColumn('ran_anime_id', 'ran_anime_id', 'center');
        $this->datagrid->addQuickColumn('nota', 'nota', 'left');
        $this->datagrid->addQuickColumn('ano', 'ano', 'left');
        $this->datagrid->addQuickColumn('comentario', 'comentario', 'left');

        $actionEdit = new TDataGridAction(array('AnimeRankingForm', 'onEdit'));
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
        
        //hp
        $panel = new TPanelGroup('Cadastro Ranking Anime');
        $panel->add($this->datagrid);
        $panel->addFooter('footer');
        
        $vbox = new TVBox;
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);

        parent::add($vbox);
    }
    
	public function onReload( $param = NULL )
    {
        try
        {
            TTransaction::open( "db_mywatching" );
                $repository = new TRepository( "AnimeRankingRecord" );
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
                $object = new AnimeRankingRecord($param['key']);
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