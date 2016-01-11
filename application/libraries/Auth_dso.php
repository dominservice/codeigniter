<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Auth_dso {
	
	/**
	* Auth_dso
	*
	* Auth_dso this open source application for authentication CodeIgniter 3.0.3 <=
	*
	* package Auth_dso
	* author Mateusz Domin
	* Copyright Copyright (c) 2016 Mateusz Domin. (http://dominservice.pl/)
	* license LGPL-3.0 - http://opensource.org/licenses/LGPL-3.0
	* Link Http://dominservice.pl/package/Auth_dso
	*/

	/**
	* Auth_dso
	*
	* Library responsible for registering Useful Links.
	* Authorization, generating new passwords and tokens.
	* Recapcha, broadcasting rights.
	*/
	
	// database tabless used in this library
	protected $users = 'dso_users';
	protected $roles = 'dso_user_role';
	protected $role_list = 'dso_user_role_list';
	protected $employee = 'dso_employee';
	protected $company = 'dso_company';
	
	/**
	 * Reference to the CodeIgniter instance
	 *
	 * @var object
	 */
	protected $CI;
	
	public function __construct()
    {
		$this->CI =& get_instance();
		
		// load defoult encrypt library
		$this->CI->load->library('encrypt');
		
		// load email library to send mail for user
		$this->CI->load->library('email');
		
		empty($config) OR $this->initialize($config);

		log_message('info', 'Auth_dso Class Initialized');
    }

    public function createAccound(array $r)
    {
		$this->CI =& get_instance();
		
		$this->CI->form_validation->set_rules('DSOfullname', 'Imię i Nazwisko', 'required|min_length[3]|max_length[50]');
        $this->CI->form_validation->set_rules('DSOemail', 'E-mail', 'required|max_length[70]|valid_email|is_unique[dso_users.email]');
		$this->CI->form_validation->set_rules('DSOuser', 'Nazwa użytkownika', 'required|min_length[6]|max_length[50]|is_unique[dso_users.username]');
        $this->CI->form_validation->set_rules('DSOpass', 'Hasło', 'required|min_length[6]|max_length[50]');
        $this->CI->form_validation->set_rules('DSOrpass', 'Powtórz hasło', 'required|max_length[50]|matches[DSOpass]');
		$this->CI->form_validation->set_rules('DSOcompany', 'Nazwa firmy', 'required|max_length[50]');
		$this->CI->form_validation->set_rules('DSOnip', 'NIP firmy', 'required|numeric|is_unique[dso_company.tax_identification_nr]');//min_length[10]|max_length[11]|
		$this->CI->form_validation->set_rules('DSOregon', 'REGON', 'numeric|is_unique[dso_company.regon]');
		$this->CI->form_validation->set_rules('DSOaddress', 'Adres Firmy', 'required|min_length[6]|max_length[150]');
		$this->CI->form_validation->set_rules('DSOcity', 'Poczta', 'required|min_length[3]|max_length[50]');
		$this->CI->form_validation->set_rules('DSOcountry', 'Państwo', 'required|min_length[3]|max_length[80]');
		$this->CI->form_validation->set_rules('DSOterms_and_conditions', 'Regulamin', 'required');
		
		if($this->CI->form_validation->run() == TRUE)
		{
			$pass = hash('sha256', $r['DSOpass']); // encode password 
				
			$nip = $r['DSOnip'];
			$nip_bez_kresek = preg_replace("/-/","",$nip);
			$reg = '/^[0-9]{10}$/';
			if(!empty($r['DSOprefix_tax_ident_nr_eu']))
			{
				$blad = '0';
			}
			elseif(preg_match($reg, $nip_bez_kresek)==false)
			{
				$blad = '1';
				$this->CI->session->set_flashdata('error', 'Nip jest nieprawidłowy');
			   return FALSE;
			}
			else
			{
				$dig = str_split($nip_bez_kresek);
				$kontrola = (6*intval($dig[0]) + 5*intval($dig[1]) + 7*intval($dig[2]) + 2*intval($dig[3]) + 3*intval($dig[4]) + 4*intval($dig[5]) + 5*intval($dig[6]) + 6*intval($dig[7]) + 7*intval($dig[8]))%11;
				if(intval($dig[9]) == $kontrola)
				{
					$blad = '0';
				}
				else
				{
					$blad = '1';
					$this->CI->session->set_flashdata('error', 'Nip jest nieprawidłowy');
					return FALSE;
				}
			}
			if($blad === '0')
			{
				$date = new DateTime();
				$date->modify('+90 days');
				$end_date = $date->format('Y-m-d');
				$email = $r['DSOemail'];
				$code = uniqid(rand());
				$data = array(
					'fullname' => $r['DSOfullname'],
					'username' =>$r['DSOuser'],
					'email' =>  $email,
					'date_upd' =>date('Y-m-d H:i'),
					'date_add' =>date('Y-m-d H:i'),
					'password' =>  $pass,
					'code' =>  $code,
					'role_user' => '7,8'
				);
				$query = $this->CI->db->insert($this->users, $data); 
				$query2 = $this->CI->db->get_where($this->users, array('email' => $email));
				$row = $query2->row_array();
				if($row <> 0)
				{
					$data = array(
						'id_user' => $row['id_user'],
						'name_company' =>  $r['DSOcompany'],
						'tax_identification_nr' => $r['DSOnip'],
						'company_add' =>  date('Y-m-d H:i'),
						'company_upd' =>  date('Y-m-d H:i'),
						'date_pro' =>  $end_date,
						
					);
					if($r['DSOnip_prefix'] <> 0 )
					{
						$data['tax_ident_nr_eu'] = '1';
						$data['prefix_tax_ident_nr_eu'] = $r['DSOnip_prefix'];
					}
					$query3 = $this->CI->db->insert($this->company, $data); 
				}
				$salect_id_company = $this->CI->db->get_where($this->company, array('id_user' => $row['id_user']));
				$s = $salect_id_company->row_array();
				if($s <> 0)
				{
					$update = $this->CI->db->update($this->company, array('id_company' => $s['id_company']),  array( 'id_user' =>$row['id_user'],)); 
				}
				if($query == TRUE AND $query2 == TRUE AND $update === TRUE)
				{
					$mail_to = $r['DSOemail'];
					$this->CI->email->from('biuro@'.$_SERVER['HTTP_HOST'], 'prioritycargo.eu');
					$this->CI->email->cc('biuro@'.$_SERVER['HTTP_HOST'], 'prioritycargo.eu');
					$this->CI->email->bcc('biuro@'.$_SERVER['HTTP_HOST'], 'prioritycargo.eu');
					$this->CI->email->to($mail_to);  
					$this->CI->email->reply_to('no_reply@'.$_SERVER['HTTP_HOST'], $this->CI->session->userdata('username'));
					$this->CI->email->subject('Weryfikacja adresu E-mail');
					$this->CI->email->message('Witaj '.$r['DSOfullname'].' ! <br /> <a href="http://'.$_SERVER['SERVER_NAME'].'/user/auth/verification_email/'.$code.'/'.$pass. '" >Kliknij </a> aby aktywować swoje konto.<br />Wiadomość została wygenerowana automatycznie przez system. Prosimy nie odpowiadać na tego maila.<hr><img src="'.base_url().'assets/admin/layout/img/logo.png" style="width:10%; height:10%" />');	

					$this->CI->email->send('Aktywacja konta w'.base_url());
					$this->CI->session->set_flashdata('success', 'Rejestracja przebiegła pomyślnie, proszę zalogować się do swojej poczty i kliknąć w link aktywacyjny w mailu od nas.');
					
					$email = $this->CI->email->send('Wiadomość z '.base_url());
				
					if( $email )
					{
						$this->CI->session->set_flashdata('success', 'Wiadomość została wysłana'); 
					}
					else 
					{
						$this->CI->session->set_flashdata('error', 'Wystąpił nieoczekiwany błąd, spróbuj jeszcze raz '.$this->CI->email->print_debugger(array('headers'))); 
					}
				}
			}
		}
		else
		{
            $this->CI->session->set_flashdata('error', validation_errors());
        }
	} /** end create_accound function */
	
	public function generateNewCode(Array $r)
	{
		/**
		* This function is for generate a new code to the change a password
		*/
		$spr = $this->CI->db->select(array('*'))->where('email', $r['DSOemail'])->limit(1)->get($this->users);
		if( $spr->num_rows() <> 0)
		{
			$email = $r['DSOemail'];
			$code = uniqid(rand());
			$row = $spr->row_array();
			$query3 = $this->CI->db->update($this->users, array('code' => $code),  array( 'email' =>$email,)); 

			$this->CI->email->from('no_reply@'.$_SERVER['HTTP_HOST'], 'Weryfikacja adresu E-mail');
			$this->CI->email->to($email);  
			$this->CI->email->subject('Weryfikacja adresu E-mail');
			$this->CI->email->message('<b>Witaj '.$row['fullname'].' ! </b><hr><a href="http://'.$_SERVER['SERVER_NAME'].'/user/auth/forgotPass/'.$code.'/'.$row['password'].'" >Kliknij </a>aby przejść do formularza zmiany hasła.<br /><br />Wiadomość została wygenerowana automatycznie przez system. Prosimy nie odpowiadać na tego maila.<hr>	
			<img src="'.base_url().'assets/admin/layout/img/logo.png" style="width:10%; height:10%" />');
			$send = $this->CI->email->send('zmiana hasła w'.base_url());
			if($send===true)
			{
				$this->CI->session->set_flashdata('success', 'Na twój adres email został wysłany link zabezpieczający dziąki któremu będziesz mógł ustawić nowe hasło');
				return TRUE;
			}
		}
		else
		{
			$this->CI->session->set_flashdata('error', 'Podany adres Email nie istnieje w naszej bazie.');
			return FALSE;
		}
    } /** end generateNewCode function */
	
	public function newPassword(Array $r)
	{
		$this->CI->form_validation->set_rules('DSOpass', 'Hasło', 'required|min_length[6]|max_length[50]');
        $this->CI->form_validation->set_rules('DSOrpass', 'Powtórz hasło', 'required|max_length[50]|matches[DSOpass]');
		if($this->CI->uri->segment(2) === 'updatePassword')
		{
			$this->CI->form_validation->set_rules('DSOold_pass', 'Aktualne hasło', 'required|min_length[6]|max_length[50]|!matches[DSOpass]');
		}
		if($this->CI->form_validation->run() == TRUE)
		{
			$password = hash('sha256', $r['DSOpass']); // encode password 
			if($this->CI->uri->segment(3) === 'updatePassword')
			{
				$old_pass = hash('sha256', $r['DSOold_pass']); // encode old password 
				$data = array(
						'password' =>  $password,
						'date_upd' =>  date('Y-m-d H:i'),
				);
				
				$query2 = $this->CI->db->get_where($this->users, array('id_user'=>$this->CI->session->userdata('id_user'), 'password' => $old_pass))->row_array();
				$p = $query2;
				$code = $p['code'];
				if($old_pass === $p['password'])
				{
					$this->CI->session->set_flashdata('error', 'Nowe hasło nie może być identyczne ze starym.');
					return FALSE;
				}
			}
			elseif($this->CI->uri->segment(3) === 'forgotPass')
			{
				$code = $r['DSOcode'];
				$old_pass = $r['DSOcode2'];
				$data = array(
						'password' =>  $password,
						'date_upd' =>  date('Y-m-d H:i'),
					);
			}
			if($this->CI->db->update($this->users, $data,  array( 'password' =>$old_pass, 'code' => $code)) )
			{
				$this->CI->session->set_flashdata('success', 'Zmiana hasła przebiegła pomyślnie, od tej pory używaj nowego hasła do logowania się w systemie.');
				return TRUE;
			}
			else
			{
				$this->CI->session->set_flashdata('error', 'Wystąpił błąd. Spóbuj ponownie za kilka minut.');
				return FALSE;
			}
		}
		else
		{
			$this->CI->session->set_flashdata('error', validation_errors());
			return FALSE;
		}
	}/** end newPassword function */
	
	public function loginUser(Array $array)
	{
        $this->CI->form_validation->set_rules('DSOuser', 'Email', 'required|min_length[6]|max_length[70]');
        $this->CI->form_validation->set_rules('DSOpass', 'Hasło', 'required|min_length[6]|max_length[50]');
        if($this->CI->form_validation->run() == TRUE)
		{
			
			$pass = hash('sha256', $array['DSOpass']);; // encode password 
			//$pass = $this->CI->encrypt->encode($array['DSOpass']); // encode password 
            $query = $this->CI->db->select(array('id_user', 'fullname', 'username', 'email', 'login_date', 'data_user', 'data_company', 'avatar', 'avatar_path', 'avatar_type','role_user'))
												->where('password', $pass)
												->where('active', 1)
												->where('username', $array['DSOuser'])
												->or_where('email', $array['DSOuser'])
												->limit(1)
												->get($this->users);
            if($query->num_rows() > 0)
			{
                $sessionArray = array();
                foreach($query->result_array() as $key)
				{
					$query2 = $this->CI->db->select(array('id_company', 'id_user', 'name_company', 'date_pro'))
								->where('id_user', $key['id_user'])
								->where_not_in('id_user', '1')
								->limit(1)
								->get($this->company);
					foreach($query2->result_array() as $key2)
					{
						if ($key['id_user'] === $key2['id_user'] and $key['data_user'] === '0' )
						{
							$sessionArray['data_user']         =  '<div class="alert alert-warning"><p><strong>Uwaga! </strong> Aby móc korzystać z pełnej funkcjonalności systemu musisz uzupełnić wszystkie niezbędne <a href="'.base_url('user/settings/edit_profile').'">dane użytkownika.</a></p>
							<p><strong>Warning!  </strong> 
							To be able to use the full functionality of the system you have to make all the necessary <a href="'.base_url('user/settings/edit_profile').'">user data</a>.</p>
							</div>';
						}
						if ($key['id_user'] === $key2['id_user'] and $key['data_company'] === '0' )
						{
							$sessionArray['data_company']         =  '<div class="alert alert-warning"><p><strong>Uwaga! </strong> Aby móc korzystać z pełnej funkcjonalności systemu musisz uzupełnić wszystkie niezbędne <a href="'.base_url('user/settings/edit_company').'">dane Twojej firmy</a>.</p>
							<p><strong>Warning! </strong> 
							To be able to use the full functionality of the system you have to make all the necessary <a href="'.base_url('user/settings/edit_company').'">data your company</a>.</p>
							</div>';
						}
						$date1 = new DateTime();
						$date_ = $date1->format('Y-m-d');
						$date = new DateTime();
						$date->modify('+15 days');
						$end_date = $date->format('Y-m-d');
						if($key2['date_pro'] < $date_)
						{
							$sessionArray['date_pro'] =  '<div class="alert alert-warning"><p><strong>Uwaga! </strong> W dniu '.$key2['date_pro'].' skończył się okres ważności Twojego konta, Aby móc odblokować Swoje konto napisz do nas po przez ten formularz. jeśli nie opłacisz abonamentu do 30 dni kalendarzowych od daty wygaśnięcia, konto zostanie całkowicie usunięte z systemu.</p>
							<p><strong>Warning! </strong> 
							On '. $key2['date_pro'].' ended the period of validity of your account To be able to unlock your account email us after this form. if you do not pay a subscription for 30 calendar days from the date of expiry, the account will be completely removed from the system
							</p>
							</div>';
							$this->CI->session->set_userdata($sessionArray);
							redirect(base_url('contact/block_account'), 'refresh');
						}
						elseif($key2['date_pro'] <= $end_date)
						{
							$sessionArray['date_pro'] =  '<div class="alert alert-warning"><p><strong>Uwaga! </strong> Kończy się okres ważności konta. W dniu '.$end_date.' konto Twojej firmy zostanie wyłączone. Jeśli chcesz nadal kożystać z naszego systemu wykup abonament na kolejny okres, możesz to zrobić <a href="'.base_url('user/pay/account').'">tutaj</a>.</p>
							<p><strong>Warning! </strong> 
							It ends with the period of validity of the account. On '. $end_date.' your company\'s account will be disabled. If you want to continue with our system kożystać a subscription for a further period, you can do so <a href="'.base_url('user/pay/account').'"> here</a>
							</p>
							</div>';
						}
						
					}
                    $sessionArray['id_user']		= $key['id_user'];
                    $sessionArray['login_date']		= $key['login_date'];
					$sessionArray['fullname']		= $key['fullname'];
					$sessionArray['username']		= $key['username'];
					$sessionArray['email']			= $key['email'];
					$sessionArray['avatar']			= base_url($key['avatar_path'].'/'.$key['avatar'].'_thumb.'.$key['avatar_type']);
					$sessionArray['id_company']    = $key2['id_company'];
					$sessionArray['id_admin_company']    = $key2['id_user'];
					$sessionArray['name_company']    = $key2['name_company'];
					$sessionArray['role']    = $key['role_user'];
                }
                $this->CI->session->set_userdata($sessionArray);
                return TRUE;
            }
            else
			{
                $this->CI->session->set_flashdata('error', 'Niepoprawne dane');
                return FALSE;
            }
        }
        else
		{
            $this->CI->session->set_flashdata('error', validation_errors());
            return FALSE;
        }
    }
	
	public function emailValid($code,$pass)
	{
		$query = $this->CI->db->select(array('id_user'))->where('active', '1')->where('code', $code)->where('active', '1')->get($this->users);
		if ($query->num_rows() <> 0) 
		{
			$this->CI->session->set_flashdata('info', 'Aktywowałeś/aś już swoje konto.');
			return FALSE;
		}
		else
		{
			$this->CI->db->query("DELETE FROM ".$this->users." WHERE date_add<=DATE_SUB(NOW(),INTERVAL 2 DAY) and active=0", 0);
			$update_data = array( 'active' => '1', 'date_upd' => date('Y-m-d H:i')); 
			$this->CI->db->update($this->users, $update_data, array('code' => $code, 'active' => '0'));
			if ($this->CI->db->get_where($this->users, array('code' => $code, 'active' => '1'))) {
				$this->CI->session->set_flashdata('success', 'Weryfikacja przebiegła pomyślnie, więc teraz pozostało Ci tylko się zalogować i zarabiać pieniądze. Powodzenia!');
				return TRUE;
			}
			else
			{
				$this->CI->session->set_flashdata('error', 'Niestety coś poszło nie tak. Najprawdopodobnie kod aktywacyjny już wygasł, ponieważ był ważny dokładnie co do sekundy 24 godziny.');
				return FALSE;
			}
		}
		
	}
}
/* End of file libraries/Auth_dso.php */
