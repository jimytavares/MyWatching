    <?php
     
    class AnimeRankingRecord extends TRecord
    {
        const TABLENAME  = "ranking";
        const PRIMARYKEY = "id";
        const IDPOLICY   = "serial";
        
    private $nome_anime;

    public function get_nome_anime()
    {
         if (empty ($this->nome_anime) )
         {
            $this->nome_anime = new animeRecord ($this->ran_anime_id);
         }
        return $this->nome_anime->nome;
    }
    }
     
