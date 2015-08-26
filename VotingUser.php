<?php

/**
 * Description of VotingUser
 *
 * @author kompas
 */
class VotingUser {
    
    public $id;
    public $points;
    public $vote_time = array();
    public $ip;
        
    public function get_vote_timer_by_site_id($id)
    {
        if(sizeof($this->vote_time) == 0)
            return null;
        else
            foreach($this->vote_time as $vote_timer)
            {
                if($vote_timer->vote_site_id == $id)
                {
                    return $vote_timer;
                }
            }
            
        return null;
    }
}
