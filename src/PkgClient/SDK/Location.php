<?php

declare (strict_types = 1);

namespace PkgClient\SDK;

final class Location
{
    /**
     * @var string
     */
    private $lat;

    /**
     * @var string
     */
    private $long;

    /**
     * @var string
     */
    private $alt;

    /**
     * @param string $lat
     * @param string $long
     * @param string $alt
     */
    public function __construct(string $lat, string $long, string $alt = '0')
    {
        $this->lat = $lat;
        $this->long = $long;
        $this->alt = $alt;
    }

    /**
     * @return string
     */
    public function lat() : string
    {
        return $this->lat;
    }

    /**
     * @return string
     */
    public function long() : string
    {
        return $this->long;
    }

    /**
     * @return string
     */
    public function alt() : string
    {
        return $this->alt;
    }
}