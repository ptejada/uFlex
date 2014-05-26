<?php
include("../core/config.php");
include("../core/validations.php");

//Process Registration
if (count($_POST)) {
    /*
     * Covert POST into a Collection object
     * for better values handling
     */
    $input = new \Ptejada\UFlex\Collection($_POST);

    //Register User
    $user->register(
        array(
            'username'   => (string) $input->username,
            'first_name' => (string) $input->first_name,
            'last_name'  => (string) $input->last_name,
            'email'      => (string) $input->email,
            'password'   => (string) $input->password,
            'password2'  => (string) $input->password2,
            'website'    => (string) $input->website,
            'group_id'   => (string) $input->group_id,
        )
    );

    echo json_encode(
        array(
            'error'   => $user->log->getErrors(),
            'confirm' => "User Registered Successfully. You may login now!",
            'form'    => $user->log->getFormErrors(),
        )
    );
}