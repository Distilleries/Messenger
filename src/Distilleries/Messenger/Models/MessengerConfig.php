<?php namespace Distilleries\Messenger\Models;


use Distilleries\Expendable\Models\BaseModel;


class MessengerConfig extends BaseModel {

    const INPUT_ANSWER_TYPE = 'answer';
    const INPUT_ANSWER_SUCCESS = 'success';
    const INPUT_ANSWER_FAILED = 'failed';

    protected $fillable = [
        'type',
        'content',
        'group_id',
        'payload',
        'extra',
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

    public function getExtraConvertedAttribute() {
        return json_decode($this->extra);
    }


    static public function getAnswerFromConfig($parent_id, $type = self::INPUT_ANSWER_TYPE) {
        $answers = self::where('parent_id', $parent_id)->get();
        foreach ($answers as $answer) {
            if ($answer->extra_converted->{self::INPUT_ANSWER_TYPE} && $answer->extra_converted->{self::INPUT_ANSWER_TYPE} == $type ) {
                return $answer;
            }
        }
        return null;
    }
}
