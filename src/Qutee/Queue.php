<?php

namespace Qutee;

/**
 * Queue
 *
 * @author anorgan
 */
class Queue
{

    /**
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * @param string $name
     *
     * @return \Qutee\Queue
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }


}