<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Symfony\Component\EventDispatcher\Event;

class SparePartParserEvent extends Event
{
    use SerializesModels;

    private $text;
    private $context;

    public function __construct($text, $context = [])
    {
        $this->text = $text;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
