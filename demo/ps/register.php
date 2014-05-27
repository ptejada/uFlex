<?php
include("../core/config.php");
include("../core/validations.php");

//Process Registration
if (count($_POST)) {
    /*
     * Covert POST into a Collection object
     * for better values handling
     */
    $input = new \ptejada\uFlex\Collection($_POST);

    //Register User
    $user->register(
        array(
            'Username'   => (string) $input->Username,
            'first_name' => (string) $input->first_name,
            'last_name'  => (string) $input->last_name,
            'Email'      => (string) $input->Email,
            'Password'   => (string) $input->Password,
            'Password2'  => (string) $input->Password2,
            'website'    => (string) $input->website,
            'GroupID'   => (string) $input->GroupID,
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