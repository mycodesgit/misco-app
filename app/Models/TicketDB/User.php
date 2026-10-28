<?php

namespace App\Models\TicketDB;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'campus_id',
        'office_id',
        'ustatus',
        'lname',
        'fname',
        'mname',
        'ext',
        'email',
        'contactno',
        'password',
        'role',
        'gender',
        'posted_by',
        'isAllowed',
        'station',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ustatus'  => 'integer',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Administrator';
    }

    // --- FEATURE ACCESS ---
    // Used by the menus and by the "feature:<name>" route middleware.

    /**
     * The only office that uses Daily Task, the Accomplishment report,
     * Work Progress (projects / kanban) and the Office Calendar.
     */
    public const TASK_OFFICE_ID = 36;

    /**
     * The one non-admin user who manages Wifi Vouchers (Administrators can too).
     */
    public const WIFI_VOUCHER_USER_ID = 5;

    public function hasDailyTaskAccess(): bool
    {
        return (int) $this->office_id === self::TASK_OFFICE_ID;
    }

    public function hasWorkProgressAccess(): bool
    {
        return (int) $this->office_id === self::TASK_OFFICE_ID;
    }

    public function hasCalendarAccess(): bool
    {
        return (int) $this->office_id === self::TASK_OFFICE_ID;
    }

    public function hasWifiVoucherAccess(): bool
    {
        return $this->isAdmin() || (int) $this->id === self::WIFI_VOUCHER_USER_ID;
    }

    /**
     * Feature names used by the "feature:<name>" route middleware.
     */
    public function hasFeatureAccess(string $feature): bool
    {
        return match ($feature) {
            // Each desk manages the categories of its own office
            'category'      => $this->isDeskStaff(),
            'daily_task'    => $this->hasDailyTaskAccess(),
            'work_progress' => $this->hasWorkProgressAccess(),
            'calendar'      => $this->hasCalendarAccess(),
            'wifi_voucher'  => $this->hasWifiVoucherAccess(),
            default         => false,
        };
    }

    // --- TICKET ROLES ---
    //
    // Everyone can file a ticket to any service desk (MIS Support, GSO
    // Support, ...). Desk staff also handle the tickets of their own desk.
    // On a single ticket, what a person can do depends on how they relate to
    // THAT ticket, not on their account role: desk staff who filed a ticket
    // are its requester, and see the requester side of it.

    /**
     * Works for a service desk (any role except "Requester"). Administrators
     * count as desk staff.
     */
    public function isDeskStaff(): bool
    {
        return $this->role !== 'Requester';
    }

    /**
     * Filed this ticket.
     */
    public function isRequesterOf(DailyTicketRequest $ticket): bool
    {
        return (int) $ticket->user_id === (int) $this->id;
    }

    /**
     * Listed in the ticket's comma-separated assigned_to.
     */
    public function isAssignedTo(DailyTicketRequest $ticket): bool
    {
        $ids = array_map('intval', array_map('trim', explode(',', (string) $ticket->assigned_to)));

        return in_array((int) $this->id, $ids, true);
    }

    /**
     * Can work on the ticket (change status, see the support side): desk staff
     * who are assigned to it, belong to the desk's office, or are admins — but
     * never the person who filed it.
     */
    public function canHandle(DailyTicketRequest $ticket): bool
    {
        if (!$this->isDeskStaff() || $this->isRequesterOf($ticket)) {
            return false;
        }

        return $this->isAdmin()
            || $this->isAssignedTo($ticket)
            || (int) $this->office_id === (int) $ticket->off_id;
    }

    /**
     * Can open the ticket and its chat: its requester or its handlers.
     */
    public function canViewTicket(DailyTicketRequest $ticket): bool
    {
        return $this->isRequesterOf($ticket) || $this->canHandle($ticket);
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function assignedTasks()
    {
        return $this->hasOne(UserAssignedTask::class, 'user_id');
    }

    public function pageTitle(string $title): string
    {
        $prefix = !$this->isDeskStaff()
        ? $this->role
        : ($this->office->office_abbr ?? 'N/A');

        return trim("{$prefix} {$title}");
    }
}
