<?php

namespace Sweetchuck\Robo\TsLint\Test\Helper\Dummy;

class DummyOutput extends \Symfony\Component\Console\Output\Output
{

    /**
     * @var string
     */
    public $output = '';

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
