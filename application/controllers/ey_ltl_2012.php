<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ey_ltl_2012 extends CI_Controller {

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
    $this->load->library('form_validation');
    $this->load->helper('url');
    $this->load->helper('form');
  //this line doesnt do anything!
  }

  public function index()
  {
    $this->load->model('ey_ltl_model');
	if ($this->uri->segment(3, 0) === "reload")
	{
		echo $this->uri->segment(3, 0);
		$this->ey_ltl_model->reload_points();
	}
	if (!$this->ion_auth->logged_in())
    {
      redirect('beta');
    }
	$data['table'] = $this->ey_ltl_model->get_points_by_player();
	$this->load->view('stats/ey_ltl_view', $data);
  }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */