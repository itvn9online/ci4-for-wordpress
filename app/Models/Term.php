<?php

namespace App\Models;

use App\Models\Traits\TermGetTrait;
use App\Models\Traits\TermPermalinkTrait;
use App\Models\Traits\TermQueryTrait;
use App\Models\Traits\TermSliderTrait;

//
class Term extends TermBase
{
    use TermQueryTrait;
    use TermGetTrait;
    use TermPermalinkTrait;
    use TermSliderTrait;
}
