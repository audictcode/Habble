<?php

namespace App\Services\Concerns;

trait Notification
{
    public function getNotificationTypes(): array
    {
        return [
            'mention' => 'Mención',
            'comment' => 'Comentario',
            'author' => 'Autor',
            'staff' => 'Equipo',
            'warning' => 'Aviso',
            'info' => 'Información'
        ];
    }

    public function getNotificationColors(): array
    {
        return [
            'mention' => 'btn-primary',
            'comment' => 'btn-secondary',
            'author' => 'btn-dark',
            'staff' => 'btn-danger',
            'warning' => 'btn-warning',
            'info' => 'btn-info'
        ];
    }

    public function getNotificationType(): string
    {
        $notificationTypes = $this->getNotificationTypes();

        if(!array_key_exists($this->type, $notificationTypes)) {
            return 'Notificación';
        }

        return $notificationTypes[$this->type];
    }

    public function getNotificationColor(): string
    {
        $notificationColors = $this->getNotificationColors();

        if(!array_key_exists($this->type, $notificationColors)) {
            return 'Notificación';
        }

        return $notificationColors[$this->type];
    }

    public function userSaw(): bool
    {
        return !! $this->user_saw;
    }
}
