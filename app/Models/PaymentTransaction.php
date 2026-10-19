<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['invoice_id','paid_on','paid_at','amount','method','notes','purpose','created_by_name'];
    public function invoice(){ return $this->belongsTo(Invoice::class); }

    /**
     * The moment the payment was actually taken, for display.
     *
     * paid_at normally carries the real time (store() stamps it). A midnight
     * paid_at means the time was wiped by an edit made before update() began
     * preserving it — in that case the true moment survives in created_at.
     */
    public function getPaidMomentAttribute()
    {
        // The instant the money was taken, stored in UTC.
        $instant = $this->paid_at;

        // A midnight paid_at means the time was wiped by an edit made before
        // update() began preserving it — the real moment survives in created_at.
        if (!$instant || date('H:i:s', strtotime($instant)) === '00:00:00') {
            $instant = $this->created_at ?: $instant;
        }

        // Date comes from paid_on (the business record, never converted);
        // only the clock time is converted to the display timezone.
        return \App\Support\DisplayTime::onDate($this->paid_on, $instant);
    }
}
