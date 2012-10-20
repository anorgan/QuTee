<?php

namespace Acme;

use Qutee\TaskInterface;

/**
 * SendMail
 *
 * @author anorgan
 */
class SendMail implements TaskInterface
{
    protected $_data;

    public function run()
    {
        mail($this->_data['to'], $this->_data['subject'], $this->_data['text'], 'From:'. $this->_data['from']);

        return true;
    }

    public function setData(array $data)
    {
        $this->_data = $data;
    }
}