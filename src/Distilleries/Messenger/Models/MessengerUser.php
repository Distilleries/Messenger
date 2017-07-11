<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerUser extends BaseModel {

    protected $fillable = [
        'last_conversation_date',
        'first_name',
        'last_name',
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

    public function variables()
    {
        return $this->hasMany(MessengerUserVariable::class);
    }

    public function progress()
    {
        return $this->hasMany(MessengerUserProgress::class);
    }

}
