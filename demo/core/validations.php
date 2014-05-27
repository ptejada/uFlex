<?php
//Add validation for custom fields, first_name, last_name and website

$user->addValidation(
    array(
        'first_name' => array(
            'limit' => '0-15',
            'regEx' => '/\w+/'
        ),
        'last_name'  => array(
            'limit' => '0-15',
            'regEx' => '/\w+/'
        ),
        'website'    => array(
            'limit' => '0-50',
            'regEx' => '@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@'
        )
    )
);

/*
 * Alternative syntax to adding validation rules:
 *
 *      $user->addValidation('first_name','0-15','/\w+/');
 *      $user->addValidation('last_name','0-15','/\w+/');
 *      $user->addValidation('website','0-50','@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@');
 */