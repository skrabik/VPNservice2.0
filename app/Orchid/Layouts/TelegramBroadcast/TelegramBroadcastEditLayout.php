<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\TelegramBroadcast;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TelegramBroadcastEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            TextArea::make('broadcast.message')
                ->rows(14)
                ->required()
                ->title('Текст сообщения')
                ->help('Поддерживается HTML-разметка Telegram: <b>, <strong>, <i>, <em>, <u>, <ins>, <s>, <strike>, <del>, <code>, <pre>, <a href=\"...\">.')
                ->placeholder('Введите текст рассылки с HTML-разметкой Telegram'),

            CheckBox::make('broadcast.is_test')
                ->sendTrueOrFalse()
                ->title('Тестовая рассылка')
                ->help('Если включено, сообщение уйдет только на TELEGRAM_TEST_CHAT_ID из .env.'),
        ];
    }
}
