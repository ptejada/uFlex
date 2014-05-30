<?php

namespace ptejada\uFlex;

/**
 * A Collection which references a existing array
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class LinkedCollection extends Collection
{
    /**
     * Takes the reference of an array
     *
     * @param array $info
     */
    public function __construct(array &$info = array())
    {
        $this->_data =& $info;
    }
}
