<?php

namespace App\Http\Repositories;

use App\Http\Repositories\AbstractRepository;
use App\Models\CallEventLog;

class CallEventlogRepository extends AbstractRepository
{
    public function __construct(CallEventLog $model)
    {
        parent::__construct($model);
    }

}
