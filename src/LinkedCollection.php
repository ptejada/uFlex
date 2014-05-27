<?php

namespace ptejada\uFlex;

/**
 * A Collection which references a existing array
 *
 * @package ptejada\uFlex
 */
class LinkedCollection extends Collection
{
    public function __construct(array &$info = array())
    {
       $this->_data =& $info;
    }
}
