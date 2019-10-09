<?php

namespace App\Model; 

use App\Model\Model; 
use \PDO; 
use App\utilities\Purifier;
use App\utilities\Validator;

class AppModel extends Model{
	protected $errors=[]; 
	private $id='';

	public function hydrate($verifyInputs=[]){
		$inputs=[]; 
		if (empty($verifyInputs)){
			$verifyInputs=$_POST; 
		}// à supprimer et à passer en params. 
		foreach ($verifyInputs as $key => $value) {
			$key = Purifier::htmlPurifier($key);
			$value=Purifier::htmlPurifier($value);
			
			$function = "set".$key;
			$input = $value;
			$inputs+= [$key=>$value];
			 
			if(method_exists(get_class($this), $function)){
				$this->$function($input); 
			} 
		} 
		return $inputs; 
	}
	

	public function verifUse($label, $input ){  
		$request=$this->db->prepare("SELECT * FROM {$this->table} WHERE $label=:label");
		$request->execute(array(
			':label'=>"$input"
		)); 
		$result=$request->fetch(PDO::FETCH_ASSOC);
		$request->closeCursor();  
		return $result;
	}


	/*
	*@id if id needs (update, delete)
	*verify errors
	*launch request 
	*return bool
	**/
	protected function creationSuccess($function, $fields)	{ 
		
		if (!empty($this->errors)){
			$result['errors']=$this->errors;
			foreach ($fields as $key => $value) {
				$result[$key]=$value; 
			}
			return $result; 
		}
		if (method_exists($this, 'id')){
			$id=$this->id();
		}/*else{
			$id =""; à voir si fonctionne. 
		}   */

		if (($this->$function($fields, $id)!==true)){ 
			$this->errors[]="L'enregistrement a échoué"; 
			$result['errors']=$this->errors;
			return $result; 
		}else{
			return true; 
		}
	}


	public function validation($inputs, $validatorName="getValidator"){
		$validator = $this->$validatorName($inputs);  
		if ($validator->isValid()){
			return true; 
		}
		$error=($validator->getErrors()); 
		foreach ($error as $key => $value) {
			array_push($this->errors, $value); 
		}
		
	}
/*each class had to redeclare this function getValidator, to define which validation must be done.  */
	/*protected function getValidator($inputs){
	}*/

	protected function id(){
		return $this->id; 
	}

}
