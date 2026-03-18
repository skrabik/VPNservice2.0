<?php

namespace App\Orchid\Layouts\Server;

use App\Models\ServerParameter;
use Orchid\Screen\Field;
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
                $field = Input::make("server_parameters[$parameter->id][value]")
                    ->type($parameter_key === ServerParameter::SERVER_PARAMETER_PANEL_PASSWORD_KEY ? 'password' : 'text')
                    ->value($parameter->value)
                    ->title(ServerParameter::SERVER_PARAMETER_LABELS[$parameter_key] ?? $parameter_key)
                    ->help($parameter_key);
            } else {
                $field = Input::make("new_server_parameters[$parameter_key]")
                    ->type($parameter_key === ServerParameter::SERVER_PARAMETER_PANEL_PASSWORD_KEY ? 'password' : 'text')
                    ->title(ServerParameter::SERVER_PARAMETER_LABELS[$parameter_key] ?? $parameter_key)
                    ->help($parameter_key);
            }

            if (! in_array($parameter_key, ServerParameter::OPTIONAL_SERVER_PARAMETERS, true)) {
                $field->required();
            }

            $parameters_fields[] = $field;
        }

        return $parameters_fields;
    }
}
