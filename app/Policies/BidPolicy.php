<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\User;

class BidPolicy
{
    /** Developer hanya boleh mengubah/menarik penawarannya sendiri, selama lelang terbuka dan belum menang */
    public function update(User $user, Bid $bid): bool
    {
        return $user->role === 'developer'
            && $bid->developer_id === $user->id
            && ! $bid->is_winner
            && $bid->project->isOpen();
    }

    public function delete(User $user, Bid $bid): bool
    {
        return $this->update($user, $bid);
    }
}
