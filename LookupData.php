<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LookupData
 *
 * @author kompas
 */
class LookupData {
    
    public $id;
    public $points_on_lookup;
    public $lookup_time;
    public $added_points;
	
    public function __construct($my_id,$my_points_on_lookup,$my_lookup_time,$my_added_points)
    {
    	$this ->id = $my_id;
    	$this ->points_on_lookup = $my_points_on_lookup;
    	$this ->lookup_time = $my_lookup_time;
    	$this ->added_points = $my_added_points;
    }
}
