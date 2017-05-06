<?php

namespace Mini\Support\Facades;

use Mini\Support\Facades\Facade;

/**
 * @see \Mini\Language\Language
 * @see \Mini\Language\LanguageManager
 */
class Language extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'language'; }

}
