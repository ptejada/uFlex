<?php
	include('../core/config.php');
	include('../core/validations.php');
	
	//Process Update
	if(count($_POST)){
        /*
        * Covert POST into a Collection object
        * for better value handling
        */
        $input = new \ptejada\uFlex\Collection($_POST);

        /*
         * Updates queue
         */
		foreach($input->toArray() as $name=>$val){
            if (is_null($user->getProperty($name))) {
                /*
                 * If the field is not part of the user properties
                 * then reject the update
                 */
                unset($input->$name);
            }
            else
            {
                /*
                 * If the value is the same as the tha value stored
                 * on the user properties then reject the update
                 */
                if ($user->$name == $val) {
                    unset($input->$name);
                }

            }
		}

		if( ! $input->isEmpty() ){
			//Update info
			$user->update($input->toArray());
		}else{
			//Nothing has changed
			$user->log->error('No need to update!');
		}

		echo json_encode(array(
			'error'    => $user->log->getErrors(),
			'confirm'  => 'Account Updated!',
			'form'    => $user->log->getFormErrors(),
		));
	}