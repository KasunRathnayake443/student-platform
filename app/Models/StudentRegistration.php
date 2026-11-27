<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRegistration extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_COMPLETED = 'completed';

    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';

    public const PAYMENT_EZCASH = 'ezcash';

    public const PAYMENT_MCASH = 'mcash';

    protected $fillable = [
        'full_name',
        'institution',
        'grade',
        'language',
        'email',
        'phone',
        'referred_by',
        'payment_method',
        'payment_date',
        'transaction_reference',
        'receipt_path',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];
}
