<?php
include("../core/config.php");

//Process Password change
if (count($_POST)) {
    /*
     * Covert POST into a Collection object
     * for better handling
     */
    $input = new \Ptejada\UFlex\Collection($_POST);

    $hash = $input->c;

    if (!$user->isSigned() and $hash) {
        //Change password with confirmation hash
        $user->newPassword(
            $hash,
            array(
                'password'  => $input->password,
                'password2' => $input->password2,
            )
        );
        $redirectPage = "login";
    } else {
        //Change the password of signed in user without a confirmation hash
        $user->update(
            array(
                'password'  => $input->password,
                'password2' => $input->password2,
            )
        );
        $redirectPage = "account";
    }

    echo json_encode(
        array(
            'error'   => $user->log->getAllReports(),
            'confirm' => "Password Changed",
            'form'    => $user->log->getFormErrors(),
        )
    );
}