<?php

namespace App\Traits;

trait HasAbilities
{
    /**
     * Get abilities based on user role.
     */
    public function getAbilities(): array
    {
        return $this->isAdmin() ? $this->adminAbilities() : $this->userAbilities();
    }

    protected function adminAbilities(): array
    {
        return ['*'];
    }

    protected function userAbilities(): array
    {
        return [
            // Players
            'player:view',
            'player:create',
            'player:update',

            // Teams
            'team:view',
            'team:create',
            'team:update'
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
