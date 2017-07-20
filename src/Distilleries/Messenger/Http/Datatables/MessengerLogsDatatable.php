<?php

namespace Distilleries\Messenger\Http\Datatables;

use Distilleries\Expendable\Http\Datatables\BaseDatatable;

class MessengerLogsDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this
            ->add('id', null)
            ->add('messenger_user_id', function($model) {
                if ($model->user) {
                    $model->user->first_name.' ' . $model->user->last_name;
                }
                else return '/';
            }, trans('messenger::backend.messenger_user'))
            ->add('response', null, trans('messenger::backend.response'))
            ->add('inserted_at', function ($model) {
                return $model->inserted_at->format(trans('messenger::backend.format')));
            }, trans('messenger::backend.inserted_at'))
            ->addDefaultAction('messenger::backend.datatable.actions');
    }

}
