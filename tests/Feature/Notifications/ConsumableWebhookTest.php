<?php

namespace Tests\Feature\Notifications;

use App\Events\CheckoutableCheckedOut;
use App\Models\Consumable;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckoutConsumableNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ConsumableWebhookTest extends TestCase
{
    public function testConsumableCheckoutSendsWebhookNotificationWhenSettingEnabled()
    {
        Notification::fake();

        Setting::factory()->withWebhookEnabled()->create();

        event(new CheckoutableCheckedOut(
            Consumable::factory()->cardstock()->create(),
            User::factory()->create(),
            User::factory()->superuser()->create(),
            ''
        ));

        Notification::assertSentTo(
            new AnonymousNotifiable,
            CheckoutConsumableNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['slack'] === Setting::getSettings()->webhook_endpoint;
            }
        );
    }

    public function testConsumableCheckoutDoesNotSendWebhookNotificationWhenSettingDisabled()
    {
        Notification::fake();

        Setting::factory()->withWebhookDisabled()->create();

        event(new CheckoutableCheckedOut(
            Consumable::factory()->cardstock()->create(),
            User::factory()->create(),
            User::factory()->superuser()->create(),
            ''
        ));

        Notification::assertNotSentTo(new AnonymousNotifiable, CheckoutConsumableNotification::class);
    }
}
