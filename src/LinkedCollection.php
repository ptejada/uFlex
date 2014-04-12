<?php

namespace Ptejada\UFlex;

/**
 * A Collection which references a existing array
 *
 * @package Ptejada\UFlex
 */
class LinkedCollection extends Collection
{
    public function __construct(array &$info = array())
    {
       $this->_data =& $info;
    }
}
