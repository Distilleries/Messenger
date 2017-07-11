<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerConfig extends BaseModel {

    protected $fillable = [
        'type',
        'content',
        'group_id',
        'payload',
        'parent_id'
    ];
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'messenger_config';

    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(MessengerConfig::class, "parent_id");
    }
}
