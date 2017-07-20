<?php

namespace Distilleries\Messenger\Http\Forms;

use Distilleries\FormBuilder\FormValidator;

class MessengerUserForm extends FormValidator
{
    /**
     * {@inheritdoc}
     */
    public static $rules = [
        'link_id' => 'required|integer',
        'sender_id' => 'required|integer',
        'first_name' => 'required',
        'last_name' => 'required',
        'last_conversation_date' => 'required|date',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm()
    {
        $this
            ->add('id', 'hidden')
            ->add('link_id', 'text', [
                'validation' => 'required',
                'label' => trans('messenger::backend.link_id'),
            ])
            ->add('sender_id', 'number', [
                'validation' => 'required',
                'label' => trans('messenger::backend.sender_id'),
            ])
            ->add('first_name', 'text', [
                'validation' => 'required',
                'label' => trans('messenger::backend.first_name'),
            ])
            ->add('last_name', 'text', [
                'validation' => 'required',
                'label' => trans('messenger::backend.last_name'),
            ])
            ->add('last_conversation_date', 'datepicker', [
                'label' => trans('messenger::backend.approved_at'),
                'format' => trans('messenger::backend.formats.date_js'),
                'language' => app()->getLocale(),
            ]);
        if ($this->model->variables) {
            foreach($this->model->variables as $key => $variable) {
                $this
                    ->add('variable_'.$key, 'text', [
                        'label' => trans('messenger::backend.variable'). $variable->name,
                        'default_value' => $variable->value
                    ]);
            }
        }
        $this->add('back', 'button',
                [
                    'label' => trans('form-builder::form.back'),
                    'attr'  => [
                        'class'   => 'btn default',
                        'onclick' => 'window.history.back()'
                    ],
                ], false, true);
    }
}