<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar_model extends CI_Model
{

    public function __construct() {
        parent::__construct();
    }

    public function getEvents($start = false, $end = false)
	{

        $this->db->select('id, title, start, end, description, color, holiday');
        $this->db->where('start >=', $start)->where('start <=', $end);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;

    }

    public function getEventByID($id = false)
	{
        $q = $this->db->get_where('calendar', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addEvent($data = array())
	{
        if ($this->db->insert('calendar', $data)) {
            return true;
        }
        return false;
    }

    public function updateEvent($id = false, $data = array())
	{
        if ($this->db->update('calendar', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteEvent($id = false)
	{
        if ($this->db->delete('calendar', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

	public function addCalendar($data = array())
	{
		if($data){
			foreach($data as $row){
				$row['holiday'] = 1;
				$row['user_id'] = $this->session->userdata("user_id");
				$this->db->insert("calendar", $row);
			}
			return true;
		}
		return false;
	}

	public function deleteCalendar($id = false)
	{	
		if($this->db->where("id",$id)->delete("calendar")){
			return true;
		}
		return false;
	}
	
	
	
}
