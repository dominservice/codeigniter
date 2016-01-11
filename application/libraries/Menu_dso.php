<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu_dso {
	
	/**
	* Menu_dso
	*
	* Menu_dso this open source application for authentication CodeIgniter 3.0.3 <=
	*
	* package Menu_dso
	* author Mateusz Domin
	* Copyright Copyright (c) 2016 Mateusz Domin. (http://dominservice.pl/)
	* license LGPL-3.0 - http://opensource.org/licenses/LGPL-3.0
	* Link Http://dominservice.pl/package/Menu_dso
	* declare this library in the "application/config/autoload.php"
	* To start a multi-level menu, simply insert the template
	* " <ul><?php echo $this->menu_dso->menu(); ?></ul> "
	*/

	/**
	* Menu_dso
	*
	* Library to create a multilevel menu
	*/
	
	// database tabless used in this library
	private $menu= 'menu';
	
	
	/**
	 * Reference to the CodeIgniter instance
	 *
	 * @var object
	 */
	public $c = '';
	public $a = '';
	public $p = '';
	
	protected $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		if( !empty($this->CI->session->userdata('role'))  ){
			$role = array();
			$role = explode(',', $this->CI->session->userdata('role'));
			
			if( in_array(7, $role) OR in_array(8, $role) )
			{ 
				$this->c = 1;
			}
			elseif( in_array(1, $role) OR in_array(2, $role) OR in_array(3, $role) OR in_array(4, $role) )
			{
				 $this->a= 1;
			}
			elseif( in_array(6, $role) OR in_array(7, $role) )
			{
				$this->p = 1;
			}
		}
		//$this->CI->load->library('parser');
    }
	
	public function menu()
	{
		$query = $this->CI->db->select(array('*'));
		if($this->c  === 1){ 
			 $this->CI->db->where('permissions', '1');
			 $this->CI->db->or_where('permissions', '0');
			 $folder = 'user/';
		}elseif($this->a === 1){
			 $this->CI->db->where('permissions', '2');
			 $this->CI->db->or_where('permissions', '1');
			 $this->CI->db->or_where('permissions', '0');
			 $folder = 'administration/';
		} elseif($this->c !==1 AND $this->a !==1 AND $this->p !==1){
			 $this->CI->db->where('permissions', '100');
			 $this->CI->db->or_where('permissions', '0');
			 $folder = '';
		} 
		$query = $this->CI->db->order_by('parent, sort, label', "asc");
		$query = $this->CI->db->get($this->menu);
		// Select all entries from the menu table
		// Create a multidimensional array to conatin a list of items and parents
		$menu = array(
			'items' => array(),
			'parents' => array()
		);
		// Builds the array lists with data from the menu table
		foreach ( $query->result_array() as $items)
		{
			// Creates entry into items array with current menu item id ie. $menu['items'][1]
			$menu['items'][$items['id_menu']] = $items;
			// Creates entry into parents array. Parents array contains a list of all items with children
			$menu['parents'][$items['parent']][] = $items['id_menu'];
		}
		return $this->sort_menu(0, $menu, $folder);		//.var_dump($menu)
	}
	
	protected function sort_menu($parent, $menu, $folder)
	{
		 $html = "";
		   if (isset($menu['parents'][$parent]))
		   {	
				if($parent <> 0)
				{
					$html .= '
					<ul class="sub-menu">'."\n";
				}
			   foreach ($menu['parents'][$parent] as $itemId)
			   {
				   $a =''; 
				   $_link = $menu['items'][$itemId]['link']; 
				   if($this->CI->uri->segment(2)."/".$this->CI->uri->segment(3).'/'.$this->CI->uri->segment(4).'/'.$this->CI->uri->segment(5) === $_link and empty($this->CI->uri->segment(6)))
					{ 
						$a = ' active open';
					}
					elseif($this->CI->uri->segment(2)."/".$this->CI->uri->segment(3).'/'.$this->CI->uri->segment(4) === $_link  and empty($this->CI->uri->segment(5)))
					{ 
						$a = ' active open';
					}
					elseif($this->CI->uri->segment(2)."/".$this->CI->uri->segment(3) === $_link  and empty($this->CI->uri->segment(4)))
					{ 
						$a = ' active open';
					} 
					elseif($this->CI->uri->segment(2) === $_link  and empty($this->CI->uri->segment(3)))
					{ 
						$a = ' active open';
					} 
					
					if(!isset($menu['parents'][$itemId]))
					{
						$html .= '<li class="'.$a.' tooltips" data-container="body" data-placement="right" data-html="true" data-original-title="'.$menu['items'][$itemId]['description'].'"><a href="'.base_url($folder.$_link ).'"><i class="fa fa-'.$menu['items'][$itemId]['icon'].'"></i><span class="'.$menu['items'][$itemId]['color'].'">&nbsp;'.$menu['items'][$itemId]['label'].'</span></a></li>'."\n";
					}
					if(isset($menu['parents'][$itemId]))
					{
						$html .= '<li class="'.$a.' tooltips" data-container="body" data-placement="right" data-html="true" data-original-title="'.$menu['items'][$itemId]['description'].'"><a href="javascript:;" ><i class="fa fa-'.$menu['items'][$itemId]['icon'].'"></i><span class="'.$menu['items'][$itemId]['color'].'">'.$menu['items'][$itemId]['label'].'</span><span class="arrow "></span></a>'."\n";
						$html .= $this->sort_menu($itemId, $menu, $folder);
						$html .= "</li> \n";
					}
			   }
			   if($parent <> 0)
				{
					$html .= "
					</ul>\n";
				}
		   }
		   return $html;
	}
}
