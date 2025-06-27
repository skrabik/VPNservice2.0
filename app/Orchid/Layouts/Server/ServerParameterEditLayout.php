<?php

namespace App\Orchid\Layouts\Server;

use App\Models\ServerParameter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class ServerParameterEditLayout extends Rows
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
        $server = $this->query->get('server');
        $server_parameters = $this->query->get('server_parameters');

        $parameters_fields = [];

        $required_parameters = ServerParameter::SERVER_TYPES_PARAMETERS[$server->type] ?? [];

        $existing_parameters = [];
        foreach ($server_parameters as $parameter) {
            $existing_parameters[$parameter->key] = $parameter;
        }

        foreach ($required_parameters as $parameter_key) {
            if (isset($existing_parameters[$parameter_key])) {
                $parameter = $existing_parameters[$parameter_key];
                $parameters_fields[] = Input::make("server_parameters[$parameter->id][value]")
                    ->type('text')
                    ->value($parameter->value)
                    ->required()
                    ->title($parameter_key);
            } else {
                $parameters_fields[] = Input::make("new_server_parameters[$parameter_key]")
                    ->type('text')
                    ->required()
                    ->title($parameter_key);
            }
        }

        return $parameters_fields;
    }
}
