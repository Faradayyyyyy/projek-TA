<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvDevice extends Model
{
    use HasFactory;

    protected $table = 'cctv_devices';

    protected $fillable = [
        'name',
        'ip_address',
        'oid',
        'agent_oid',
        'rtsp_port',
        'onvif_port',
        'username',
        'password',
        'stream_path',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rtsp_port' => 'integer',
        'onvif_port' => 'integer',
        'agent_oid' => 'integer',
    ];

    /**
     * Accessor alias: $device->ip mengembalikan $device->ip_address
     */
    public function getIpAttribute(): ?string
    {
        return $this->ip_address;
    }

    /**
     * Mutator alias: menyetel $device->ip akan mengisi kolom ip_address
     */
    public function setIpAttribute($value): void
    {
        $this->attributes['ip_address'] = $value;
    }

    /**
     * Accessor: pastikan $device->oid selalu berupa string OID
     */
    public function getOidAttribute($value): string
    {
        return (string) ($value ?: $this->agent_oid ?: '');
    }

    /**
     * Mutator: saat oid disetel, sinkronkan juga kolom agent_oid jika numerik
     */
    public function setOidAttribute($value): void
    {
        $this->attributes['oid'] = (string) $value;
        if (is_numeric($value)) {
            $this->attributes['agent_oid'] = (int) $value;
        }
    }

    /**
     * Accessor alias pendukung untuk port, user, dan pass
     */
    public function getPortAttribute(): ?int
    {
        return $this->onvif_port;
    }

    public function getUserAttribute(): ?string
    {
        return $this->username;
    }

    public function getPassAttribute(): ?string
    {
        return $this->password;
    }
}
