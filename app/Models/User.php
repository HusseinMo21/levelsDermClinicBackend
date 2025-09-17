<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'profile_image',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function createdPatients(): HasMany
    {
        return $this->hasMany(Patient::class, 'created_by');
    }

    public function createdServices(): HasMany
    {
        return $this->hasMany(Service::class, 'created_by');
    }

    public function createdInventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'created_by');
    }

    public function createdSuppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'created_by');
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function processedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function createdAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    public function updatedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'updated_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Accessors
    public function getIsDoctorAttribute(): bool
    {
        return $this->hasRole('doctor');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }

    public function getIsReceptionistAttribute(): bool
    {
        return $this->hasRole('receptionist');
    }

    public function getIsInventoryManagerAttribute(): bool
    {
        return $this->hasRole('inventory');
    }

    public function getIsCustomerServiceAttribute(): bool
    {
        return $this->hasRole('customerservice');
    }

    public function getIsPatientAttribute(): bool
    {
        return $this->hasRole('patient');
    }
}
