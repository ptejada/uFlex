<?php

namespace ptejada\uFlex\Classes;

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
     * @param bool  $autoEscape
     */
    public function __construct(array &$info = array(), $autoEscape=false)
    {
        $this->_data =& $info;
        $this->_autoEscape = $autoEscape;
    }
}
