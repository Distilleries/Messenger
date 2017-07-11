<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerUserVariable extends BaseModel {

    protected $fillable = [
        'name',
        'value',
        'messenger_user_id'
    ];


    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(MessengerUser::class);
    }
}
