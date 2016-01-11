<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

  /**
	* Menu_dso
	*
	* Auth this open source controller for authentication CodeIgniter 3.0.3 <=
	*
	* controller Auth
	* author Mateusz Domin
	* Copyright Copyright (c) 2016 Mateusz Domin. (http://dominservice.pl/)
	* license LGPL-3.0 - http://opensource.org/licenses/LGPL-3.0
	* Link Http://dominservice.pl/package/Auth
	* declare this library in the "application/config/autoload.php"
	*/

class Auth extends CI_Controller {
	
	public function __construct()
	{
        parent::__construct();
		// authorization is in construct function
		if( !empty($this->session->userdata('role'))  ){
			$role = array();
			$role = explode(',', $this->session->userdata('role'));
			
			if( in_array(7, $role) OR in_array(8, $role) )
			{ 
				if($this->uri->segment(3) === 'logout')
				{
					$this->session->sess_destroy();
					redirect(base_url('user/auth/authorization'), 'refresh');
				}
				else redirect(base_url('user/panel'), 'refresh');
			}
			elseif( in_array(1, $role) OR in_array(2, $role) OR in_array(3, $role) OR in_array(4, $role) )
			{
				 redirect(base_url('administration/panel'), 'refresh');
			}
			elseif( in_array(6, $role) OR in_array(7, $role) )
			{
				 redirect(base_url('production/panel'), 'refresh');
			}
		}
		elseif( empty($this->session->userdata('role'))  )
		{
			$this->load->model('Land_model');
		}
    }
 
	public function index()
	{
		redirect(base_url('user/auth/authorization'), 'refresh');
	}
	
	public function verification_email()
	{
		if($this->uri->segment(3)){
				$code= htmlspecialchars(stripslashes(strip_tags(trim($this->uri->segment(4)))), ENT_QUOTES);
				$pass= htmlspecialchars(stripslashes(strip_tags(trim($this->uri->segment(5)))), ENT_QUOTES);
			$this->auth_dso->emailValid($code, $pass);
			redirect(base_url('user/auth/authorization'), 'refresh');
		}
		else
		{
			show_404();
		}
		
	}
	public function authorization()
	{
		if($this->input->post())
		{
			if($this->uri->segment(4) === 'login')
			{
				$inputArray = array(
						'DSOuser' => $this->input->post('DSOuser'),
						'DSOpass' => $this->input->post('DSOpass'),
				);
				$this->auth_dso->loginUser($inputArray);
				if( !empty($this->session->userdata('role'))){
					$this->db->where('id_user', $this->session->userdata('id_user'));
					$this->db->update('dso_users', array('login_date' => date('Y-m-d H:i:s')));
					redirect(base_url('user/panel'), 'refresh');
				}
			}
			elseif($this->uri->segment(4) === 'verification_new_pass')
			{
				if($this->uri->segment(5))
				{
					$code= htmlspecialchars(stripslashes(strip_tags(trim($this->uri->segment(4)))), ENT_QUOTES);
					$password= htmlspecialchars(stripslashes(strip_tags(trim($this->uri->segment(5)))), ENT_QUOTES);
					$s = array(
								'meta_title' => 'Wprowadź nowe hasło',
								'meta_keyword' => '',
								'meta_description' => '',
								'code' => $code,
								'pass' => $password,
					);
					$this->load->view('user/auth/newPassword', $s);
				}
				elseif(!$this->uri->segment(5))
				{
					$inputArray = array(
						'password' => $this->input->post('password'),
						'password2' => $this->input->post('password2'),
						'code' => $this->input->post('code'),
						'pass' => $this->input->post('pass'),
					);
					$this->Auth_model->updatePassword($inputArray);
				}
			}
			elseif($this->uri->segment(4) === 'generateNewCode')
			{
				$inputArray = array(
                    'DSOemail' => $this->input->post('DSOemail'),
				);
				$this->auth_dso->generateNewCode($inputArray);
			}
			elseif(empty($this->uri->segment(4) ))
			{
				$inputArray = array(
					'DSOfullname' => $this->input->post('DSOfullname'),
					'DSOemail' => $this->input->post('DSOemail'),
					'DSOuser' => $this->input->post('DSOuser'),
					'DSOpass' =>  $this->input->post('DSOpass'), 
					'DSOrpass' =>  $this->input->post('DSOrpass'),
					'DSOcompany' =>  $this->input->post('DSOcompany'),
					'DSOnip' =>  $this->input->post('DSOnip'),
					'DSOnip_prefix' =>  $this->input->post('DSOnip_prefix'),
					'DSOregon' =>  $this->input->post('DSOregon'),
					'DSOaddress' => $this->input->post('DSOaddress'),
					'DSOcity' => $this->input->post('DSOcity'),
					'DSOcountry' => $this->input->post('DSOcountry'),
					'DSOterms_and_conditions' => $this->input->post('DSOterms_and_conditions')
				);
				if($this->auth_dso->createAccound($inputArray) === TRUE){
					$this->db->where('id_user', $this->session->userdata('id_user'));
					$this->db->update('dso_users', array('login_date' => date('Y-m-d H:i:s')));
					redirect(base_url('user/panel'), 'refresh');
				}
			}
		}
		$head = array(
			'meta_title' => 'Logowanie',
			'meta_keyword' => 'logowanie,dostęp,rejestracja',
			'meta_description' => 'Logowanie do serwisu i formularz rejestracyjny',
		);
		$content = array(
			'name_lang' =>$this->Land_model->countries(),
			);
		$this->load->view('template_head', $head);
		$this->load->view('user/login', $content);
		$this->load->view('template_footer' );
    }
	public function logout()
	{
        $this->session->sess_destroy();
		redirect(base_url('user/auth/authorization'), 'refresh');
    }
	public function forgotPass()
	{
		if($this->input->post('DSOpass'))
		{
			$inputArray = array(
					'DSOpass' =>  $this->input->post('DSOpass'), 
					'DSOrpass' =>  $this->input->post('DSOrpass'),
					'DSOcode' =>  $this->input->post('DSOcode'),
					'DSOcode2' =>  $this->input->post('DSOcode2'),
			);
			if($this->auth_dso->newPassword($inputArray) === TRUE){
				$this->db->where('id_user', $this->session->userdata('id_user'));
				$this->db->update('dso_users', array('login_date' => date('Y-m-d H:i:s')));
				redirect(base_url('user/auth/authorization'), 'refresh');
			}
		}
		else
		{
			$head = array(
				'meta_title' => 'Logowanie',
				'meta_keyword' => 'logowanie,dostęp,rejestracja',
				'meta_description' => 'Logowanie do serwisu i formularz rejestracyjny',
			);
			$this->load->view('template_head', $head);
			$this->load->view('user/forgot_pass');
			$this->load->view('template_footer' );
		}
	}
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */
