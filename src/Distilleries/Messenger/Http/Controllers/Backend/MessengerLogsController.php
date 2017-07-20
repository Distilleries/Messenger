<?php

namespace Distilleries\Messenger\Http\Controllers\Backend;

use Distilleries\Messenger\Http\Datatables\MessengerLogsDatatable;
use Distilleries\Messenger\Http\Forms\MessengerLogsForm;
use Distilleries\Messenger\Models\MessengerLog;
use Distilleries\Expendable\Contracts\LayoutManagerContract;
use Distilleries\Expendable\Http\Controllers\Backend\Base\BaseComponent;

class MessengerLogsController extends BaseComponent
{

    /**
     * VisitorController constructor.
     *
     * @param  \Distilleries\Messenger\Http\Datatables\MessengerLogsDatatable  $datatable
     * @param  \Distilleries\Messenger\Http\Forms\MessengerLogsForm  $form
     * @param  \Distilleries\Messenger\Models\MessengerLogs  $model
     * @param  \Distilleries\Expendable\Contracts\LayoutManagerContract  $layoutManager
     */
    public function __construct(MessengerLogsDatatable $datatable, MessengerLogsForm $form, MessengerLog $model, LayoutManagerContract $layoutManager)
    {
        parent::__construct($model, $layoutManager);

        $this->datatable = $datatable;
        $this->form = $form;
    }

}
