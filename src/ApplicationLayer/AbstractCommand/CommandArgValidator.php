<?php

declare(strict_types=1);

namespace App\ApplicationLayer\AbstractCommand;

interface CommandArgValidator
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public function validate($value, ...$additionalArgs);
}
