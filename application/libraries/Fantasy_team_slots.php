<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Hockey_player
*
* Author: Michael Gross
*		  mikegross@gmail.com
*
*/

class Fantasy_team_slots extends CI_Model
{
	//slots for the fantasy team
	public $slots = Array();
	
	//position groups
	public $positionGroups = Array();
	
	//multipliers for the different statistics
	public $multipliers = Array();
	
	//all the fantasy teams in the league
	public $fantasy_league_teams = Array();
	
	//league name
	public $fantasy_league_name;
	
	//the type of league
	public $fantasy_league_style;
	
	//head-to-head on or off
	public $headtohead;
	
	//weeks and matchups
	public $weeks_and_matchups = Array();
	
	public function __construct($slotsInput)
	{
		$this->slots = $slotsInput;
		$this->headtohead = false;
	}

	/*this function sets the multipliers to convert statistics to fantasy points*/
	public function set_multipliers($type, $multipliersArr)
	{
		$this->multipliers[$type]=$multipliersArr;
	}
	
	public function set_position_groups($group, $positions)
	{
		$this->positionGroups[$group] = $positions;
	}

	public function add_team_to_league($fantasy_team)
	{
		$this->fantasy_league_teams[] = $fantasy_team;
	}
	public function set_fantasy_league_style($league_style)
	{
		$this->fantasy_league_style = $league_style;
		if ($this->fantasy_league_style == "headpoint" || $this->fantasy_league_style == "headtohead")
			$this->headtohead = true;
		else
			$this->headtohead = false;
	}
	public function set_weeks_and_matchups($week_and_matchups)
	{
		$this->weeks_and_matchups = $week_and_matchups;
	}
	
	
	
}