<?php

namespace Distilleries\Messenger\Http\Forms;

use Distilleries\FormBuilder\FormValidator;

class MessengerLogsForm extends FormValidator
{
    /**
     * {@inheritdoc}
     */
    public static $rules = [
        'messenger_user_id' => 'required|integer',
        'request'           => 'required',
        'response'          => 'required',
        'inserted_at'       => 'required|date',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm()
    {
        $this
            ->add('id', 'hidden')
            ->add('messenger_user_id', 'number', [
                'label'      => trans('messenger::backend.messenger_user'),
                'validation' => 'required',
            ])
            ->add('request', 'textarea', [
                'validation' => 'required',
                'label'      => trans('messenger::backend.request'),
            ])
            ->add('response', 'text', [
                'validation' => 'required',
                'label'      => trans('messenger::backend.response'),
            ])
            ->add('inserted_at', 'datepicker', [
                'language' => app()->getLocale(),
            ]);
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