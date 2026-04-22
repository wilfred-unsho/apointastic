<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_id',
        'provider_id',
        'service_id',
        'starts_at_utc',
        'ends_at_utc',
        'user_timezone',
        'status',
    ];

    protected $casts = [
        'starts_at_utc' => 'immutable_datetime',
        'ends_at_utc' => 'immutable_datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected function startsAtLocal(): Attribute
    {
        return Attribute::make(
            get: fn (): ?CarbonImmutable => $this->starts_at_utc?->setTimezone($this->user_timezone),
            set: function (CarbonImmutable|string|null $value): ?string {
                if ($value === null) {
                    return null;
                }

                $local = $value instanceof CarbonImmutable
                    ? $value
                    : CarbonImmutable::parse($value, $this->user_timezone ?: 'UTC');

                return $local->utc()->toDateTimeString();
            },
        );
    }

    protected function endsAtLocal(): Attribute
    {
        return Attribute::make(
            get: fn (): ?CarbonImmutable => $this->ends_at_utc?->setTimezone($this->user_timezone),
            set: function (CarbonImmutable|string|null $value): ?string {
                if ($value === null) {
                    return null;
                }

                $local = $value instanceof CarbonImmutable
                    ? $value
                    : CarbonImmutable::parse($value, $this->user_timezone ?: 'UTC');

                return $local->utc()->toDateTimeString();
            },
        );
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }
}
