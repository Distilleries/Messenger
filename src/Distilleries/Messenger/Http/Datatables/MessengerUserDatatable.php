<?php

namespace Distilleries\Messenger\Http\Datatables;

use Distilleries\Expendable\Http\Datatables\BaseDatatable;

class MessengerUserDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this
            ->add('id', null)
            ->add('first_name', null, trans('messenger::backend.first_name'))
            ->add('last_name', null, trans('messenger::backend.last_name'))
            ->add('sender_id', null, trans('messenger::backend.sender_id'))
            ->add('link_id', null, trans('messenger::backend.link_id'))
            ->add('last_conversation_date', function ($model) {
                return $model->inserted_at->format('messenger::backend.format');
            }, trans('messenger::backend.last_conversation_date'))
            ->addDefaultAction('messenger::backend.form.datatable.actions');
    }

}
