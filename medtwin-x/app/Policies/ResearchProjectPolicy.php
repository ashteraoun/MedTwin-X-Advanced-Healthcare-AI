<?php

namespace App\Policies;

use App\Models\ResearchProject;
use App\Models\User;

class ResearchProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ResearchProject $researchProject): bool
    {
        return $researchProject->owner_id === $user->id
            || ($researchProject->owner_id === null && $researchProject->status === 'demo');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ResearchProject $researchProject): bool
    {
        return $researchProject->owner_id === $user->id;
    }

    public function delete(User $user, ResearchProject $researchProject): bool
    {
        return $researchProject->owner_id === $user->id;
    }
}
