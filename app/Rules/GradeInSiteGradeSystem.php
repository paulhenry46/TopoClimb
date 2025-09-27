<?php

namespace App\Rules;

use App\Models\Site;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GradeInSiteGradeSystem implements ValidationRule
{
     protected $site;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cotations = $this->site->cotations();

        if (!array_key_exists($value, $cotations)) {
            $fail("The selected grade is not valid for this site.");
        }
    }
}
