<?php

namespace App\Models\Academy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignInfoComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_info_id',
        'user_id',
        'content',
    ];

    public function campaignInfo()
    {
        return $this->belongsTo(CampaignInfo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
