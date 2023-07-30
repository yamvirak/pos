<?php
class MY_DB_mysqli_driver extends CI_DB_mysqli_driver{
   
    private $CI;
	
    public function __construct($params)
    {
        parent::__construct($params);
        $this->CI =& get_instance();
        $this->CI->config->load('trail');
    }
  
    public function insert($table = '', $set = NULL, $escape = NULL)
    {
		$auto_status = 1;
		$this->trail($auto_status,'insert', $table, $set);
        $status = parent::insert($table , $set, $escape);
        return $status;
    } 
 
   /*  public function insert_batch($table, $set = NULL, $escape = NULL, $batch_size = 1000)
    {
        $affected_rows = parent::insert_batch($table , $set, $escape, $batch_size);
        $this->trail($affected_rows,'insert', $table, $set);
        return $affected_rows;
    } */
 
    public function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
    {
        $condition = isset($this->qb_where) ? $this->qb_where : "";
        $previous_values = null;
        if(empty($where)){
            $query = $this->get($table);
        }else{
            $query = $this->get_where($table, $where);
		}
        $previous_values = $query->row_array();
        $query->free_result();
        $this->qb_where = $condition; // reset where condition
        $status = parent::update($table, $set, $where, $limit);
        $this->trail($status,'update', $table, $set, $previous_values);
        return $status;
    }
   
    public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE)
    {
        $condition = isset($this->qb_where) ? $this->qb_where : '';
        $previous_values = null;
        if(empty($where)){
            $query = $this->get($table);
        }else{
            $query = $this->get_where($table, $where);
		}
        $previous_values = $query->row_array();
        $query->free_result();
        $this->qb_where = $condition; // reset where condition
        if($this->CI->config->item('sess_save_path') == $table && $where == ''){
            $where = "id = '{" . $this->CI->session->session_id . "}'";
        }
        $status= parent::delete($table, $where, $limit, $reset_data);
        $this->trail($status,'delete', $table, $where, $previous_values);
        return $status;
    }
   
    public function trail($status, $event, $table, $set = NULL, $previous_values = NULL)
    {
        if(!$status) return 1;  // event not performed
        if(!$this->CI->config->item('audit_enable')) return 1; // trail not enabled
        if($event === 'insert' && !$this->CI->config->item('track_insert')) return 1; // insert tracking not enabled
        if($event === 'update' && !$this->CI->config->item('track_update')) return 1; // update tracking not enabled
        if($event === 'delete' && !$this->CI->config->item('track_delete')) return 1; // delete tracking not enabled
        if(in_array($table, $this->CI->config->item('not_allowed_tables'))) return 1; // table tracking not allowed
		
        if($event == 'update') {
            $this->diff_on_update($previous_values, $set);
            if(empty($previous_values) && empty($set)){
                return 1;
			}
        }
		$new_value = null;
        $old_value = null;
        if(!empty($previous_values)){
			$old_value = json_encode($previous_values);
		}else{
			$new_value = json_encode($set);
		}
		return parent::insert('cus_audit_trails' ,
			[
				'user_id' 		=> (isset($this->CI->session->userdata['user_id']) ? $this->CI->session->userdata['user_id'] : 0),
				'event' 		=> $event,
				'table_name' 	=> $table,
				'old_values' 	=> $old_value,
				'new_values' 	=> $new_value,
				'url' 			=> $this->CI->uri->ruri_string(),
				'created_at' 	=> date('Y-m-d H:i:s'),
			]);
    }
  
    public function diff_on_update(&$old_value = NULL, &$new_value = NULL)
    {
        $old = [];
        $new = [];
        foreach($new_value as $key => $val) {
            if(isset($new_value[$key])) {
                if(isset($old_value[$key])) {
                    if($new_value[$key] != $old_value[$key]) {
                        $old[$key] = $old_value[$key];
                        $new[$key] = $new_value[$key];
                    }
                } else {
                     $old[$key] = '';
                     $new[$key] = $new_value[$key];
                }
            }
        }
        $old_value = $old;
        $new_value = $new;
    }
	
	
	
}