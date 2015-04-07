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
        $fp = fopen(__DIR__ .'/../mail.log', 'a');
        fwrite($fp, json_encode($this->_data, JSON_PRETTY_PRINT) . PHP_EOL);
        fclose($fp);

        return true;
    }

    public function setData(array $data)
    {
        $this->_data = $data;
    }
}