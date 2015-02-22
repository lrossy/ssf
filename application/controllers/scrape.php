<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Scrape extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->library('ion_auth');
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->model('scrape_model');
		if (!$this->ion_auth->is_admin())
		{
			$this->session->set_flashdata('message', 'You must be an admin to view this page');
			redirect('stats/index');
		}
	}

	public function index()
	{
		$this->load->view('/scrape/index.php',$this->data);
	}
	function tonightsGames()
	{
		$currentGames = $this->scrape->nightly();
		$this->unSetMemCache($currentGames);
	}
	function dailyWrapUp()
	{
		$this->scrape->dailyWrapUp();
	}
	function reScrape()
	{
		$this->scrape->rescrape(1201,1230,'20082009',0,2,0);

	}
	function unSetMemCache($tnGames){
		$this->season = '20102011';
		$this->gameType = '2';
		$this->memci->set('seasonLeaders',null,6000);
		$this->memci->set('tonightLeaders',null,6000);
		if(count($tnGames)>0){
			foreach($tnGames as $game){
				$gameID = $this->season.$this->gameType.$game;
				$this->memci->set('game_'.$gameID,null,6000);
			}
		}
		//$this->memci->set('game_'.$array['gameID'],$todList,60);
	}
	function getPlayerMugs(){
		$this->scrape->getPlayerMugs();
	}
	function getSched(){
	//	$this->scrape->getNHLSchedualPlayed(2);
		$this->scrape->getNHLSchedual(3);
	}
	function scrapeFile()
	{
		$this->scrape->scrapeFile('GS020259.HTM','ES020259.HTM',259);
	}
	function scrapeBoxScore()
	{
		$season = $this->input->post('season');
		$gametype = $this->input->post('gametype');
		$game_id = $this->input->post('game_id');
		$date = $this->input->post('date');
		$debug = $this->input->post('debug');
		//echo "$season,$game_id,$date,$gametype,$debug";
		$this->scrape_model->scrapeBoxScore($season,'0'.$gametype,$game_id,$date,$gametype,$debug);
		//FORMAT: scrapeBoxScore($season, $gameType, $currentID,$gameDate,$playoff_marker='2',$debug =1)
		//$this->scrape->scrapeBoxScore('20092010','02','0827','2010-02-02',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0836','2010-02-03',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0857','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0081','2010-10-14',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0863','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20082009','02','0259','2008-11-17',2,0);

		////$this->scrape_model->scrapeBoxScore('20082009','02','1077','2009-03-21',2,0);

		//$this->scrape->scrapeBoxScore('20082009','02','0409','2008-12-10',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0863','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0081','2009-10-14',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0857','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0836','2010-02-03',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0827','2010-02-02',2,0);

	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */