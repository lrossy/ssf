<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//require(APPPATH.'/libraries/newOAuth/http.php');
//require(APPPATH.'/libraries/newOAuth/oauth_client.php');
class OAuth_MG extends CI_Model
{
	public $client;
	public function __construct()
	{
		$this->client = new oauth_client_class;
	}
	public function connect_yahoo($login = false)
	{

		$this->client->debug = false;
		$this->client->debug_http = true;
		$this->client->server = 'Yahoo';
		//$this->client->redirect_uri = site_url("fantasy/hockey/");
		$this->client->client_id = 'dj0yJmk9bm9uNm9hQTdNQjNyJmQ9WVdrOU1FNVFiR2t4TkhFbWNHbzlNVEUyT1RJMk56QTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD05MQ--'; $application_line = __LINE__;
		$this->client->client_secret = '057bbac71f7d3fc7aecc1a120faedece7ec6bae4';
		$this->client->Initialize();
		$this->GetAccessToken($access_token);
		if($access_token['value'] != null)
		{
			$this->client->Process();
		}
	}
	public function yahoo_login()
	{
		if(strlen($this->client->client_id) == 0
		|| strlen($this->client->client_secret) == 0)
			die('Please go to Yahoo Apps page https://developer.apps.yahoo.com/projects/ , '.
				'create a project, and in the line '.$application_line.
				' set the client_id to Consumer key and client_secret with Consumer secret. '.
				'The Callback URL must be '.$client->redirect_uri).' Make sure you enable the '.
				'necessary permissions to execute the API calls your application needs.';

		$this->GetAccessToken($access_token);
		if($access_token['value'] == null)		
		{
			$success = $this->client->Process();
		}
		echo '<head><meta http-equiv="refresh" content="0; url=http://statsmachine.ca/fantasy/hockey" /></head>';
	}
	public function get_nhl_league_key()
	{
		$success = false;
		if(strlen($this->client->access_token))
		{
			$success = $this->client->CallAPI(
				'http://query.yahooapis.com/v1/yql', 
				'GET', array(
					'q'=>"select * from fantasysports.leagues where game_key='nhl' and use_login = 1",
					'format'=>'json'
				), array('FailOnAccessError'=>true), $league);
		}		
		if($this->client->exit)
			exit;
		if ($success)
		{
			$success = $this->client->Finalize($success);
			$leagues = Array();
			$i=0;
			foreach ($league->query->results as $key=>$league_data)
			{
				$leagues[$i]['league_key'] = $league_data->league_key;
				$leagues[$i]['name'] = $league_data->name;
			}
			return $leagues;
		}
		else
		{
			echo "could not connect";
			return false;
		}
	}

	
	public function get_nhl_team_keys($league_key, $manager_team_key)
	{
		$lastperiod = strrchr($manager_team_key,'.');
		$team_id = substr($lastperiod, strlen($lastperiod)-1);
		$query = "select * from fantasysports.teams where league_key='$league_key'";
		$result = $this->query_fantasy_DB($query);
		if ($result)
		{
			foreach ($result->query->results->team as $key=>$team)
			{

				$team_keys[$key]['team_key'] = $team->team_key;
				$team_keys[$key]['team_name'] = $team->name;
			}
			return($team_keys);
		}
		else
		{
			return false;
		}
	}
	public function query_fantasy_DB($yql)
	{
		$success = false;
		if(strlen($this->client->access_token))
		{
			$success = $this->client->CallAPI(
				'http://query.yahooapis.com/v1/yql', 
				'GET', array(
					'q'=>$yql,
					'format'=>'json'
				), array('FailOnAccessError'=>true), $result);
		}		
		if($this->client->exit)
			exit;
		if ($success)
		{
			$success = $this->client->Finalize($success);
			return $result;
		}
		else
		{
			echo "could not connect";
			return false;
		}
	}
	public function get_logged_in_users_team_key($league_key)
	{
		$query = "select * from fantasysports.teams where league_key='$league_key' and managers.manager.guid=me";
		$result=$this->query_fantasy_DB($query);
		if ($result)
		{
			$team_key = $result->query->results->team->team_key;
			return $team_key;
		}
		else return false;
	}
	
	
	public function query_week_matchup($league_key, $week_number)
	{
		$query = "select * from fantasysports.leagues.scoreboard where league_key='$league_key' and week='$week_number'";
		$result=$this->query_fantasy_DB($query);
		if ($result)
		{
			return $result;
		}
		else return false;
	}
	
	public function get_users_team_players($team_key)
	{
		$query = "select * from fantasysports.players where team_key='$team_key'";
		$result=$this->query_fantasy_DB($query);
		if ($result)
		{
			foreach ($result->query->results->player as $key=>$player)
			{
				$fantasy_player[$key]['full'] = $player->name->full;
				if (isset($player->status))
				{
					$fantasy_player[$key]['status'] = $player->status;
				}
				else
				{
					$fantasy_player[$key]['status'] = true;
				}
				$fantasy_player[$key]['position'] = $player->display_position;
			}
			return($fantasy_player);
		}
		else return false;
	}
	public function get_all_other_team_players($array_of_team_keys)
	{
		foreach ($array_of_team_keys as $key=>$team_key)
		{
			$team_key['team_name'] = str_replace("'","",$team_key['team_name']);
			$array[$team_key['team_name']] = $this->get_users_team_players($team_key['team_key']);
		}
		return $array;
	}
	public function add_nhl_ids_to_team_players($team)
	{
		$this->load->model('stats_model');
		foreach ($team as $key=>$player)
		{
			$team[$key]['nhl_id'] = $this->stats_model->getPlayerNHLID(str_replace(".","",$player['full']));
		}
		return $team;
	}
	public function get_fantasy_league_settings($league_key)
	{
		$query = "select * from fantasysports.leagues.settings where league_key='$league_key'";
		$result=$this->query_fantasy_DB($query);
		return $result;
	}
	public function log_off_yahoo()
	{
		$success = false;
		if($this->client->GetAccessToken()==1)
		{
			return $this->client->ResetAccessToken();
		}		
		return false;
	}
	public function GetAccessToken(&$access_token)
	{
		return $this->client->GetAccessToken($access_token);
	}
}

?>