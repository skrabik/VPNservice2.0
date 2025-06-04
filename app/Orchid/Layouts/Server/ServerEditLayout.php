<?php

namespace App\Orchid\Layouts\Server;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;

class ServerEditLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [];
    }
}
