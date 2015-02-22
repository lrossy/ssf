<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * BackendPro
 *
 * An open source development control panel written in PHP
 *
 * @package		BackendPro
 * @author		Adam Price
 * @copyright	Copyright (c) 2008, Adam Price
 * @license		http://www.gnu.org/licenses/lgpl.html
 * @link		http://www.kaydoo.co.uk/projects/backendpro
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Cron
 *
 * The cron controller
 *
 * @package  	Stats
 * @subpackage  Controllers
 */
class Cron extends Public_Controller
{
	function Cron()
	{
		parent::Public_Controller();
	}

	function index()
	{
		// Display Page
		//get basic info

	}
	function tonightsGames()
	{
		$this->load->model('scrape');
		$currentGames = $this->scrape->nightly();
		$this->unSetMemCache($currentGames);
	}
	function dailyWrapUp()
	{
		$this->load->model('scrape');
		$this->scrape->dailyWrapUp();
	}
	function reScrape()
	{
		$this->load->model('scrape');
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
		$this->load->model('scrape');
		$this->scrape->getPlayerMugs();
	}
	function getSched(){
		$this->load->model('scrape');
	//	$this->scrape->getNHLSchedualPlayed(2);
		$this->scrape->getNHLSchedual(3);
	}
	function scrapeFile()
	{
		$this->load->model('scrape');
		$this->scrape->scrapeFile('GS020259.HTM','ES020259.HTM',259);

	}
	function scrapeBoxScore()
	{
		$this->load->model('scrape');
		//FORMAT: scrapeBoxScore($season, $gameType, $currentID,$gameDate,$playoff_marker='2',$debug =1)
		//$this->scrape->scrapeBoxScore('20092010','02','0827','2010-02-02',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0836','2010-02-03',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0857','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0081','2010-10-14',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0863','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20082009','02','0259','2008-11-17',2,0);
		$this->scrape->scrapeBoxScore('20082009','02','1077','2009-03-21',2,0);
		//$this->scrape->scrapeBoxScore('20082009','02','0409','2008-12-10',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0863','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0081','2009-10-14',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0857','2010-02-06',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0836','2010-02-03',2,0);
		//$this->scrape->scrapeBoxScore('20092010','02','0827','2010-02-02',2,0);





	}
}


/* End of file cron.php */
/* Location: ./modules/cron/controllers/cron.php */
