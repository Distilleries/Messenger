<?php

namespace Distilleries\Messenger\Http\Controllers\Backend;

use Distilleries\Messenger\Http\Datatables\MessengerUserDatatable;
use Distilleries\Messenger\Http\Forms\MessengerUserForm;
use Distilleries\Messenger\Models\MessengerLog;
use Distilleries\Messenger\Models\MessengerUser;
use Distilleries\Expendable\Contracts\LayoutManagerContract;
use Distilleries\Expendable\Http\Controllers\Backend\Base\BaseComponent;

class MessengerLogsController extends BaseComponent
{

    /**
     * VisitorController constructor.
     *
     * @param  \Distilleries\Messenger\Http\Datatables\MessengerUserDatatable  $datatable
     * @param  \Distilleries\Messenger\Http\Forms\MessengerUserForm  $form
     * @param  \Distilleries\Messenger\Models\MessengerUser  $model
     * @param  \Distilleries\Expendable\Contracts\LayoutManagerContract  $layoutManager
     */
    public function __construct(MessengerUserDatatable $datatable, MessengerUserForm $form, MessengerLog $model, LayoutManagerContract $layoutManager)
    {
        parent::__construct($model, $layoutManager);

        $this->datatable = $datatable;
        $this->form = $form;
    }

}
