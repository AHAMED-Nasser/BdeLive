<?php

abstract class DefaultController
{
    public function __construct()
    {
    }

    abstract protected function loadView(string $viewName): void;
}
