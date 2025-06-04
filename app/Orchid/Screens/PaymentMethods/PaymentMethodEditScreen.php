<?php

declare(strict_types=1);

namespace App\Orchid\Screens\PaymentMethods;

use App\Models\PaymentMethod;
use App\Orchid\Layouts\PaymentMethod\PaymentMethodEditLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PaymentMethodEditScreen extends Screen
{
    public $payment_method;

    public function query(PaymentMethod $payment_method): iterable
    {
        return [
            'payment_method' => $payment_method,
        ];
    }

    public function name(): ?string
    {
        return $this->payment_method->exists ? 'Edit Payment Method' : 'Create Payment Method';
    }

    public function description(): ?string
    {
        return 'Payment Method Information';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.payment_methods',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash3')
                ->confirm('Are you sure you want to delete this payment method?')
                ->method('remove')
                ->canSee($this->payment_method->exists),

            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block(PaymentMethodEditLayout::class)
                ->title('Payment Method Information')
                ->description('Update payment method information.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                ),
        ];
    }

    public function save(PaymentMethod $payment_method, Request $request)
    {
        $payment_method->fill($request->get('payment_method'))->save();
        Toast::info('Payment method was saved.');

        return redirect()->route('platform.payment_methods');
    }

    public function remove(PaymentMethod $payment_method)
    {
        $payment_method->delete();
        Toast::info('Payment method was removed');

        return redirect()->route('platform.payment_methods');
    }
}
