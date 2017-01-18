<?php

namespace Unisharp\Loggable\Facades;

use Illuminate\Support\Facades\Facade;

class Loggable extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    return 'Loggable';
  }

}
