<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerUserVariable extends BaseModel {

    protected $fillable = [
        'variable',
        'value',
        'messenger_user_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_conversation_date',
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
