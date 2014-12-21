<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Company
 *
 * @author hp
 */
class CompanyModel extends CI_Model {
    public function searchCompany($search_txt) {
        return $this->db->query('SELECT * FROM "companies" WHERE "company_name" ILIKE \'%'.$search_txt.'%\' ESCAPE \'!\' AND "description" ILIKE \'%'.$search_txt.'%\' ESCAPE \'!\' ORDER BY employee_range_code;');
    }
    public function save($result_set) {
        $company_list = $result_set->companies->values;
        foreach ($company_list as $desc) {
            $data = array(
                'company_name'  =>  $desc->name,
                'description'  =>  $desc->description,
                'employee_range'  =>  $desc->employeeCountRange->name,
                'employee_range_code'  =>  $desc->employeeCountRange->code
            );
            $this->db->insert('companies', $data);
        }
        return true;
    }
}

?>
