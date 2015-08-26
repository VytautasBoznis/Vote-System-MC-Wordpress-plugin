<?php

class VoteSites {
    
    	public $id; //id - unikalus skaicius kad butu patogiau atpazint
	public $site_name; // pavadinimas kuri rodis virs nuorodos
	public $site_banner_url; // banerio tinklapis
	public $site_vote_url; // tinklapis kur reikia balsuot
	public $interval;// kas kiek minuciu galima balsuot
	public $interval_text;// tekstas kuri turetu rasyt prie baneriu pvz vietoj 3600s ->1h
	
	//jai nodojamas advance tai bus sniffinami balsu skaiciai pries ir po balsavimo todel turi buti tiksliai nurodita ko reikia ieskoti ir kur.
	public $advanced_lookup_text;
	
	public function __construct($my_id = -1,$my_name = "",$my_banner_url ="",$my_vote_url="",$my_interval="",$my_interval_text="",$my_lookup_text ="")
	{
		$this ->id = $my_id;
		$this ->site_name = $my_name;
		$this ->site_banner_url = $my_banner_url;
		$this ->site_vote_url = $my_vote_url;
		$this ->interval = $my_interval * 60;
		$this ->interval_text = $my_interval_text;
		$this ->advanced_lookup_text = $my_lookup_text;
	}
        
        public function getId() {
            return $this->id;
        }
}
