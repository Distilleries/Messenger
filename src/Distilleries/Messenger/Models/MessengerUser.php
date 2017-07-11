<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerUser extends BaseModel {

    protected $fillable = [
        'email',
        'last_conversation_date',
        'sender_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_conversation_date',
    ];

}
