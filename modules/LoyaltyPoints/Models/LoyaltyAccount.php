<?php

namespace Modules\LoyaltyPoints\Models;

use App\Restorant;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $table = 'loyalty_accounts';

    protected $guarded = [];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function restorant(): BelongsTo
    {
        return $this->belongsTo(Restorant::class, 'restorant_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class)->orderBy('id', 'DESC');
    }

    /**
     * How many riyals the current point balance is worth, based on the
     * vendor's configured redeem ratio (default 10 points = 1 riyal).
     */
    public function getRedeemableValueAttribute(): float
    {
        $ratio = (float) $this->restorant->getConfig('loyalty_redeem_points_per_riyal', 10);
        if ($ratio <= 0) {
            return 0;
        }

        return floor($this->points_balance / $ratio);
    }
}
