<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerUserProgress extends BaseModel {

    protected $fillable = [
        'messenger_user_id',
        'messenger_config_id',
        'progression_date'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'progression_date',
    ];

    public $timestamps = false;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messenger_user_progress';

    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(MessengerUser::class);
    }

    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function config()
    {
        return $this->belongsTo(MessengerConfig::class, 'messenger_config_id');
    }
}
