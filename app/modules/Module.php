<?php

namespace Module;

abstract class Module {

    protected $app;
    protected $twig;

    public function __construct($app, $twig)
    {
        $this->app  = $app;
        $this->twig = $twig;

        $this->init_routes();
    }

    abstract protected function init_routes();
}