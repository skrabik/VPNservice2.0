<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Support;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class SupportTicketReplyLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            TextArea::make('reply.message')
                ->rows(8)
                ->required()
                ->title('Ответ клиенту')
                ->placeholder('Введите ответ поддержки'),
        ];
    }
}
