<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code',
        'register_directly_id',
        'normalized_license_plate',
        'car_catalog_id',
        'amount',
        'is_paid',
        'paid_at',
        'payment_method',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function registerDirectly()
    {
        return $this->belongsTo(RegisterDirectly::class);
    }

    public function carCatalog()
    {
        return $this->belongsTo(CarCatalog::class);
    }

    // Helper methods
    public static function generateInvoiceCode()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        
        // Sử dụng lock để tránh race condition
        return DB::transaction(function () use ($prefix, $date) {
            $lastInvoice = self::whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();
            
            $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_code, -4) + 1 : 1;
            
            return $prefix . $date . sprintf('%04d', $sequence);
        });
    }

    public static function normalizeLicensePlate($licensePlate)
    {
        if (empty($licensePlate)) {
            return null;
        }
        
        // Loại bỏ khoảng trắng, dấu gạch ngang, chuyển thành uppercase
        $cleaned = strtoupper(str_replace([' ', '-', '_', '.'], '', $licensePlate));
        
        // Tách phần chữ và số để thêm dấu gạch ngang chuẩn
        // Ví dụ: 20C00735 -> 20C-00735
        if (preg_match('/^([0-9]+[A-Z]+)([0-9]+)$/', $cleaned, $matches)) {
            $prefix = $matches[1]; // 20C
            $number = $matches[2]; // 00735
            return $prefix . '-' . $number;
        }
        
        // Nếu không match pattern thì trả về như cũ
        return $cleaned;
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }
}
