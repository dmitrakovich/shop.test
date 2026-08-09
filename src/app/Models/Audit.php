<?php

namespace App\Models;

use App\Enums\Audit\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Models\Audit as BaseAudit;

/**
 * @property int $id
 * @property string|null $user_type
 * @property int|string|null $user_id
 * @property AuditEvent $event
 * @property string $auditable_type
 * @property int|string $auditable_id
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property string|null $url
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $tags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Model|null $user
 * @property-read Model|null $auditable
 */
class Audit extends BaseAudit
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'event' => AuditEvent::class,
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    /**
     * @return list<string>
     */
    public function getChangedAttributeNames(): array
    {
        return array_values(array_unique([
            ...array_keys($this->old_values ?? []),
            ...array_keys($this->new_values ?? []),
        ]));
    }

    /**
     * @return array<int, array{attribute: string, old: string, new: string}>
     */
    public function getChangesList(): array
    {
        $changes = [];

        foreach ($this->getChangedAttributeNames() as $attribute) {
            $changes[] = [
                'attribute' => $attribute,
                'old' => $this->stringifyValue(($this->old_values ?? [])[$attribute] ?? null),
                'new' => $this->stringifyValue(($this->new_values ?? [])[$attribute] ?? null),
            ];
        }

        return $changes;
    }

    public function getAuditableLabel(): string
    {
        return class_basename($this->auditable_type) . ' #' . $this->auditable_id;
    }

    public function getUserLabel(): ?string
    {
        $user = $this->user;

        if ($user === null) {
            return null;
        }

        if (isset($user->name) && filled($user->name)) {
            $label = (string)$user->name;

            if (isset($user->username) && filled($user->username)) {
                return "{$label} ({$user->username})";
            }

            return $label;
        }

        if (isset($user->username) && filled($user->username)) {
            return (string)$user->username;
        }

        if (isset($user->email) && filled($user->email)) {
            return (string)$user->email;
        }

        return class_basename($user) . ' #' . $user->getKey();
    }

    private function stringifyValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        if (is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        }

        return (string)$value;
    }
}
