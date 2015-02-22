<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contact extends CI_Controller {

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
	}

	public function index()
	{    $user = $this->ion_auth->get_user();
    
		//validate form input
		$this->form_validation->set_rules('first_name', 'First Name', 'required|xss_clean');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required|xss_clean');
		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email');
        $this->form_validation->set_rules('message', 'Message', 'required|xss_clean');
		if ($this->form_validation->run() == FALSE)
		{
		    if($user && $this->form_validation->set_value('first_name') ==''){
		        $firstName = $user->first_name;
		    }
		    else{ $firstName = $this->form_validation->set_value('first_name');}
		    if($user && $this->form_validation->set_value('last_name') ==''){
		        $last_name = $user->last_name;
		    }
		    else{ $last_name = $this->form_validation->set_value('last_name');}
		    if($user && $this->form_validation->set_value('email') ==''){
		        $email = $user->email;
		    }
		    else{ $email = $this->form_validation->set_value('email');}
		    $this->data['first_name'] = array('name' => 'first_name',
				'id' => 'first_name',
				'type' => 'text',
				'size' => '30',
				'value' => $firstName,
			);
			$this->data['last_name'] = array('name' => 'last_name',
				'id' => 'last_name',
				'type' => 'text',
				'size' => '30',
				'value' => $last_name,
			);
			$this->data['email'] = array('name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'size' => '30',
				'value' => $email,
			);
			$this->data['reason'] = array(
                              'general'  => 'General Inquiries',
                              'bug'    => 'Bug',
                              'feature'   => 'Feature Request'
                            );

			$this->data['message'] = array('name' => 'message',
				'id' => 'message',
				'rows' => '20',
				'cols' => '20',
				'value' => $this->form_validation->set_value('message'),
			);
			
			$this->load->view('contact',$this->data);
		}
		else
		{
		    $first_name =$this->input->post('first_name');
		    $last_name =$this->input->post('last_name');
			$email = $this->input->post('email');
			$message = $this->input->post('message');
			$reason = $this->input->post('reason');
			$data = array(
               'first_name' => $first_name ,
               'last_name' => $last_name ,
               'email' => $email,
               'reason' => $reason,
               'message' => $message
            );

            $this->db->insert('contact', $data);
			$this->load->view('contact_success');
		}
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */